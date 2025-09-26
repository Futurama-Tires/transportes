<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /** Muestra el formulario para capturar el correo. */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /** Envía el enlace de restablecimiento al correo indicado. */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Intenta enviar el enlace (si el usuario existe y está habilitado).
        $status = Password::sendResetLink($request->only('email'));

        // Para evitar enumeración de usuarios, devolvemos siempre un mensaje genérico de éxito.
        return back()->with(
            'status',
            $status === Password::RESET_LINK_SENT
                ? __('Te enviamos un enlace para restablecer tu contraseña (si el correo existe).')
                : __('Te enviamos un enlace para restablecer tu contraseña (si el correo existe).')
        );
    }
}
