<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CargaCombustible;
use App\Models\CargaFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CargaFotoWebController extends Controller
{
    /**
     * Muestra la imagen protegida (inline) por ID.
     * GET /cargas/fotos/{foto}
     */
    public function show(Request $request, CargaFoto $foto)
    {
        // Se asume middleware auth + roles ya aplicado en la ruta.
        $disk = Storage::disk('public');

        if (!$foto->path || !$disk->exists($foto->path)) {
            abort(404, 'Archivo no encontrado');
        }

        $name = $foto->original_name ?: basename($foto->path);
        return $disk->response($foto->path, $name);
        // Si quisieras forzar descarga:
        // return $disk->download($foto->path, $name);
    }

    /**
     * Sube una foto a la carga indicada.
     * POST /cargas/{carga}/fotos
     */
    public function store(Request $request, CargaCombustible $carga)
    {
        $data = $request->validate([
            'tipo'  => ['nullable', 'in:ticket,voucher,odometro,extra'],
            'image' => ['required', 'image', 'max:10240'], // 10 MB
        ]);

        $disk = Storage::disk('public');
        $dir  = "cargas/{$carga->id}";
        if (!$disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        $file = $request->file('image');
        $ext  = $file->getClientOriginalExtension() ?: 'jpg';
        $name = ($data['tipo'] ?? 'extra') . '-' . now()->format('Ymd-His') . '-' . uniqid() . '.' . $ext;

        $path = $file->storePubliclyAs($dir, $name, ['disk' => 'public']);

        CargaFoto::create([
            'carga_id'      => $carga->id,
            'tipo'          => $data['tipo'] ?? CargaFoto::EXTRA,
            'path'          => $path,
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Foto subida correctamente.');
    }

    /**
     * Borra una foto de la carga.
     * DELETE /cargas/{carga}/fotos/{foto}
     */
    public function destroy(Request $request, CargaCombustible $carga, CargaFoto $foto)
    {
        if ($foto->carga_id !== $carga->id) {
            return back()->with('error', 'La foto no pertenece a esta carga.');
        }

        $disk = Storage::disk('public');
        if ($foto->path && $disk->exists($foto->path)) {
            $disk->delete($foto->path);
        }

        $foto->delete();
        return back()->with('success', 'Foto eliminada.');
    }
}
