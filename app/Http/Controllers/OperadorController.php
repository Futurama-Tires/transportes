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

class OperadorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        $operadores = Operador::with(['user'])
            ->withCount('fotos')
            ->filter($request->all())
            ->paginate(25)
            ->withQueryString();

        return view('operadores.index', compact('operadores'));
    }

    /** Form de creación. */
    public function create()
    {
        return view('operadores.create');
    }

    /** Persistir un operador nuevo (crea también el User ligado). */
    public function store(Request $request)
    {
        // Validaciones
        $data = $this->validateOperador($request, isUpdate:false);
        $request->validate([
            'email' => ['required','email', Rule::unique('users','email')],
        ]);

        // Creamos todo y devolvemos lo necesario
        $payload = \DB::transaction(function () use ($request, $data) {
            $passwordPlain = \Illuminate\Support\Str::password(12);

            $user = \App\Models\User::create([
                'name'     => trim(($data['nombre'] ?? '').' '.($data['apellido_paterno'] ?? '').' '.($data['apellido_materno'] ?? '')),
                'email'    => $request->input('email'),
                'password' => \Illuminate\Support\Facades\Hash::make($passwordPlain),
            ]);

            if (method_exists($user, 'assignRole')) {
                try { $user->assignRole('operador'); } catch (\Throwable $e) {}
            }

            $data['user_id'] = $user->id;
            $operador = \App\Models\Operador::create($data);

            return compact('operador','user','passwordPlain');
        });

        // Volvemos a CREATE para que aparezca el modal que ya tienes ahí
        return redirect()
            ->route('operadores.create')
            ->with([
                'success'  => 'Operador creado correctamente.',
                'created'  => true,
                'email'    => $payload['user']->email,
                'password' => $payload['passwordPlain'],
            ]);
    }


    /** Mostrar detalles. */
    public function show(Operador $operador)
    {
        $operador->load(['user', 'fotos']);
        return view('operadores.show', compact('operador'));
    }

    /** Form de edición. */
    public function edit(Operador $operador)
    {
        $operador->load(['user','fotos']);
        return view('operadores.edit', compact('operador'));
    }

    /** Actualizar datos + subir/borrar fotos en un solo submit. */
    public function update(Request $request, Operador $operador)
    {
        // 1) Validación de campos del Operador (modo update)
        $data = $this->validateOperador($request, isUpdate:true);

        // 2) Actualizar Operador
        $operador->update($data);

        // 3) (Opcional) Actualizar email del usuario ligado, si existe y viene en request
        if ($request->filled('email') && $operador->user) {
            $request->validate([
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($operador->user->id)],
            ]);
            $operador->user->update(['email' => $request->input('email')]);
        }

        // 4) Borrar fotos marcadas
        $toDelete = $request->input('delete_fotos', []);
        if (!empty($toDelete) && is_array($toDelete)) {
            $fotos = OperadorFoto::whereIn('id', $toDelete)
                ->where('operador_id', $operador->id)
                ->get();

            foreach ($fotos as $foto) {
                Storage::disk('local')->delete($foto->ruta);
                $foto->delete();
            }
        }

        // 5) Subir nuevas fotos (si vienen)
        if ($request->hasFile('fotos')) {
            $request->validate([
                'fotos'   => ['array'],
                'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            ], [
                'fotos.*.image' => 'Cada archivo debe ser una imagen.',
                'fotos.*.mimes' => 'Formatos permitidos: jpg, jpeg, png, webp.',
                'fotos.*.max'   => 'Cada imagen no debe superar los 8 MB.',
            ]);

            foreach ($request->file('fotos', []) as $file) {
                $dir = "operadores/{$operador->id}";
                $filename = now()->format('Ymd_His') . '_' . $operador->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

                $relativePath = $file->storeAs($dir, $filename, 'local');

                OperadorFoto::create([
                    'operador_id' => $operador->id,
                    'ruta'        => $relativePath,
                    'orden'       => 0,
                ]);
            }
        }

        return redirect()
            ->route('operadores.edit', $operador)
            ->with('success', 'Operador actualizado correctamente.');
    }

    /** Eliminar operador (+ sus fotos). */
    public function destroy(Operador $operador)
    {
        $operador->load('fotos');

        foreach ($operador->fotos as $foto) {
            Storage::disk('local')->delete($foto->ruta);
            $foto->delete();
        }

        $operador->delete();

        return redirect()
            ->route('operadores.index')
            ->with('success', 'Operador eliminado correctamente.');
    }

    /**
     * Reglas de validación compartidas.
     * - En create: apellido_paterno y email requeridos (email se valida aparte en store()).
     * - En update: email opcional (se valida cuando viene).
     */
    private function validateOperador(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'user_id'                   => ['nullable', 'exists:users,id'],
            'nombre'                    => ['required', 'string', 'max:255'],
            'apellido_paterno'          => ['required', 'string', 'max:255'], // ← requerido como en tus vistas
            'apellido_materno'          => ['nullable', 'string', 'max:255'],

            // Campos nuevos (nullable)
            'telefono'                  => ['nullable', 'string', 'max:20'],
            'contacto_emergencia_nombre'=> ['nullable', 'string', 'max:255'],
            'contacto_emergencia_tel'   => ['nullable', 'string', 'max:20'],
            'tipo_sangre'               => ['nullable', 'string', 'max:5'],
        ], [
            'user_id.exists'   => 'El usuario seleccionado no es válido.',
            'nombre.required'  => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
        ]);
    }
}
