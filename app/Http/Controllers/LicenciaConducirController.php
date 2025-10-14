<?php

namespace App\Http\Controllers;

use App\Models\LicenciaConducir;
use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LicenciaConducirController extends Controller
{
    /** Disco de Laravel (privado). */
    private const DISK = 'local';

    /** Carpeta base para archivos de licencias. */
    private const BASE_DIR = 'licencias';

    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /**
     * Lista de licencias (filtros: search, ambito, estatus).
     * Se eliminó filtro por operador_id y la columna ID.
     */
    public function index(Request $request)
    {
        $licencias = LicenciaConducir::with(['operador', 'archivos'])
            ->search($request->input('search'))
            ->ambito($request->input('ambito'))
            ->estatus($request->input('estatus')) // vigente|por_vencer|vencida
            ->orderByDesc('fecha_vencimiento')
            ->paginate(25)
            ->withQueryString();

        return view('licencias.index', compact('licencias'));
    }

    /**
     * Form de creación.
     */
    public function create(Request $request)
    {
        $operador = null;
        if ($request->filled('operador_id')) {
            $operador = Operador::find($request->integer('operador_id'));
        }
        return view('licencias.create', compact('operador'));
    }

    /**
     * Crear licencia.
     */
    public function store(Request $request)
    {
        $data = $this->validateLicencia($request, false, null);
        $licencia = LicenciaConducir::create($data);

        return redirect()
            ->route('operadores.edit', $licencia->operador_id)
            ->with('success', 'Licencia creada correctamente.');
    }

    /**
     * Mostrar licencia (detalle).
     */
    public function show(LicenciaConducir $licencia)
    {
        $licencia->load(['operador', 'archivos']);
        return view('licencias.show', compact('licencia'));
    }

    /**
     * Editar licencia.
     */
    public function edit(LicenciaConducir $licencia)
    {
        $licencia->load(['operador', 'archivos']);
        return view('licencias.edit', compact('licencia'));
    }

    /**
     * Actualizar licencia.
     */
    public function update(Request $request, LicenciaConducir $licencia)
    {
        $data = $this->validateLicencia($request, true, $licencia);
        $licencia->update($data);

        return redirect()
            ->route('operadores.edit', $licencia->operador_id)
            ->with('success', 'Licencia actualizada correctamente.');
    }

    /**
     * Eliminar licencia + archivos físicos.
     */
    public function destroy(LicenciaConducir $licencia)
    {
        $licencia->load('archivos');
        $paths      = $licencia->archivos->pluck('ruta')->all();
        $dir        = self::BASE_DIR . '/' . $licencia->id;
        $operadorId = $licencia->operador_id;

        DB::transaction(function () use ($licencia) {
            $licencia->archivos()->delete();
            $licencia->delete();
        });

        $disk = Storage::disk(self::DISK);
        foreach ($paths as $p) {
            try { $disk->delete($p); } catch (\Throwable $e) {}
        }
        try { $disk->deleteDirectory($dir); } catch (\Throwable $e) {}

        return redirect()
            ->route('operadores.edit', $operadorId)
            ->with('success', 'Licencia y archivos asociados eliminados correctamente.');
    }

    /**
     * Validación y normalización mínima de datos de licencia.
     * (Se normaliza previo a validar para que la unicidad de 'folio' sea consistente)
     */
    private function validateLicencia(Request $request, bool $isUpdate, ?LicenciaConducir $licencia): array
    {
        $ignoreId = $licencia?->id;

        // Normalizaciones ligeras antes de validar (coherencia en unique folio)
        if ($request->has('ambito')) {
            $request->merge(['ambito' => strtolower(trim((string) $request->input('ambito')))]);
        }
        if ($request->has('tipo')) {
            $request->merge(['tipo' => strtoupper(trim((string) $request->input('tipo')))]);
        }
        if ($request->has('folio')) {
            $folio = strtoupper(preg_replace('/\s+/', '', (string) $request->input('folio')));
            $request->merge(['folio' => $folio]);
        }

        return $request->validate([
            'operador_id'        => ['required', 'exists:operadores,id'],
            'ambito'             => ['nullable', Rule::in(['federal','estatal'])],
            'tipo'               => ['nullable', 'string', 'max:50'],
            'folio'              => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('licencias_conducir', 'folio')->ignore($ignoreId),
                // Si deseas unicidad por (folio, ambito), usa un índice compuesto en DB y:
                // Rule::unique('licencias_conducir')->where(fn($q)=>$q->where('ambito',$request->input('ambito')))->ignore($ignoreId),
            ],
            'fecha_expedicion'   => ['nullable', 'date', 'before_or_equal:fecha_vencimiento'],
            'fecha_vencimiento'  => ['nullable', 'date', 'after_or_equal:fecha_expedicion'],
            'emisor'             => ['nullable', 'string', 'max:100'],
            'estado_emision'     => ['nullable', 'string', 'max:100'],
            'observaciones'      => ['nullable', 'string', 'max:5000'],
        ], [
            'operador_id.required' => 'Debes seleccionar un operador.',
            'operador_id.exists'   => 'El operador seleccionado no existe.',
            'ambito.in'            => 'Ámbito inválido (usa federal o estatal).',
            'folio.unique'         => 'El folio de licencia ya existe.',
            'fecha_expedicion.before_or_equal' => 'La fecha de expedición no puede ser posterior a la de vencimiento.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a la de expedición.',
        ]);
    }
}
