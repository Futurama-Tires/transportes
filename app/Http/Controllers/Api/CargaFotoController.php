<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CargaCombustible;
use App\Models\CargaFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CargaFotoController extends Controller
{
    /**
     * GET /api/cargas/{carga}/fotos
     * Query params opcionales:
     *  - tipo: ticket|voucher|odometro|extra
     *  - paginate: true|false (default false)
     *  - per_page: 1..100 (default 50)
     */
    public function index(Request $request, CargaCombustible $carga)
    {
        $query = $carga->fotos()->orderBy('id', 'desc');

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($request->boolean('paginate', false)) {
            $perPage = max(1, min((int)$request->query('per_page', 50), 100));
            $fotos = $query->paginate($perPage)->appends($request->query());
        } else {
            $fotos = $query->get();
        }

        // CargaFoto ya expone "url" vía accessor -> appends
        return response()->json($fotos);
    }

    /**
     * GET /api/cargas/{carga}/fotos/{foto}
     */
    public function show(Request $request, CargaCombustible $carga, CargaFoto $foto)
    {
        if ($foto->carga_id !== $carga->id) {
            return response()->json(['message' => 'Foto no pertenece a la carga'], 404);
        }
        return response()->json($foto);
    }

    /**
     * GET /api/cargas/{carga}/fotos/{foto}/download
     * Devuelve el archivo (útil si NO expones /storage públicamente).
     */
    public function download(Request $request, CargaCombustible $carga, CargaFoto $foto)
    {
        if ($foto->carga_id !== $carga->id) {
            return response()->json(['message' => 'Foto no pertenece a la carga'], 404);
        }

        $disk = Storage::disk('public');
        if (!$foto->path || !$disk->exists($foto->path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        $name = $foto->original_name ?: basename($foto->path);
        // Usa response() para ver en navegador; usa download() si quieres forzar descarga
        return $disk->response($foto->path, $name);
        // return $disk->download($foto->path, $name); // ← alternativa
    }

    /**
     * POST /api/cargas/{carga}/fotos
     * Body: image (archivo), tipo (ticket|voucher|odometro|extra)
     */
    public function store(Request $request, CargaCombustible $carga)
    {
        $data = $request->validate([
            'tipo'  => ['nullable', 'in:ticket,voucher,odometro,extra'],
            'image' => ['required', 'image', 'max:10240'], // hasta ~10MB
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

        $foto = CargaFoto::create([
            'carga_id'      => $carga->id,
            'tipo'          => $data['tipo'] ?? CargaFoto::EXTRA,
            'path'          => $path,
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json($foto, 201);
    }

    /**
     * DELETE /api/cargas/{carga}/fotos/{foto}
     */
    public function destroy(Request $request, CargaCombustible $carga, CargaFoto $foto)
    {
        if ($foto->carga_id !== $carga->id) {
            return response()->json(['message' => 'Foto no pertenece a la carga'], 404);
        }

        $disk = Storage::disk('public');
        if ($foto->path && $disk->exists($foto->path)) {
            $disk->delete($foto->path);
        }
        $foto->delete();

        return response()->json(['ok' => true]);
    }
}
