<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAiVisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OcrController extends Controller
{
    public function ticket(Request $request, OpenAiVisionService $vision)
    {
        $path = $this->storeTempImage($request, 'ticket');
        $data = $vision->extractTicketData(Storage::path($path));

        return response()->json([
            'ok' => true,
            'type' => 'ticket',
            'data' => $data,
            'tmp_path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    public function voucher(Request $request, OpenAiVisionService $vision)
    {
        $path = $this->storeTempImage($request, 'voucher');
        $data = $vision->extractVoucherAmount(Storage::path($path));

        return response()->json([
            'ok' => true,
            'type' => 'voucher',
            'data' => $data,
            'tmp_path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    public function odometro(Request $request, OpenAiVisionService $vision)
    {
        $path = $this->storeTempImage($request, 'odometro');
        $data = $vision->extractOdometer(Storage::path($path));

        return response()->json([
            'ok' => true,
            'type' => 'odometro',
            'data' => $data,
            'tmp_path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    // ---------------- Helpers ----------------

    /**
     * Sube una imagen temporal al disco 'public' en /tmp/ocr/YYYY-MM/
     * Devuelve la ruta relativa dentro del disco (para Storage::path/url).
     */
    protected function storeTempImage(Request $request, string $prefix): string
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:8192'], // hasta ~8 MB
        ], [
            'image.required' => 'Falta la imagen.',
            'image.image'    => 'El archivo debe ser una imagen.',
            'image.max'      => 'La imagen es muy grande (máx ~8MB).',
        ]);

        $dir = 'tmp/ocr/' . now()->format('Y-m');
        $name = $prefix . '-' . now()->format('Ymd-His') . '-' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();

        $path = $request->file('image')->storePubliclyAs($dir, $name, ['disk' => 'public']);
        if (!$path) {
            throw ValidationException::withMessages(['image' => 'No se pudo guardar la imagen.']);
        }
        return $path; // ej. tmp/ocr/2025-09/ticket-20250915-130501-abc123.jpg
    }
}
