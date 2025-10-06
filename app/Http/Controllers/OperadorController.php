<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use App\Models\OperadorFoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

// === AÑADIDOS PARA EXPORTAR EXCEL ===
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OperadoresExport;

/**
 * Controlador de Operadores
 *
 * Responsabilidades:
 * - Listar, crear, ver, editar, actualizar y eliminar Operadores.
 * - Gestionar la cuenta User asociada (creación y borrado).
 * - Subir y almacenar fotografías del Operador en disco privado/local.
 * - Borrar físicamente imágenes y limpiar directorios vacíos al eliminar Operador.
 *
 * Decisiones de diseño:
 * - Subida de fotos fuera de la transacción de BD (evita archivos huérfanos si falla la BD).
 * - Al eliminar un operador, también se elimina su User y todas sus fotos.
 * - Se usan constantes para parametrizar disco, límites y rutas base.
 *
 * NOTA: si en tu negocio NO quieres borrar el User cuando tenga otros roles (admin, etc.),
 * adapta el método deleteUserCompletely() para que sólo quite el rol "operador" o desactive al usuario.
 */
class OperadorController extends Controller
{
    /** Disco de Laravel a usar (config/filesystems.php). */
    private const DISK = 'local';

    /** Carpeta base donde se almacenan fotos de operadores. */
    private const BASE_DIR = 'operadores';

    /** Máximo de archivos permitidos por envío. */
    private const PHOTOS_MAX_FILES = 12;

    /** Tamaño máx. por imagen en KILOBYTES (8192 = 8 MB). Cambia a 5120 para 5 MB. */
    private const PHOTOS_MAX_SIZE_KB = 8192;

    public function __construct()
    {
        // Restringe todo el módulo a usuarios autenticados con rol administrador o capturista
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /**
     * Listado con filtros y paginación + Exportación Excel.
     */
    public function index(Request $request)
    {
        // Exportación Excel: respeta filtros y orden actuales, sin paginar (todos los datos que cumplen)
        if ($request->string('export')->lower()->toString() === 'xlsx') {
            $filename = 'operadores_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new OperadoresExport($request), $filename);
        }

        // Listado normal paginado
        $operadores = Operador::with(['user'])
            ->withCount('fotos')
            ->filter($request->all())
            ->paginate(15)
            ->withQueryString();

        return view('operadores.index', compact('operadores'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('operadores.create');
    }

    /**
     * Persiste un operador nuevo (crea también el User ligado) y guarda sus fotos.
     *
     * Flujo:
     *   1) Normaliza mayúsculas y valida datos.
     *   2) Crea User + Operador dentro de transacción.
     *   3) Guarda fotos (fuera de transacción).
     *   4) Redirige con modal de credenciales.
     */
    public function store(Request $request)
    {
        // (0) Normaliza a MAYÚSCULAS (antes de validar) — incluye ESTADO_CIVIL
        $this->normalizeRequestToUpper($request);

        // (1) Validación de campos del Operador (incluye DOMICILIO)
        $data = $this->validateOperador($request, isUpdate: false, operador: null);

        // Email: aceptar cualquier dominio válido. No se fuerza a mayúsculas.
        $request->validate([
            'email' => ['required', 'string', 'email:rfc', Rule::unique('users', 'email')],
        ]);

        // Validación de fotos para fallar pronto si no cumplen
        $this->validatePhotos($request, nullable: true);

        // (2) Crear User + Operador dentro de transacción
        $payload = DB::transaction(function () use ($request, $data) {
            $passwordPlain = Str::password(8, letters: true, numbers: true, symbols: false);

            $user = User::create([
                // Nombre visible en User también en MAYÚSCULAS
                'name'     => Str::upper(trim(($data['nombre'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? ''))),
                'email'    => $request->string('email')->toString(), // sin tocar mayúsculas/minúsculas
                'password' => \Hash::make($passwordPlain),
            ]);

            // Asignar rol "operador" si está disponible (Spatie)
            if (method_exists($user, 'assignRole')) {
                try { $user->assignRole('operador'); } catch (\Throwable $e) {}
            }

            $data['user_id'] = $user->id;
            $operador = Operador::create($data);

            return compact('operador', 'user', 'passwordPlain');
        });

        // (3) Subir fotos (fuera de la transacción)
        $this->saveUploadedPhotos($request, $payload['operador']->id);

        // (4) Redirección con modal de credenciales
        // (4) Redirección: la pestaña NUEVA irá a confirmación
return redirect()
    ->route('operadores.confirmacion')
    ->with([
        'created'  => true,
        'email'    => $payload['user']->email,
        'password' => $payload['passwordPlain'],
        // opcional:
        'success'  => 'Operador creado correctamente.',
    ]);

    }

    /**
     * Mostrar detalles del Operador.
     */
    public function show(Operador $operador)
    {
        $operador->load(['user', 'fotos']);
        return view('operadores.show', compact('operador'));
    }

    /**
     * Muestra el formulario de edición con datos y fotos cargados.
     */
    public function edit(Operador $operador)
    {
        $operador->load(['user', 'fotos']);
        return view('operadores.edit', compact('operador'));
    }

    /**
     * Actualiza datos del Operador, permite borrar fotos marcadas y subir nuevas.
     *
     * Flujo:
     *   1) Normaliza mayúsculas y valida datos.
     *   2) Actualiza operador.
     *   3) (Opcional) actualiza email del User ligado.
     *   4) Borrar fotos marcadas (BD + archivo).
     *   5) Validar/subir nuevas fotos.
     */
    public function update(Request $request, Operador $operador)
    {
        // (0) Normaliza a MAYÚSCULAS (antes de validar) — incluye ESTADO_CIVIL
        $this->normalizeRequestToUpper($request);

        // (1) Validación de campos (ignorando unique de CURP/RFC en este operador) — incluye DOMICILIO
        $data = $this->validateOperador($request, isUpdate: true, operador: $operador);

        // (2) Actualizar modelo
        $operador->update($data);

        // (3) Actualizar correo del User (opcional, sin cambiar mayúsculas/minúsculas)
        if ($request->filled('email') && $operador->user) {
            $request->validate([
                'email' => ['nullable', 'string', 'email:rfc', Rule::unique('users', 'email')->ignore($operador->user->id)],
            ]);
            $operador->user->update(['email' => $request->string('email')->toString()]);
        }

        // (4) Borrar fotos seleccionadas
        $toDelete = $request->input('delete_fotos', []);
        if (!empty($toDelete) && is_array($toDelete)) {
            $fotos = OperadorFoto::whereIn('id', $toDelete)
                ->where('operador_id', $operador->id)
                ->get();

            // Borra primero los archivos físicos, luego las filas
            $disk = Storage::disk(self::DISK);
            foreach ($fotos as $foto) {
                try { $disk->delete($foto->ruta); } catch (\Throwable $e) {}
                $foto->delete();
            }
        }

        // (5) Subir nuevas fotos si hay
        if ($request->hasFile('fotos')) {
            $this->validatePhotos($request, nullable: false);
            $this->saveUploadedPhotos($request, $operador->id);
        }

        return redirect()
            ->route('operadores.edit', $operador)
            ->with('success', 'Operador actualizado correctamente.');
    }

    /**
     * Elimina al Operador, su User asociado y todas sus fotos con limpieza de carpetas.
     */
    public function destroy(Operador $operador)
    {
        $operador->load(['fotos', 'user']);
        $user        = $operador->user;
        $operadorId  = $operador->id;

        // Capturamos rutas para limpieza en disco post-transacción
        $fotoRutas = $operador->fotos->pluck('ruta')->all();

        // (2) Borrado en BD
        DB::transaction(function () use ($operador, $user) {
            // Borrar filas de fotos del operador
            $operador->fotos()->delete();

            // Borrar operador
            $operador->delete();

            // Borrar el usuario ligado (ajustar si no deseas borrarlo en ciertos casos)
            $this->deleteUserCompletely($user);
        });

        // (3) Borrado físico de archivos + limpieza de directorios
        $this->deletePhysicalFilesAndDirs($fotoRutas, self::BASE_DIR . '/' . $operadorId);

        return redirect()
            ->route('operadores.index')
            ->with('success', 'Operador eliminado correctamente.');
    }

    /**
     * Normaliza el Request para que ciertos campos queden en MAYÚSCULAS (y con espacios colapsados).
     * No altera email ni teléfonos.
     */
    private function normalizeRequestToUpper(Request $request): void
    {
        // Llaves a forzar MAYÚSCULAS (agrega/quita según tu modelo)
        // OJO: ESTADO_CIVIL se normaliza a MAYÚSCULAS aquí.
        $upperKeys = [
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'contacto_emergencia_nombre',
            'contacto_emergencia_parentesco',
            'contacto_emergencia_ubicacion',
            'tipo_sangre',
            'estado_civil',
            'curp',
            'rfc',
            // NOTA: "domicilio" lo dejamos sin upper por si quieres respetar minúsculas/mayúsculas del usuario.
            // Si deseas que sea en mayúsculas, agrega 'domicilio' aquí.
        ];

        $normalize = [];
        foreach ($upperKeys as $key) {
            if ($request->has($key)) {
                $val = (string) $request->input($key);
                // Trim y colapsar espacios múltiples
                $val = trim(preg_replace('/\s+/u', ' ', $val) ?? '');
                // MAYÚSCULAS UTF-8
                $val = Str::upper($val);
                $normalize[$key] = $val;
            }
        }

        // También limpiamos domicilio (sin upper): trim + colapsar espacios
        if ($request->has('domicilio')) {
            $dom = (string) $request->input('domicilio');
            $dom = trim(preg_replace('/\s+/u', ' ', $dom) ?? '');
            $normalize['domicilio'] = $dom;
        }

        if (!empty($normalize)) {
            $request->merge($normalize);
        }
    }

    /**
     * Valida los campos comunes del Operador.
     *
     * @param  Request       $request
     * @param  bool          $isUpdate  Modo actualización (true) o creación (false).
     * @param  Operador|null $operador  Para ignorar reglas unique en update.
     * @return array
     */
    private function validateOperador(Request $request, bool $isUpdate, ?Operador $operador = null): array
    {
        $ignoreId = $operador?->id;

        // Nota: el email se valida aparte (en store/update) porque pertenece al User.
        return $request->validate([
            'user_id'                     => ['nullable', 'exists:users,id'],
            'nombre'                      => ['required', 'string', 'max:255'],
            'apellido_paterno'            => ['required', 'string', 'max:255'],
            'apellido_materno'            => ['nullable', 'string', 'max:255'],

            // Contacto / datos existentes
            'telefono'                    => ['nullable', 'string', 'max:20'],
            'contacto_emergencia_nombre'  => ['nullable', 'string', 'max:255'],
            'contacto_emergencia_tel'     => ['nullable', 'string', 'max:20'],
            'tipo_sangre'                 => ['nullable', 'string', 'max:5'],

            // ===== NUEVO: DOMICILIO =====
            'domicilio'                   => ['nullable', 'string', 'max:255'],

            // ===== Nuevos campos (validados en MAYÚSCULAS) =====
            'estado_civil' => [
                'nullable',
                Rule::in(['SOLTERO','CASADO','VIUDO','DIVORCIADO']),
            ],
            'curp' => [
                'nullable',
                'string',
                'size:18',
                // 18 alfanumérico en MAYÚSCULAS
                'regex:/^[A-ZÑ0-9]{18}$/',
                Rule::unique('operadores', 'curp')->ignore($ignoreId),
            ],
            'rfc' => [
                'nullable',
                'string',
                'min:12',
                'max:13',
                // RFC PM (12) o PF (13) en MAYÚSCULAS: 3-4 letras (&/Ñ permitidas) + 6 dígitos fecha + 3 alfanum.
                'regex:/^([A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}$/',
                Rule::unique('operadores', 'rfc')->ignore($ignoreId),
            ],
            'contacto_emergencia_parentesco' => ['nullable', 'string', 'max:100'],
            'contacto_emergencia_ubicacion'  => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.exists'                => 'El usuario seleccionado no es válido.',
            'nombre.required'               => 'El nombre es obligatorio.',
            'apellido_paterno.required'     => 'El apellido paterno es obligatorio.',
            'estado_civil.in'               => 'El estado civil debe ser: SOLTERO, CASADO, VIUDO o DIVORCIADO.',
            'curp.size'                     => 'La CURP debe tener 18 caracteres.',
            'curp.regex'                    => 'La CURP debe ser alfanumérica en mayúsculas (18 chars).',
            'curp.unique'                   => 'La CURP ya está registrada para otro operador.',
            'rfc.min'                       => 'El RFC debe tener entre 12 y 13 caracteres.',
            'rfc.max'                       => 'El RFC debe tener entre 12 y 13 caracteres.',
            'rfc.regex'                     => 'El RFC no cumple el formato esperado (usa mayúsculas).',
            'rfc.unique'                    => 'El RFC ya está registrado para otro operador.',
        ]);
    }

    /**
     * Valida el arreglo de fotos según reglas comunes del módulo.
     *
     * @param  Request $request
     * @param  bool    $nullable  Si se permite que no vengan fotos.
     */
    private function validatePhotos(Request $request, bool $nullable = true): void
    {
        $rules = [
            'fotos'   => [$nullable ? 'nullable' : 'required', 'array', 'max:' . self::PHOTOS_MAX_FILES],
            'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::PHOTOS_MAX_SIZE_KB],
        ];

        $messages = [
            'fotos.required' => 'Debes seleccionar al menos una imagen.',
            'fotos.array'    => 'El campo de fotos debe ser un arreglo de archivos.',
            'fotos.max'      => 'No puedes subir más de ' . self::PHOTOS_MAX_FILES . ' imágenes a la vez.',
            'fotos.*.image'  => 'Cada archivo debe ser una imagen.',
            'fotos.*.mimes'  => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'fotos.*.max'    => 'Cada imagen no debe superar los ' . floor(self::PHOTOS_MAX_SIZE_KB / 1024) . ' MB.',
        ];

        $request->validate($rules, $messages);
    }

    /**
     * Sube las fotos del request y crea sus filas en OperadorFoto.
     *
     * @param  Request $request
     * @param  int     $operadorId
     */
    private function saveUploadedPhotos(Request $request, int $operadorId): void
    {
        if (!$request->hasFile('fotos')) {
            return;
        }

        $disk = Storage::disk(self::DISK);
        $dir  = self::BASE_DIR . '/' . $operadorId;

        foreach ($request->file('fotos', []) as $file) {
            if (!$file instanceof UploadedFile) { continue; }

            $filename = $this->buildPhotoFilename($operadorId, $file);
            $relative = $file->storeAs($dir, $filename, self::DISK);

            OperadorFoto::create([
                'operador_id' => $operadorId,
                'ruta'        => $relative,
                'orden'       => 0,
            ]);
        }
    }

    /**
     * Genera un nombre de archivo único y legible para una foto de Operador.
     *
     * Formato: YYYYmmdd_His_{operadorId}_{uuid}.{ext}
     */
    private function buildPhotoFilename(int $operadorId, UploadedFile $file): string
    {
        $timestamp = now()->format('Ymd_His');
        $uuid      = Str::uuid();
        $ext       = $file->getClientOriginalExtension();

        return $timestamp . '_' . $operadorId . '_' . $uuid . '.' . $ext;
        }

    /**
     * Elimina el usuario completamente, limpiando tokens y roles si están disponibles.
     *
     * @param  User|null $user
     */
    private function deleteUserCompletely(?User $user): void
    {
        if (!$user) {
            return;
        }

        // Revoca tokens (Sanctum)
        if (method_exists($user, 'tokens')) {
            try { $user->tokens()->delete(); } catch (\Throwable $e) {}
        }

        // Limpia roles (Spatie)
        if (method_exists($user, 'syncRoles')) {
            try { $user->syncRoles([]); } catch (\Throwable $e) {}
        }

        // Borra usuario
        $user->delete();
    }

    /**
     * Borra archivos físicos y limpia directorios derivados + base.
     *
     * @param  array  $filePaths  Rutas relativas (dentro del DISK) a borrar.
     * @param  string $baseDir    Directorio base a intentar eliminar al final (e.g., 'operadores/{id}').
     */
    private function deletePhysicalFilesAndDirs(array $filePaths, string $baseDir): void
    {
        $disk = Storage::disk(self::DISK);

        // (1) Borra archivos individuales
        foreach ($filePaths as $path) {
            try { $disk->delete($path); } catch (\Throwable $e) {}
        }

        // (2) Intenta borrar primero subdirectorios (de lo más profundo a lo más superficial)
        $dirs = collect($filePaths)
            ->map(fn ($p) => trim(str_replace('\\', '/', dirname($p)), '/'))
            ->filter()
            ->unique()
            ->sortByDesc(fn ($d) => strlen($d));

        foreach ($dirs as $dir) {
            // Seguridad básica: sólo dentro de BASE_DIR
            if (\Str::startsWith($dir, self::BASE_DIR . '/')) {
                try { $disk->deleteDirectory($dir); } catch (\Throwable $e) {}
            }
        }

        // (3) Finalmente, intenta borrar la carpeta base
        if (\Str::startsWith($baseDir, self::BASE_DIR . '/')) {
            try { $disk->deleteDirectory($baseDir); } catch (\Throwable $e) {}
        }
    }
}
