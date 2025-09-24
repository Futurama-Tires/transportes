<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use App\Models\OperadorFoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

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
     * Listado con filtros y paginación.
     */
    public function index(Request $request)
    {
        $operadores = Operador::with(['user'])
            ->withCount('fotos')
            ->filter($request->all())
            ->paginate(25)
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
     *   1) Valida datos, email único y (opcionalmente) fotos.
     *   2) Crea User + Operador dentro de transacción.
     *   3) Guarda fotos (fuera de transacción).
     *   4) Redirige con modal de credenciales.
     */
    public function store(Request $request)
    {
        // (1) Validación de campos básicos
        $data = $this->validateOperador($request, isUpdate: false);

        // Email único para el User que se creará
        $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')],
        ]);

        // Validación de fotos para fallar pronto si no cumplen
        $this->validatePhotos($request, nullable: true);

        // (2) Crear User + Operador dentro de transacción
        $payload = DB::transaction(function () use ($request, $data) {
            $passwordPlain = Str::password(12);

            $user = User::create([
                'name'     => trim(($data['nombre'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                'email'    => $request->string('email')->toString(),
                'password' => Hash::make($passwordPlain),
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
        return redirect()
            ->route('operadores.create')
            ->with([
                'success'  => 'Operador creado correctamente.',
                'created'  => true,
                'email'    => $payload['user']->email,
                'password' => $payload['passwordPlain'],
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
     *   1) Validar datos del operador.
     *   2) Actualizar operador.
     *   3) (Opcional) actualizar email del User ligado.
     *   4) Borrar fotos marcadas (BD + archivo).
     *   5) Validar/subir nuevas fotos.
     */
    public function update(Request $request, Operador $operador)
    {
        // (1) Validación de campos
        $data = $this->validateOperador($request, isUpdate: true);

        // (2) Actualizar modelo
        $operador->update($data);

        // (3) Actualizar correo del User (opcional)
        if ($request->filled('email') && $operador->user) {
            $request->validate([
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($operador->user->id)],
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
     *
     * Flujo:
     *   1) Cargar relaciones y captar rutas/ID para limpieza posterior.
     *   2) Transacción: borrar filas de fotos, operador y usuario.
     *   3) Fuera de transacción: borrar archivos y directorios vacíos.
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
            ->with('success', 'Operador, usuario y carpetas de fotos eliminados correctamente.');
    }

    /**
     * Valida los campos comunes del Operador.
     *
     * @param  Request $request
     * @param  bool    $isUpdate  Modo actualización (true) o creación (false).
     * @return array
     */
    private function validateOperador(Request $request, bool $isUpdate): array
    {
        // Nota: el email se valida aparte (en store/update) porque pertenece al User.
        return $request->validate([
            'user_id'                     => ['nullable', 'exists:users,id'],
            'nombre'                      => ['required', 'string', 'max:255'],
            'apellido_paterno'            => ['required', 'string', 'max:255'],
            'apellido_materno'            => ['nullable', 'string', 'max:255'],

            // Campos adicionales (opcionales)
            'telefono'                    => ['nullable', 'string', 'max:20'],
            'contacto_emergencia_nombre'  => ['nullable', 'string', 'max:255'],
            'contacto_emergencia_tel'     => ['nullable', 'string', 'max:20'],
            'tipo_sangre'                 => ['nullable', 'string', 'max:5'],
        ], [
            'user_id.exists'      => 'El usuario seleccionado no es válido.',
            'nombre.required'     => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
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
            if (Str::startsWith($dir, self::BASE_DIR . '/')) {
                try { $disk->deleteDirectory($dir); } catch (\Throwable $e) {}
            }
        }

        // (3) Finalmente, intenta borrar la carpeta base
        if (Str::startsWith($baseDir, self::BASE_DIR . '/')) {
            try { $disk->deleteDirectory($baseDir); } catch (\Throwable $e) {}
        }
    }
}
