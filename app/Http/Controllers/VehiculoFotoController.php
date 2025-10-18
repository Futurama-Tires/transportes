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
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /** Página para gestionar fotos de un vehículo (vista dedicada). */
    public function index(Vehiculo $vehiculo)
    {
        $vehiculo->load('fotos');
        return view('vehiculos.fotos.index', compact('vehiculo'));
    }

    /** Subir una o varias fotos (almacenadas en DISCO PRIVADO: 'local'). */
    public function store(Request $request, Vehiculo $vehiculo)
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
        $disk  = 'local'; // PRIVADO

        foreach ($request->file('fotos', []) as $file) {
            $dir = "vehiculos/{$vehiculo->id}";
            $filename = now()->format('Ymd_His') . '_' . $vehiculo->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

            $relativePath = $file->storeAs($dir, $filename, $disk);

            VehiculoFoto::create([
                'vehiculo_id' => $vehiculo->id,
                'ruta'        => $relativePath,
                'orden'       => 0,
            ]);

            $saved++;
        }

        return back()->with('success', "Se subieron {$saved} foto(s).");
    }

    /**
     * Mostrar una foto PRIVADA por ID (ruta directa: /vehiculos/fotos/{foto}).
     * NOTA: solo recibe el modelo 'VehiculoFoto' para evitar 404 por binding.
     */
    public function show(VehiculoFoto $foto)
    {
        $path = Storage::disk('local')->path($foto->ruta);
        if (!is_file($path)) {
            abort(404, 'Imagen no encontrada.');
        }

        $mime = File::mimeType($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    /**
     * Eliminar una foto (y su archivo físico).
     * Se mantiene para pantallas dedicadas; en edición principal se elimina por marcado.
     */
    public function destroy(Vehiculo $vehiculo, VehiculoFoto $foto)
    {
        abort_unless($foto->vehiculo_id === $vehiculo->id, 404);

        Storage::disk('local')->delete($foto->ruta);
        $foto->delete();

        return back()->with('success', 'Foto eliminada.');
    }
}
