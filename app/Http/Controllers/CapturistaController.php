<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Capturista;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CapturistaController extends Controller
{
    public function index()
    {
        $capturistas = Capturista::with('user')->get();
        return view('capturistas.index', compact('capturistas'));
    }

    public function create()
    {
        return view('capturistas.create');
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

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido_paterno,
            'email' => $request->email,
            'password' => Hash::make($randomPassword),
        ]);

        $user->assignRole('capturista');

        Capturista::create([
            'user_id' => $user->id,
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
        ]);

        return view('capturistas.confirmacion', [
            'email' => $user->email,
            'password' => $randomPassword,
        ]);
    }

    public function edit($id)
    {
        $capturista = Capturista::with('user')->findOrFail($id);
        return view('capturistas.edit', compact('capturista'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'regex:/@futuramatiresmx\.com$/i', 'unique:users,email,' . $id],
        ]);

        $capturista = Capturista::findOrFail($id);
        $capturista->update([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
        ]);

        $capturista->user->update([
            'name' => $request->nombre . ' ' . $request->apellido_paterno,
            'email' => $request->email
        ]);

        return redirect()->route('capturistas.index')->with('success', 'Capturista actualizado correctamente');
    }

    public function destroy($id)
    {
        $capturista = Capturista::findOrFail($id);
        $capturista->user->delete(); // Elimina también capturista por la relación ON DELETE CASCADE
        return redirect()->route('capturistas.index')->with('success', 'Capturista eliminado correctamente');
    }
}
