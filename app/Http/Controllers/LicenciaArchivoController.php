<?php

namespace App\Http\Controllers;

use App\Models\LicenciaConducir;
use App\Models\LicenciaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LicenciaArchivoController extends Controller
{
    /** Disco privado. */
    private const DISK = 'local';

    /** Carpeta base para archivos de licencias. */
    private const BASE_DIR = 'licencias';

    /** Límite de archivos por envío. */
    private const MAX_FILES = 12;

    /** Tamaño máx por archivo en KB (10240 = 10 MB). */
    private const MAX_SIZE_KB = 10240;

    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /**
     * Subir uno o varios archivos a una licencia.
     * Espera input name="archivos[]"
     */
    public function store(Request $request, LicenciaConducir $licencia)
    {
        $this->validateFiles($request);

        $disk = Storage::disk(self::DISK);
        $dir  = self::BASE_DIR . '/' . $licencia->id;

        foreach ($request->file('archivos', []) as $file) {
            if (!$file) continue;

            $filename = $this->buildFilename($licencia->id, $file->getClientOriginalExtension());
            $relative = $file->storeAs($dir, $filename, self::DISK);

            LicenciaArchivo::create([
                'licencia_id'    => $licencia->id,
                'nombre_original'=> $file->getClientOriginalName(),
                'ruta'           => $relative,
                'mime'           => $file->getClientMimeType(),
                'size'           => $file->getSize(),
                'orden'          => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Archivo(s) de licencia cargado(s) correctamente.');
    }

    /**
     * Descargar archivo (forzar download).
     */
    public function download(LicenciaArchivo $archivo)
    {
        $this->authorizeAccess($archivo); // por si luego agregas policies

        $disk = Storage::disk(self::DISK);
        if (!$disk->exists($archivo->ruta)) {
            return redirect()->back()->withErrors(['archivo' => 'El archivo no existe en el servidor.']);
        }

        // Descarga con el nombre original
        return $disk->download($archivo->ruta, $archivo->nombre_original);
    }

    /**
     * Ver archivo inline (si el navegador lo soporta; útil para PDF/imagen).
     */
    public function inline(LicenciaArchivo $archivo)
    {
        $this->authorizeAccess($archivo);

        $disk = Storage::disk(self::DISK);
        if (!$disk->exists($archivo->ruta)) {
            return redirect()->back()->withErrors(['archivo' => 'El archivo no existe en el servidor.']);
        }

        $content = $disk->get($archivo->ruta);
        return response($content, 200, [
            'Content-Type'        => $archivo->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$archivo->nombre_original.'"',
        ]);
    }

    /**
     * Eliminar archivo (BD + físico).
     */
    public function destroy(LicenciaArchivo $archivo)
    {
        $this->authorizeAccess($archivo);

        $path = $archivo->ruta;
        $licenciaId = $archivo->licencia_id;

        $archivo->delete();

        $disk = Storage::disk(self::DISK);
        try { $disk->delete($path); } catch (\Throwable $e) {}

        // Limpia carpeta si quedó vacía
        $dir = self::BASE_DIR . '/' . $licenciaId;
        try { $disk->deleteDirectory($dir); } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Archivo eliminado correctamente.');
    }

    /* =====================
     * Helpers
     * ===================== */

    private function validateFiles(Request $request): void
    {
        $rules = [
            'archivos'   => ['required', 'array', 'max:' . self::MAX_FILES],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:' . self::MAX_SIZE_KB],
        ];

        $messages = [
            'archivos.required' => 'Debes seleccionar al menos un archivo.',
            'archivos.array'    => 'El campo de archivos debe ser un arreglo.',
            'archivos.max'      => 'No puedes subir más de ' . self::MAX_FILES . ' archivos a la vez.',
            'archivos.*.mimes'  => 'Formatos permitidos: PDF, JPG, JPEG, PNG, WEBP.',
            'archivos.*.max'    => 'Cada archivo no debe superar los ' . floor(self::MAX_SIZE_KB/1024) . ' MB.',
        ];

        $request->validate($rules, $messages);
    }

    private function buildFilename(int $licenciaId, string $ext): string
    {
        $ts  = now()->format('Ymd_His');
        $uid = (string) \Str::uuid();
        $ext = strtolower($ext ?: 'bin');
        return "{$ts}_{$licenciaId}_{$uid}.{$ext}";
    }

    private function authorizeAccess(LicenciaArchivo $archivo): void
    {
        // Punto central si luego implementas policies:
        // $this->authorize('view', $archivo);
        // Por ahora, el middleware de rol ya protege el módulo.
    }
}
