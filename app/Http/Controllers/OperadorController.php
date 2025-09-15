<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use App\Models\OperadorFoto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

    /** Persistir un operador nuevo. */
    public function store(Request $request)
    {
        $data = $this->validateOperador($request);

        $operador = Operador::create($data);

        return redirect()
            ->route('operadores.index')
            ->with('success', 'Operador creado correctamente.');
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
        // 1) Validación de campos del Operador
        $data = $this->validateOperador($request, $operador->id);

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
                // eliminar archivo físico si existe
                Storage::disk('local')->delete($foto->ruta);
                // eliminar registro
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

        // Opción A (recomendada): pasar el modelo
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

    /** Reglas de validación compartidas. */
    private function validateOperador(Request $request, $operadorId = null): array
    {
        return $request->validate([
            'user_id'          => ['nullable', 'exists:users,id'],
            'nombre'           => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.exists'   => 'El usuario seleccionado no es válido.',
            'nombre.required'  => 'El nombre es obligatorio.',
        ]);
    }
}
