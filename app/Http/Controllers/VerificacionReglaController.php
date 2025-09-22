<?php

namespace App\Http\Controllers;

use App\Models\VerificacionRegla;
use App\Models\CalendarioVerificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VerificacionReglaController extends Controller
{
    /* ===== Catálogos ===== */
    protected function catalogoEstados(): array
    {
        return [
            'Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua',
            'Ciudad de México','Coahuila','Colima','Durango','Guanajuato','Guerrero','Hidalgo','Jalisco',
            'México','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo',
            'San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'
        ];
    }
    protected function megalopolis(): array
    {
        return ['Ciudad de México','México','Hidalgo','Morelos','Puebla','Tlaxcala','Querétaro'];
    }

    /* ===== Normalización de estado ===== */
    protected function normalizeEstado(?string $s): string
    {
        $norm = $s ? Str::of($s)->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim() : '';
        $val = (string) $norm;

        if (in_array($val, [
            'ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'
        ], true)) {
            return 'EDO MEX';
        }

        return $val;
    }

    /* ===== Vistas ===== */
    public function index()
    {
        $reglas = VerificacionRegla::withCount('periodos')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('verificacion_reglas.index', [
            'reglas' => $reglas,
        ]);
    }

    public function create()
    {
        return view('verificacion_reglas.create', [
            'catalogoEstados' => $this->catalogoEstados(),
            'megalopolis'     => $this->megalopolis(),
        ]);
    }

    public function edit(VerificacionRegla $verificacion_regla)
    {
        return view('verificacion_reglas.edit', [
            'regla'           => $verificacion_regla,
            'catalogoEstados' => $this->catalogoEstados(),
            'megalopolis'     => $this->megalopolis(),
        ]);
    }

    public function generarForm(VerificacionRegla $verificacion_regla)
    {
        return view('verificacion_reglas.generar', [
            'regla' => $verificacion_regla,
        ]);
    }

    /* ===== Acciones ===== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => ['required', 'string', 'max:255'],
            'version'         => ['nullable', 'string', 'max:50'],
            'status'          => ['nullable', Rule::in(['draft','published','archived'])],
            'vigencia_inicio' => ['nullable', 'date'],
            'vigencia_fin'    => ['nullable', 'date', 'after_or_equal:vigencia_inicio'],
            'frecuencia'      => ['required', Rule::in(['Semestral','Anual'])],
            'estados'         => ['required', 'array', 'min:1'],
            'estados.*'       => ['string', 'max:100'],
            'notas'           => ['nullable','string'],
        ]);

        $data['status'] = $data['status'] ?? 'published';

        VerificacionRegla::create([
            'nombre'          => $data['nombre'],
            'version'         => $data['version'] ?? null,
            'status'          => $data['status'],
            'vigencia_inicio' => $data['vigencia_inicio'] ?? null,
            'vigencia_fin'    => $data['vigencia_fin'] ?? null,
            'frecuencia'      => $data['frecuencia'],
            'estados'         => $data['estados'], // guardamos legible; normalizamos hasta generar periodos
            'notas'           => $data['notas'] ?? null,
        ]);

        return redirect()->route('verificacion-reglas.index')
            ->with('success', 'Regla creada correctamente.');
    }

    public function update(Request $request, VerificacionRegla $verificacion_regla)
    {
        $data = $request->validate([
            'nombre'          => ['required', 'string', 'max:255'],
            'version'         => ['nullable', 'string', 'max:50'],
            'status'          => ['required', Rule::in(['draft','published','archived'])],
            'vigencia_inicio' => ['nullable', 'date'],
            'vigencia_fin'    => ['nullable', 'date', 'after_or_equal:vigencia_inicio'],
            'frecuencia'      => ['required', Rule::in(['Semestral','Anual'])],
            'estados'         => ['required', 'array', 'min:1'],
            'estados.*'       => ['string', 'max:100'],
            'notas'           => ['nullable','string'],
        ]);

        $verificacion_regla->update([
            'nombre'          => $data['nombre'],
            'version'         => $data['version'] ?? null,
            'status'          => $data['status'],
            'vigencia_inicio' => $data['vigencia_inicio'] ?? null,
            'vigencia_fin'    => $data['vigencia_fin'] ?? null,
            'frecuencia'      => $data['frecuencia'],
            'estados'         => $data['estados'],
            'notas'           => $data['notas'] ?? null,
        ]);

        return redirect()->route('verificacion-reglas.index')
            ->with('success', 'Regla actualizada.');
    }

    public function destroy(Request $request, VerificacionRegla $verificacion_regla)
    {
        $deletePeriodos = (bool) $request->boolean('deletePeriodos', false);

        DB::transaction(function () use ($verificacion_regla, $deletePeriodos) {
            if ($deletePeriodos) {
                CalendarioVerificacion::where('regla_id', $verificacion_regla->id)->delete();
            }
            $verificacion_regla->delete();
        });

        return redirect()->route('verificacion-reglas.index')
            ->with('success', 'Regla eliminada.');
    }

    public function generar(Request $request, VerificacionRegla $verificacion_regla)
    {
        $data = $request->validate([
            'anio'         => ['required', 'integer', 'min:2000', 'max:2999'],
            'sobrescribir' => ['nullable', 'boolean'],
        ]);

        $anio = (int) $data['anio'];
        $sobrescribir = (bool) ($data['sobrescribir'] ?? true);

        if (empty($verificacion_regla->estados) || !is_array($verificacion_regla->estados)) {
            return back()->withErrors(['estados'=>'La regla no tiene estados seleccionados.'])->withInput();
        }

        $bimestres = $this->defaultBimestralMapping();

        DB::transaction(function () use ($verificacion_regla, $anio, $sobrescribir, $bimestres) {
            if ($sobrescribir) {
                CalendarioVerificacion::where('regla_id', $verificacion_regla->id)
                    ->where('anio', $anio)
                    ->delete();
            }

            $toInsert = [];

            foreach ($verificacion_regla->estados as $estadoLegible) {
                $estadoNorm = $this->normalizeEstado($estadoLegible);

                foreach ($bimestres as $bm) {
                    $mi = $bm['mes_inicio'];
                    $mf = $bm['mes_fin'];
                    $semestre = $bm['semestre'];
                    $desde = Carbon::create($anio, $mi, 1)->startOfDay();
                    $hasta = Carbon::create($anio, $mf, 1)->endOfMonth()->endOfDay();

                    foreach ($bm['terminaciones'] as $digit) {
                        $toInsert[] = [
                            'regla_id'       => $verificacion_regla->id,
                            'estado'         => $estadoNorm, // <— guardamos normalizado
                            'terminacion'    => $digit,
                            'mes_inicio'     => $mi,
                            'mes_fin'        => $mf,
                            'semestre'       => $semestre,
                            'frecuencia'     => $verificacion_regla->frecuencia,
                            'anio'           => $anio,
                            'vigente_desde'  => $desde->toDateString(),
                            'vigente_hasta'  => $hasta->toDateString(),
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];
                    }
                }
            }

            if (!empty($toInsert)) {
                foreach (array_chunk($toInsert, 1000) as $chunk) {
                    CalendarioVerificacion::insert($chunk);
                }
            }
        });

        $count = CalendarioVerificacion::where('regla_id', $verificacion_regla->id)
            ->where('anio', $anio)
            ->count();

        return redirect()->route('verificacion-reglas.index')
            ->with('success', "Periodos generados para {$verificacion_regla->nombre} ({$anio}). Registros: {$count}");
    }

    /* ===== Mapa bimestral típico CAMe ===== */
    protected function defaultBimestralMapping(): array
    {
        return [
            ['clave'=>1,  'mes_inicio'=>1,  'mes_fin'=>2,  'semestre'=>1, 'terminaciones'=>[5,6]],
            ['clave'=>2,  'mes_inicio'=>2,  'mes_fin'=>3,  'semestre'=>1, 'terminaciones'=>[7,8]],
            ['clave'=>3,  'mes_inicio'=>3,  'mes_fin'=>4,  'semestre'=>1, 'terminaciones'=>[3,4]],
            ['clave'=>4,  'mes_inicio'=>4,  'mes_fin'=>5,  'semestre'=>1, 'terminaciones'=>[1,2]],
            ['clave'=>5,  'mes_inicio'=>5,  'mes_fin'=>6,  'semestre'=>1, 'terminaciones'=>[9,0]],
            ['clave'=>6,  'mes_inicio'=>7,  'mes_fin'=>8,  'semestre'=>2, 'terminaciones'=>[5,6]],
            ['clave'=>7,  'mes_inicio'=>8,  'mes_fin'=>9,  'semestre'=>2, 'terminaciones'=>[7,8]],
            ['clave'=>8,  'mes_inicio'=>9,  'mes_fin'=>10, 'semestre'=>2, 'terminaciones'=>[3,4]],
            ['clave'=>9,  'mes_inicio'=>10, 'mes_fin'=>11, 'semestre'=>2, 'terminaciones'=>[1,2]],
            ['clave'=>10, 'mes_inicio'=>11, 'mes_fin'=>12, 'semestre'=>2, 'terminaciones'=>[9,0]],
        ];
    }
}
