<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class OperadorController extends Controller
{
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

        $randomPassword = Str::random(10); // genera una contraseña aleatoria


        // Crear usuario
        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido_paterno,
            'email' => $request->email,
            'password' => Hash::make($randomPassword),
        ]);

        $user->assignRole('operador'); // Spatie

        // Crear operador
        Operador::create([
            'user_id' => $user->id,
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
        ]);

        // Mostrar al admin las credenciales generadas
        return view('operadores.confirmacion', [
            'email' => $user->email,
            'password' => $randomPassword,
        ]);

        return redirect()->route('operadores.create')->with('success', 'Operador creado correctamente');
    }
}
