<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Capturista;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CapturistaController extends Controller
{
    public function index(Request $request)
    {
        // Filtros usados en el index
        $filters = $request->only(['search', 'sort_by', 'sort_dir']);

        $capturistas = Capturista::query()
            ->with('user')
            ->filter($filters)
            ->paginate(25)           // paginación 
            ->withQueryString();     // conserva filtros al paginar

        return view('capturistas.index', compact('capturistas'));
    }

    public function create()
    {
        return view('capturistas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'            => 'required|string|max:255',
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'nullable|string|max:255',
            'email'             => ['required', 'email', 'regex:/@futuramatiresmx\.com$/i', 'unique:users,email'],
        ]);

        // Generar password temporal
        $randomPassword = Str::random(10);

        // Crear usuario
        $user = User::create([
            'name'     => trim($validated['nombre'].' '.$validated['apellido_paterno']),
            'email'    => $validated['email'],
            'password' => Hash::make($randomPassword),
        ]);

        // Asignar rol
        $user->assignRole('capturista');

        // Crear capturista
        Capturista::create([
            'user_id'          => $user->id,
            'nombre'           => $validated['nombre'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
        ]);

        // REDIRECCIÓN A CREATE CON FLASH PARA MOSTRAR EL MODAL
        return redirect()
            ->route('capturistas.create')
            ->with('created', true)
            ->with('email', $user->email)
            ->with('password', $randomPassword)
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit($id)
    {
        $capturista = Capturista::with('user')->findOrFail($id);
        return view('capturistas.edit', compact('capturista'));
    }

    public function update(Request $request, $id)
    {
        // Cargamos primero para poder ignorar el email actual del usuario relacionado
        $capturista = Capturista::with('user')->findOrFail($id);

        $validated = $request->validate([
            'nombre'            => 'required|string|max:255',
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'nullable|string|max:255',
            'email'             => [
                'required',
                'email',
                'regex:/@futuramatiresmx\.com$/i',
                // Ignoramos el email del usuario actual (no el id del capturista)
                Rule::unique('users', 'email')->ignore($capturista->user_id),
            ],
        ]);

        // Actualizar capturista
        $capturista->update([
            'nombre'           => $validated['nombre'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
        ]);

        // Actualizar usuario relacionado
        $capturista->user->update([
            'name'  => trim($validated['nombre'].' '.$validated['apellido_paterno']),
            'email' => $validated['email'],
        ]);

        return redirect()->route('capturistas.index')->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        $capturista = Capturista::with('user')->findOrFail($id);

        // Elimina el usuario (si la FK tiene ON DELETE CASCADE, eliminará el capturista)
        $capturista->user->delete();

        return redirect()->route('capturistas.index')->with('success', 'Registro eliminado correctamente');
    }
}
