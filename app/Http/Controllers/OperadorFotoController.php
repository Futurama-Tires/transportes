<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use App\Models\OperadorFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OperadorFotoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /** Página de gestión (opcional) */
    public function index(Operador $operador)
    {
        $operador->load('fotos'); // define relación en Operador: hasMany(OperadorFoto::class)
        return view('operadores.fotos.index', compact('operador'));
    }

    /** Subida de una o varias imágenes (privadas). */
    public function store(Request $request, Operador $operador)
    {
        $request->validate([
            'fotos'   => ['required', 'array'],
            'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'fotos.required' => 'Selecciona al menos una imagen.',
            'fotos.*.image'  => 'Cada archivo debe ser una imagen.',
            'fotos.*.mimes'  => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'fotos.*.max'    => 'Cada imagen no debe superar los 8 MB.',
        ]);

        $saved = 0;

        foreach ($request->file('fotos', []) as $file) {
            $dir = "operadores/{$operador->id}";
            $filename = now()->format('Ymd_His') . '_' . $operador->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Guarda en disco local (privado)
            $relativePath = $file->storeAs($dir, $filename, 'local');

            OperadorFoto::create([
                'operador_id' => $operador->id,
                'ruta'        => $relativePath,
                'orden'       => 0,
            ]);

            $saved++;
        }

        return back()->with('success', "Se subieron {$saved} foto(s).");
    }

    /** Elimina una foto (archivo + fila) validando pertenencia. */
    public function destroy(Operador $operador, OperadorFoto $foto)
    {
        abort_unless($foto->operador_id === $operador->id, 404);

        // El archivo se borra en el hook deleting() del modelo
        $foto->delete();

        return back()->with('success', 'Foto eliminada.');
    }

    /** Sirve la imagen privada. */
    public function show(OperadorFoto $foto)
    {
        $path = Storage::disk('local')->path($foto->ruta);
        if (! file_exists($path)) abort(404);

        $mime = File::mimeType($path) ?: 'application/octet-stream';

        // Para mejor UX en galería puedes cachear privadamente:
        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }
}
