<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\VehiculoFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VehiculoFotoController extends Controller
{
    public function __construct()
    {
        // Solo admin y capturista
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /**
     * Página para gestionar fotos de un vehículo.
     */
    public function index(Vehiculo $vehiculo)
    {
        $vehiculo->load('fotos');
        return view('vehiculos.fotos.index', compact('vehiculo'));
    }

    /**
     * Subir una o varias fotos (privadas).
     * Se guardan en storage/app/vehiculos/{vehiculo_id}/...
     */
    public function store(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'fotos'   => ['required', 'array'],
            'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'], // 8MB c/u
        ], [
            'fotos.required'   => 'Selecciona al menos una imagen.',
            'fotos.*.image'    => 'Cada archivo debe ser una imagen.',
            'fotos.*.mimes'    => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'fotos.*.max'      => 'Cada imagen no debe superar los 8 MB.',
        ]);

        $saved = 0;

        foreach ($request->file('fotos', []) as $file) {
            $dir = "vehiculos/{$vehiculo->id}";
            $filename = now()->format('Ymd_His') . '_' . $vehiculo->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Guarda en disco local (privado)
            $relativePath = $file->storeAs($dir, $filename, 'local');

            VehiculoFoto::create([
                'vehiculo_id' => $vehiculo->id,
                'ruta'        => $relativePath, // p. ej. vehiculos/5/20250909_120101_5_uuid.jpg
                'orden'       => 0,
            ]);

            $saved++;
        }

        return back()->with('success', "Se subieron {$saved} foto(s).");
    }

    /**
     * Eliminar una foto (y su archivo físico).
     */
    public function destroy(Vehiculo $vehiculo, VehiculoFoto $foto)
    {
        // Seguridad: que la foto pertenezca al vehículo de la URL
        abort_unless($foto->vehiculo_id === $vehiculo->id, 404);

        // Borra archivo si existe
        Storage::disk('local')->delete($foto->ruta);

        // Borra registro
        $foto->delete();

        return back()->with('success', 'Foto eliminada.');
    }

    /**
     * Servir la imagen (privada) al navegador.
     */
    public function show(VehiculoFoto $foto)
    {
        // El middleware ya exige auth + rol
        $path = Storage::disk('local')->path($foto->ruta);

        if (! file_exists($path)) {
            abort(404);
        }

        $mime = File::mimeType($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }
}
