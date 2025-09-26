<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

/**
 * Controlador de administración para respaldos y restauraciones de la base de datos.
 *
 * Características:
 * - Descarga streaming del .sql usando `mysqldump` (opciones seguras por defecto).
 * - Restauración usando cliente `mysql` (comando SOURCE) con deshabilitado temporal de FK.
 * - Fallback a librería PHP ifsnop/mysqldump-php (si existe) al fallar `mysqldump`.
 * - Fallback de restauración "parser simple" en PHP cuando no hay cliente `mysql`.
 *
 * Seguridad:
 * - Nunca registra usuario/contraseña/host/DB en logs.
 * - Las contraseñas se pasan vía variable de entorno del proceso (`MYSQL_PWD`).
 *
 * Variables .env soportadas:
 * - MYSQLDUMP_PATH      (por defecto: "mysqldump")
 * - MYSQL_CLI_PATH      (por defecto: "mysql")
 * - MYSQLDUMP_FORCE_TCP (bool, por defecto: false)
 * - MYSQL_FORCE_TCP     (bool, por defecto: false)
 *
 * Notas:
 * - Dumps con `DELIMITER`/procedimientos complejos pueden no restaurar con el parser PHP.
 * - Recomendado: tener instalados los binarios nativos para máxima robustez y velocidad.
 */
class AdminBackupController extends Controller
{
    /* ===========================
     * Constantes y propiedades
     * =========================== */

    /** Tiempo para comandos cortos (detecciones, --help). */
    private const HELP_TIMEOUT_SEC = 10;

    /** Tiempo máximo razonable para dump/restore grandes. Ajusta según tu BD. */
    private const DUMP_TIMEOUT_SEC = 3600;
    private const RESTORE_TIMEOUT_SEC = 3600;

    /** Tamaño del buffer al descomprimir .gz (bytes). */
    private const GUNZIP_CHUNK_BYTES = 1024 * 512; // 512KB

    /**
     * Tablas de "sondeo" opcional para checar efecto de restauración.
     * Deja vacío para no realizar comprobación. Puedes agregar las que quieras.
     * Ej.: ['operadores', 'vehiculos']
     */
    private const SAMPLE_TABLES = ['operadores'];

    /** Cache interno para supportsOption(). */
    private array $supportsOptionCache = [];

    /** Cache interno para binaryWorks(). */
    private array $binaryWorksCache = [];

    /* ===========================
     * Vistas / UI
     * =========================== */

    /**
     * Muestra la vista del módulo con datos de conexión y disponibilidad de binarios.
     */
    public function index(Request $request)
    {
        $conn = DB::connection()->getConfig();

        $dbInfo = [
            'driver'   => config('database.default'),
            'host'     => $conn['host']     ?? 'localhost',
            'port'     => (string)($conn['port'] ?? '3306'),
            'database' => $conn['database'] ?? '',
            'username' => $conn['username'] ?? '',
        ];

        $hasMySqlDump = $this->binaryWorks($this->mysqldumpPath(), ['--version']);
        $hasMysqlCli  = $this->binaryWorks($this->mysqlCliPath(), ['--version']);

        return view('admin.backup', [
            'dbInfo'        => $dbInfo,
            'hasMySqlDump'  => $hasMySqlDump,
            'hasMysqlCli'   => $hasMysqlCli,
        ]);
    }

    /* ===========================
     * Descarga / Dump
     * =========================== */

    /**
     * Genera y descarga el archivo .sql en streaming usando `mysqldump`.
     *
     * @return StreamedResponse
     */
    public function download(Request $request)
    {
        $this->authorizeAdmin();

        $dumpBin = $this->mysqldumpPath();
        if (!$this->binaryWorks($dumpBin, ['--version'])) {
            return back()->withErrors(
                'No se encontró el binario "mysqldump". ' .
                'Define MYSQLDUMP_PATH en .env o agrega el binario al PATH del servidor.'
            );
        }

        $cfg = $this->getDbConnConfig();
        if ($cfg['database'] === '' || $cfg['username'] === '') {
            return back()->withErrors('Faltan datos de conexión (database/username). Revisa tu configuración de DB.');
        }

        $filename = sprintf('backup-%s.sql', now()->format('Ymd-His'));

        // Construye el comando `mysqldump` de forma portable y segura
        $cmd = $this->buildMysqldumpCommand($dumpBin, $cfg);

        return response()->streamDownload(function () use ($cmd, $cfg) {
            $stderr = '';

            $process = new Process($cmd, null, ['MYSQL_PWD' => $cfg['password']]);
            $process->setTimeout(self::DUMP_TIMEOUT_SEC);
            $process->run(function (string $type, string $data) use (&$stderr) {
                if ($type === Process::OUT) {
                    echo $data;
                    @ob_flush();
                    @flush(); // Empuja el stream al cliente
                } else {
                    $stderr .= $data;
                }
            });

            if ($process->isSuccessful()) {
                return; // Ya se envió todo por STDOUT
            }

            // Fallback 1: ifsnop/mysqldump-php (si está instalado)
            if (class_exists(\Ifsnop\Mysqldump\Mysqldump::class)) {
                try {
                    $dsn  = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                        $cfg['host'] ?: '127.0.0.1',
                        (string) $cfg['port'],
                        $cfg['database']
                    );
                    $opts = [
                        'add-drop-table'     => true,
                        'single-transaction' => true,
                        'routines'           => true,
                        'events'             => true,
                        'hex-blob'           => true,
                    ];
                    $dump = new \Ifsnop\Mysqldump\Mysqldump($dsn, $cfg['username'], $cfg['password'], $opts);
                    $dump->start('php://output');
                    return;
                } catch (\Throwable $e) {
                    $stderr .= "\nPHP fallback (ifsnop) failed: " . $e->getMessage();
                }
            }

            // Fallback 2: emite el error como COMENTARIOS SQL (no rompe el import)
            $stderr = trim($stderr) !== '' ? $stderr : $process->getErrorOutput();
            echo "\n-- mysqldump FAILED --\n-- " . str_replace("\n", "\n-- ", trim($stderr)) . "\n";
        }, $filename, [
            'Content-Type'        => 'application/sql; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Construye el comando de `mysqldump` con opciones seguras y detecciones por versión.
     *
     * @param  string $dumpBin Ruta o nombre del binario mysqldump.
     * @param  array  $cfg     ['host','port','database','username','password','unix_socket'|null]
     * @return array<string>   Comando listo para Process.
     */
    private function buildMysqldumpCommand(string $dumpBin, array $cfg): array
    {
        $isWin    = $this->isWindows();
        $forceTcp = (bool) env('MYSQLDUMP_FORCE_TCP', false);

        $cmd = [
            $dumpBin,
            '-u', $cfg['username'],
            '--single-transaction',
            '--quick',
            '--add-drop-table',
            '--routines',
            '--triggers',
            '--events',
        ];

        // Opcionales por soporte de versión
        if ($this->supportsOption($dumpBin, 'set-gtid-purged')) {
            $cmd[] = '--set-gtid-purged=OFF';
        }
        if ($this->supportsOption($dumpBin, 'column-statistics')) {
            $cmd[] = '--column-statistics=0';
        }

        // Conectividad:
        // - En Windows evitamos forzar TCP por defecto (error 10106 común).
        // - Si forceTcp=true, o si NO es Windows, pasamos host/port y --protocol=TCP.
        if (!$isWin || $forceTcp) {
            $hostArg = $cfg['host'] ?: '127.0.0.1';
            array_push($cmd, '-h', $hostArg, '-P', (string) $cfg['port'], '--protocol=TCP');
        } elseif (!empty($cfg['unix_socket'])) {
            // Socket UNIX si existe y no estamos forzando TCP (útil en Linux/Mac)
            array_push($cmd, '--socket=' . $cfg['unix_socket']);
        }

        // Por último, la base de datos
        $cmd[] = $cfg['database'];

        return $cmd;
    }

    /* ===========================
     * Restauración / Restore
     * =========================== */

    /**
     * Restaura la base de datos desde un archivo .sql o .sql.gz.
     *
     * Flujo:
     * 1) Validar y guardar temporalmente el archivo.
     * 2) Si es .gz, descomprimir a .sql.
     * 3) Intento A: cliente `mysql` con SOURCE (robusto).
     * 4) Intento B: parser PHP línea a línea (limitado).
     */
    public function restore(Request $request)
    {
        $this->authorizeAdmin();

        // 1) Entrada y validaciones
        if (!$request->hasFile('sql_file')) {
            $u = ini_get('upload_max_filesize');
            $p = ini_get('post_max_size');
            return back()->withErrors(
                "No se recibió el archivo. Verifica que seleccionaste un .sql/.gz " .
                "y que no excede los límites (upload_max_filesize={$u}, post_max_size={$p})."
            );
        }

        $request->validate([
            'sql_file' => ['required', 'file'],
            'confirm'  => ['required', 'accepted'],
        ], [
            'confirm.accepted' => 'Debes confirmar que entiendes que se sobreescribirá la base de datos.',
        ]);

        // 2) Guardar archivo temporal
        Storage::disk('local')->makeDirectory('tmp/restores');
        $uploaded = $request->file('sql_file');
        $ext      = strtolower($uploaded->getClientOriginalExtension() ?: 'sql');

        $name     = uniqid('restore_', true) . '.' . $ext;
        $tempPath = $uploaded->storeAs('tmp/restores', $name, 'local');
        $fullPath = Storage::disk('local')->path($tempPath);

        if (!is_file($fullPath)) {
            return back()->withErrors('No fue posible guardar el archivo en disco (permisos/espacio).');
        }

        // 3) Si viene .gz, descomprimir a .sql
        if ($ext === 'gz') {
            $sqlTemp = Storage::disk('local')->path('tmp/restores/' . uniqid('restore_', true) . '.sql');
            if (!$this->gunzipTo($fullPath, $sqlTemp) || !is_file($sqlTemp)) {
                @unlink($fullPath);
                return back()->withErrors('No se pudo descomprimir el archivo .gz.');
            }
            @unlink($fullPath);
            $fullPath = $sqlTemp;
        } elseif ($ext !== 'sql') {
            @unlink($fullPath);
            return back()->withErrors('El archivo debe ser .sql o .sql.gz.');
        }

        // 4) Datos de conexión
        $cfg = $this->getDbConnConfig();

        // Conteo opcional de tablas de muestra (para validar efecto)
        $beforeSample = $this->sampleTablesCount(self::SAMPLE_TABLES);

        // ===== 5) Intento 1: cliente mysql/mariadb con SOURCE (robusto) =====
        $mysqlBin = $this->mysqlCliPath();
        if ($this->binaryWorks($mysqlBin, ['--version'])) {
            $isWin    = $this->isWindows();
            $forceTcp = (bool) env('MYSQL_FORCE_TCP', false);
            $sourcePath = str_replace('\\', '/', $fullPath); // Normaliza para SOURCE

            // Base: usuario y base; host/port TCP si procede
            $cmd = [$mysqlBin, '-u', $cfg['username'], $cfg['database']];
            if (!$isWin || $forceTcp) {
                array_splice($cmd, 2, 0, ['-h', $cfg['host'] ?: '127.0.0.1', '-P', (string) $cfg['port'], '--protocol=TCP']);
            } elseif (!empty($cfg['unix_socket'])) {
                array_splice($cmd, 2, 0, ['--socket=' . $cfg['unix_socket']]);
            }

            // Ejecuta la restauración encapsulando SOURCE y FK OFF/ON
            $cmd = array_merge($cmd, [
                '-e', "SET FOREIGN_KEY_CHECKS=0; SOURCE {$sourcePath}; SET FOREIGN_KEY_CHECKS=1;"
            ]);

            $process = new Process($cmd, null, ['MYSQL_PWD' => $cfg['password']]);
            $process->setTimeout(self::RESTORE_TIMEOUT_SEC);
            $process->run();

            if ($process->isSuccessful()) {
                $afterMsg = $this->formatSampleDiffMsg($beforeSample, $this->sampleTablesCount(self::SAMPLE_TABLES));
                @unlink($fullPath);
                return back()->with('success', 'Restauración completada con el cliente mysql' . $afterMsg);
            }

            Log::error('MySQL restore failed', [
                'exit_code' => $process->getExitCode(),
                // No incluimos credenciales ni ruta exacta del archivo
                'stderr_len' => strlen($process->getErrorOutput()),
                'stdout_len' => strlen($process->getOutput()),
            ]);
            // Dejamos $fullPath para fallback PHP
        }

        // ===== 6) Intento 2: fallback PHP (parser simple) =====
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $handle = fopen($fullPath, 'r');
            if (!$handle) {
                return back()->withErrors('No se pudo abrir el archivo SQL.');
            }

            $buffer = '';
            $delimiter = ';'; // Parser simple no maneja DELIMITER dinámico
            $firstLine = true;

            while (($line = fgets($handle)) !== false) {
                if ($firstLine) {
                    $line = $this->stripUtf8Bom($line);
                    $firstLine = false;
                }

                $trim = ltrim($line);

                // Ignorar comentarios y ruidos comunes
                if ($this->isIgnorableSqlLine($trim)) {
                    continue;
                }

                $buffer .= $line;

                // Ejecuta cuando termina en ';' (delimiter fijo)
                if ($this->endsWithDelimiter($line, $delimiter)) {
                    DB::unprepared($buffer);
                    $buffer = '';
                }
            }
            fclose($handle);

            // Si quedó buffer (última sentencia sin salto), intenta ejecutarla
            if (trim($buffer) !== '') {
                DB::unprepared($buffer);
            }

            $afterMsg = $this->formatSampleDiffMsg($beforeSample, $this->sampleTablesCount(self::SAMPLE_TABLES));
            return back()->with(
                'success',
                'Restauración completada (método PHP)' 
            );
        } catch (\Throwable $e) {
            Log::error('PHP restore failed', ['msg' => $e->getMessage()]);
            return back()->withErrors('Falló la restauración: ' . $e->getMessage());
        } finally {
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Throwable $e) {}
            if (is_file($fullPath)) { @unlink($fullPath); }
        }
    }

    /* ===========================
     * Helpers de autorización / entorno
     * =========================== */

    /**
     * Asegura que el usuario autenticado sea administrador.
     * (Es redundante si el middleware role:administrador ya protege las rutas.)
     */
    protected function authorizeAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->hasRole('administrador')) {
            abort(403, 'Solo administradores.');
        }
    }

    /**
     * Ruta o nombre del binario mysqldump.
     */
    protected function mysqldumpPath(): string
    {
        return env('MYSQLDUMP_PATH', 'mysqldump');
    }

    /**
     * Ruta o nombre del binario mysql CLI.
     */
    protected function mysqlCliPath(): string
    {
        return env('MYSQL_CLI_PATH', 'mysql');
    }

    /**
     * ¿El proceso binario funciona (e.g., --version)?, con cache en memoria.
     *
     * @param  string        $bin
     * @param  array<string> $args
     */
    protected function binaryWorks(string $bin, array $args = ['--version']): bool
    {
        $key = $bin . ' ' . implode(' ', $args);
        if (array_key_exists($key, $this->binaryWorksCache)) {
            return $this->binaryWorksCache[$key];
        }

        try {
            $p = new Process(array_merge([$bin], $args));
            $p->setTimeout(self::HELP_TIMEOUT_SEC);
            $p->run();
            return $this->binaryWorksCache[$key] = $p->isSuccessful();
        } catch (\Throwable $e) {
            return $this->binaryWorksCache[$key] = false;
        }
    }

    /**
     * Revisa si el binario soporta una opción (aparece en --help), con cache.
     *
     * @param  string $bin
     * @param  string $option e.g. "set-gtid-purged" o "--set-gtid-purged"
     */
    protected function supportsOption(string $bin, string $option): bool
    {
        $needle = ltrim($option, '-'); // normaliza
        $cacheKey = $bin . '::' . $needle;
        if (array_key_exists($cacheKey, $this->supportsOptionCache)) {
            return $this->supportsOptionCache[$cacheKey];
        }

        try {
            $p = new Process([$bin, '--help']);
            $p->setTimeout(self::HELP_TIMEOUT_SEC);
            $p->run();
            $help = $p->getOutput() . $p->getErrorOutput();
            return $this->supportsOptionCache[$cacheKey] = (stripos($help, $needle) !== false);
        } catch (\Throwable $e) {
            return $this->supportsOptionCache[$cacheKey] = false;
        }
    }

    /**
     * Devuelve la configuración de conexión activando defaults seguros.
     *
     * @return array{host:string,port:int,database:string,username:string,password:string,unix_socket: ?string}
     */
    private function getDbConnConfig(): array
    {
        $cfg = DB::connection()->getConfig();

        return [
            'host'        => (string)($cfg['host'] ?? '127.0.0.1'),
            'port'        => (int)($cfg['port'] ?? 3306),
            'database'    => (string)($cfg['database'] ?? ''),
            'username'    => (string)($cfg['username'] ?? ''),
            'password'    => (string)($cfg['password'] ?? ''),
            'unix_socket' => isset($cfg['unix_socket']) ? (string)$cfg['unix_socket'] : null,
        ];
    }

    /**
     * ¿Es Windows?
     */
    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /* ===========================
     * Helpers de archivos y parsing
     * =========================== */

    /**
     * Descomprime un .gz hacia un archivo de salida .sql.
     */
    protected function gunzipTo(string $gzFile, string $outFile): bool
    {
        try {
            $in = gzopen($gzFile, 'rb');
            if (!$in) {
                return false;
            }
            $out = fopen($outFile, 'wb');
            if (!$out) {
                gzclose($in);
                return false;
            }
            while (!gzeof($in)) {
                fwrite($out, gzread($in, self::GUNZIP_CHUNK_BYTES));
            }
            gzclose($in);
            fclose($out);
            return true;
        } catch (\Throwable $e) {
            Log::error('gunzip error', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Determina si una línea SQL puede ignorarse: comentarios/ruido de herramientas.
     */
    private function isIgnorableSqlLine(string $trimmedLine): bool
    {
        return $trimmedLine === ''
            || str_starts_with($trimmedLine, '-- ')
            || str_starts_with($trimmedLine, '#')
            || preg_match('/^\/\*![0-9]{5}\s.*\*\/;?$/', trim($trimmedLine)) === 1
            || preg_match('/^(mysqldump(\.exe)?|mysql(\.exe)?):/i', $trimmedLine) === 1
            || preg_match('/^--\s*mysqldump\s*FAILED/i', $trimmedLine) === 1;
    }

    /**
     * Verifica si una línea termina con el delimitador actual (por defecto ';').
     */
    private function endsWithDelimiter(string $line, string $delimiter): bool
    {
        $r = rtrim($line);
        return substr($r, -strlen($delimiter)) === $delimiter;
    }

    /**
     * Elimina BOM UTF-8 si está presente en la primera línea del archivo.
     */
    private function stripUtf8Bom(string $line): string
    {
        $bom = "\xEF\xBB\xBF";
        if (strncmp($line, $bom, 3) === 0) {
            return substr($line, 3);
        }
        return $line;
    }

    /**
     * Cuenta registros de un conjunto de tablas de muestra si existen.
     *
     * @param  string[] $tables
     * @return array<string,int>|null  [tabla => conteo] o null si no se midió nada
     */
    private function sampleTablesCount(array $tables): ?array
    {
        if (empty($tables)) {
            return null;
        }
        $out = [];
        foreach ($tables as $t) {
            try {
                if (Schema::hasTable($t)) {
                    $out[$t] = (int) DB::table($t)->count();
                }
            } catch (\Throwable $e) {
                // Ignora errores de conteo
            }
        }
        return $out === [] ? null : $out;
    }

    /**
     * Crea un mensaje " (tabla1: a → b, tabla2: c → d)" con los difs de conteo.
     *
     * @param  array<string,int>|null $before
     * @param  array<string,int>|null $after
     */
    private function formatSampleDiffMsg(?array $before, ?array $after): string
    {
        if ($before === null || $after === null) {
            return '';
        }
        $parts = [];
        foreach ($after as $table => $cntAfter) {
            $cntBefore = $before[$table] ?? null;
            if ($cntBefore !== null) {
                $parts[] = "{$table}: {$cntBefore} → {$cntAfter}";
            }
        }
        return $parts ? ' (' . implode(', ', $parts) . ')' : '';
    }
}
