<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperadorController extends Controller
{
    public function index(Request $request)
{
    $query = Operador::with('user');

    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('apellido_paterno', 'like', "%{$search}%")
              ->orWhere('apellido_materno', 'like', "%{$search}%")
              ->orWhereHas('user', function($u) use ($search) {
                  $u->where('email', 'like', "%{$search}%");
              });
        });
    }

    $query->orderBy('nombre')
          ->orderBy('apellido_paterno')
          ->orderBy('apellido_materno');

    $operadores = $query->paginate(10);

    return view('operadores.index', compact('operadores'));
}


    public function create()
    {
        return view('operadores.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'nullable|string|max:255',
        'email' => ['required', 'email', 'regex:/@futuramatiresmx\.com$/i', 'unique:users,email'],
    ]);

    $randomPassword = Str::random(10);

    // Crear usuario
    $user = User::create([
        'name' => $request->nombre . ' ' . $request->apellido_paterno,
        'email' => $request->email,
        'password' => Hash::make($randomPassword),
    ]);

    $user->assignRole('operador');

    // Crear operador
    Operador::create([
        'user_id' => $user->id,
        'nombre' => $request->nombre,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
    ]);

    // Redirige a la misma vista de crear con datos en sesión para el modal
    return redirect()
        ->route('operadores.create')
        ->with([
            'created'  => true,
            'email'    => $user->email,
            'password' => $randomPassword,
        ]);
}

    public function edit($id)
    {
        $operador = Operador::with('user')->findOrFail($id);
        return view('operadores.edit', compact('operador'));
    }

    public function update(Request $request, $id)
{
    // 1) Trae al operador con su usuario
    $operador = Operador::with('user')->findOrFail($id);

    // 2) Valida ignorando el ID del usuario actual
    $validated = $request->validate([
        'nombre'            => ['required','string','max:255'],
        'apellido_paterno'  => ['required','string','max:255'],
        'apellido_materno'  => ['nullable','string','max:255'],
        'email' => [
            'required',
            'email',
            'regex:/@futuramatiresmx\.com$/i',
            Rule::unique('users', 'email')->ignore($operador->user->id), // 👈 clave
        ],
    ]);

    // 3) Actualiza el operador
    $operador->update([
        'nombre'            => $validated['nombre'],
        'apellido_paterno'  => $validated['apellido_paterno'],
        'apellido_materno'  => $validated['apellido_materno'] ?? null,
    ]);

    // 4) Actualiza el usuario (email solo si cambió)
    $operador->user->update([
        'name'  => $validated['nombre'].' '.$validated['apellido_paterno'],
        'email' => $validated['email'], // puedes envolver en if si quieres tocarlo solo cuando cambie
    ]);

    return redirect()
        ->route('operadores.index')
        ->with('success', 'Operador actualizado correctamente');
}

    public function destroy($id)
    {
        $operador = Operador::findOrFail($id);
        $operador->user->delete(); // Elimina también el operador por la relación ON DELETE CASCADE
        return redirect()->route('operadores.index')->with('success', 'Operador eliminado correctamente');
    }
}
