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
    /** Tiempo para comandos cortos (detecciones, --help). */
    private const HELP_TIMEOUT_SEC = 10;

    /** Tiempo máximo razonable para dump/restore grandes. Ajusta según tu BD. */
    private const DUMP_TIMEOUT_SEC = 3600;
    private const RESTORE_TIMEOUT_SEC = 3600;

    /** Tamaño del buffer al descomprimir .gz (bytes). */
    private const GUNZIP_CHUNK_BYTES = 1024 * 512; // 512KB

    /**
     * Tablas de "sondeo" opcional para checar efecto de restauración.
     * Deja vacío para no realizar comprobación.
     */
    private const SAMPLE_TABLES = ['operadores'];

    /** Cache interno para supportsOption(). */
    private array $supportsOptionCache = [];

    /** Cache interno para binaryWorks(). */
    private array $binaryWorksCache = [];

    /* ===========================
     * Vistas / UI
     * =========================== */

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

        // Aún calculamos disponibilidad, aunque tu Blade ya no lo usa.
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
     * Genera y descarga el archivo .sql en streaming.
     * Quitar pre-chequeo bloqueante: intentamos mysqldump y, si falla,
     * hacemos fallback a ifsnop o emitimos comentarios SQL con el error.
     */
    public function download(Request $request)
    {
        $this->authorizeAdmin();

        $cfg = $this->getDbConnConfig();
        if ($cfg['database'] === '' || $cfg['username'] === '') {
            return back()->withErrors('Faltan datos de conexión (database/username). Revisa tu configuración de DB.');
        }

        $filename = sprintf('backup-%s.sql', now()->format('Ymd-His'));
        $dumpBin  = $this->mysqldumpPath();
        $cmd      = $this->buildMysqldumpCommand($dumpBin, $cfg);

        return response()->streamDownload(function () use ($cmd, $cfg) {
            $stderr = '';

            // ===== Intento A: mysqldump (streaming a php://output) =====
            try {
                $process = new Process($cmd, null, ['MYSQL_PWD' => $cfg['password']]);
                $process->setTimeout(self::DUMP_TIMEOUT_SEC);
                $process->run(function (string $type, string $data) use (&$stderr) {
                    if ($type === Process::OUT) {
                        echo $data;
                        @ob_flush();
                        @flush();
                    } else {
                        $stderr .= $data;
                    }
                });

                if ($process->isSuccessful()) {
                    return; // dump OK
                }

                // Log de diagnóstico (sin credenciales)
                Log::error('Backup download: mysqldump failed', [
                    'exit_code' => $process->getExitCode(),
                    'stderr_len'=> strlen($process->getErrorOutput()),
                    'stdout_len'=> strlen($process->getOutput()),
                ]);
            } catch (\Throwable $e) {
                // No reventamos; seguimos al fallback
                $stderr .= "\nExec error: " . $e->getMessage();
            }

            // ===== Intento B: ifsnop/mysqldump-php =====
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
                        'skip-comments'      => true,
                    ];
                    $dump = new \Ifsnop\Mysqldump\Mysqldump($dsn, $cfg['username'], $cfg['password'], $opts);
                    $dump->start('php://output');
                    @ob_flush();
                    @flush();
                    return;
                } catch (\Throwable $e) {
                    $stderr .= "\nPHP fallback (ifsnop) failed: " . $e->getMessage();
                }
            }

            // ===== Intento C: emitir error como comentarios SQL =====
            $msg = trim($stderr) !== '' ? $stderr : 'mysqldump e ifsnop fallaron sin mensaje.';
            echo "\n-- mysqldump FAILED --\n-- " . str_replace("\n", "\n-- ", $msg) . "\n";
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
            '--default-character-set=utf8mb4',
        ];

        // Opcionales por soporte de versión
        if ($this->supportsOption($dumpBin, 'set-gtid-purged')) {
            $cmd[] = '--set-gtid-purged=OFF';
        }
        if ($this->supportsOption($dumpBin, 'column-statistics')) {
            $cmd[] = '--column-statistics=0';
        }

        // Conectividad
        if (!$isWin || $forceTcp) {
            $hostArg = $cfg['host'] ?: '127.0.0.1';
            array_push($cmd, '-h', $hostArg, '-P', (string) $cfg['port'], '--protocol=TCP');
        } elseif (!empty($cfg['unix_socket'])) {
            array_push($cmd, '--socket=' . $cfg['unix_socket']);
        }

        // Base de datos al final (STDOUT por defecto)
        $cmd[] = $cfg['database'];

        return $cmd;
    }

    /* ===========================
     * Restauración / Restore
     * =========================== */

    public function restore(Request $request)
    {
        $this->authorizeAdmin();

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

        Storage::disk('local')->makeDirectory('tmp/restores');
        $uploaded = $request->file('sql_file');
        $ext      = strtolower($uploaded->getClientOriginalExtension() ?: 'sql');

        $name     = uniqid('restore_', true) . '.' . $ext;
        $tempPath = $uploaded->storeAs('tmp/restores', $name, 'local');
        $fullPath = Storage::disk('local')->path($tempPath);

        if (!is_file($fullPath)) {
            return back()->withErrors('No fue posible guardar el archivo en disco (permisos/espacio).');
        }

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

        $cfg = $this->getDbConnConfig();
        $beforeSample = $this->sampleTablesCount(self::SAMPLE_TABLES);

        // ===== Intento 1: cliente mysql con SOURCE =====
        $mysqlBin = $this->mysqlCliPath();
        if ($this->binaryWorks($mysqlBin, ['--version'])) {
            $isWin    = $this->isWindows();
            $forceTcp = (bool) env('MYSQL_FORCE_TCP', false);
            $sourcePath = str_replace('\\', '/', $fullPath);

            $cmd = [$mysqlBin, '-u', $cfg['username'], $cfg['database']];
            if (!$isWin || $forceTcp) {
                array_splice($cmd, 2, 0, ['-h', $cfg['host'] ?: '127.0.0.1', '-P', (string) $cfg['port'], '--protocol=TCP']);
            } elseif (!empty($cfg['unix_socket'])) {
                array_splice($cmd, 2, 0, ['--socket=' . $cfg['unix_socket']]);
            }
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
                'stderr_len' => strlen($process->getErrorOutput()),
                'stdout_len' => strlen($process->getOutput()),
            ]);
        }

        // ===== Intento 2: parser PHP (limitado) =====
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $handle = fopen($fullPath, 'r');
            if (!$handle) {
                return back()->withErrors('No se pudo abrir el archivo SQL.');
            }

            $buffer = '';
            $delimiter = ';';
            $firstLine = true;

            while (($line = fgets($handle)) !== false) {
                if ($firstLine) {
                    $line = $this->stripUtf8Bom($line);
                    $firstLine = false;
                }

                $trim = ltrim($line);
                if ($this->isIgnorableSqlLine($trim)) {
                    continue;
                }

                $buffer .= $line;

                if ($this->endsWithDelimiter($line, $delimiter)) {
                    DB::unprepared($buffer);
                    $buffer = '';
                }
            }
            fclose($handle);

            if (trim($buffer) !== '') {
                DB::unprepared($buffer);
            }

            $afterMsg = $this->formatSampleDiffMsg($beforeSample, $this->sampleTablesCount(self::SAMPLE_TABLES));
            return back()->with('success', 'Restauración completada (método PHP)' . $afterMsg);
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

    protected function authorizeAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->hasRole('administrador')) {
            abort(403, 'Solo administradores.');
        }
    }

    protected function mysqldumpPath(): string
    {
        return env('MYSQLDUMP_PATH', 'mysqldump');
    }

    protected function mysqlCliPath(): string
    {
        return env('MYSQL_CLI_PATH', 'mysql');
    }

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

    protected function supportsOption(string $bin, string $option): bool
    {
        $needle = ltrim($option, '-');
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

    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /* ===========================
     * Helpers de archivos y parsing
     * =========================== */

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

    private function isIgnorableSqlLine(string $trimmedLine): bool
    {
        return $trimmedLine === ''
            || str_starts_with($trimmedLine, '-- ')
            || str_starts_with($trimmedLine, '#')
            || preg_match('/^\/\*![0-9]{5}\s.*\*\/;?$/', trim($trimmedLine)) === 1
            || preg_match('/^(mysqldump(\.exe)?|mysql(\.exe)?):/i', $trimmedLine) === 1
            || preg_match('/^--\s*mysqldump\s*FAILED/i', $trimmedLine) === 1;
    }

    private function endsWithDelimiter(string $line, string $delimiter): bool
    {
        $r = rtrim($line);
        return substr($r, -strlen($delimiter)) === $delimiter;
    }

    private function stripUtf8Bom(string $line): string
    {
        $bom = "\xEF\xBB\xBF";
        if (strncmp($line, $bom, 3) === 0) {
            return substr($line, 3);
        }
        return $line;
    }

    /**
     * @param  string[] $tables
     * @return array<string,int>|null
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
