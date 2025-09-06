<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        // 1) Validamos entrada
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
            // Opcional: nombre del dispositivo para identificar el token
            'device_name' => ['sometimes','string','max:100'],
        ]);

        // 2) Buscamos al usuario
        $user = User::where('email', $data['email'])->first();

        // 3) Verificamos el password hasheado
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son válidas.'],
            ]);
        }

        // 4) Emitimos el token
        $device = $data['device_name'] ?? 'android';
        $token = $user->createToken($device)->plainTextToken;

        // 5) Respondemos con JSON (la app guardará este token)
        return response()->json([
            'message'     => 'Login correcto',
            'token'       => $token,
            'token_type'  => 'Bearer',
            'user'        => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    // GET /api/me (requiere token)
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // POST /api/logout (revoca el token actual)
    public function logout(Request $request)
    {
        // Revoca solo el token con el que viene esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada y token revocado.',
        ]);
    }
}
