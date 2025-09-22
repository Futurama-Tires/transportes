<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\VerificacionRegla;
use App\Models\CalendarioVerificacion;

class VerificacionReglas2025Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCame2025();
            $this->seedJalisco2025();
        });
    }

    /* ===================== Helpers ===================== */
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

    protected function bimestresCame(): array
    {
        return [
            ['mes_inicio'=>1,  'mes_fin'=>2,  'semestre'=>1, 'terminaciones'=>[5,6]],
            ['mes_inicio'=>2,  'mes_fin'=>3,  'semestre'=>1, 'terminaciones'=>[7,8]],
            ['mes_inicio'=>3,  'mes_fin'=>4,  'semestre'=>1, 'terminaciones'=>[3,4]],
            ['mes_inicio'=>4,  'mes_fin'=>5,  'semestre'=>1, 'terminaciones'=>[1,2]],
            ['mes_inicio'=>5,  'mes_fin'=>6,  'semestre'=>1, 'terminaciones'=>[9,0]],
            ['mes_inicio'=>7,  'mes_fin'=>8,  'semestre'=>2, 'terminaciones'=>[5,6]],
            ['mes_inicio'=>8,  'mes_fin'=>9,  'semestre'=>2, 'terminaciones'=>[7,8]],
            ['mes_inicio'=>9,  'mes_fin'=>10, 'semestre'=>2, 'terminaciones'=>[3,4]],
            ['mes_inicio'=>10, 'mes_fin'=>11, 'semestre'=>2, 'terminaciones'=>[1,2]],
            ['mes_inicio'=>11, 'mes_fin'=>12, 'semestre'=>2, 'terminaciones'=>[9,0]],
        ];
    }

    /* ===================== CAMe / Megalópolis 2025 ===================== */
    protected function seedCame2025(): void
    {
        // Estados CAMe normalizados: CDMX, EDO MEX, HIDALGO, MORELOS, PUEBLA, TLAXCALA, QUERETARO
        $estadosLegibles = ['Ciudad de México','México','Hidalgo','Morelos','Puebla','Tlaxcala','Querétaro'];
        $estados = array_map(fn($e)=>$this->normalizeEstado($e), $estadosLegibles);

        $regla = VerificacionRegla::query()->updateOrCreate(
            ['nombre' => 'CAMe 2025', 'version' => '2025'],
            [
                'status'          => 'published',
                'vigencia_inicio' => '2025-01-01',
                'vigencia_fin'    => '2025-12-31',
                'frecuencia'      => 'Semestral',
                'estados'         => $estadosLegibles, // legibles; generamos normalizados abajo
                'notas'           => 'Calendario bimestral por terminación (5-6, 7-8, 3-4, 1-2, 9-0) repetido en S2.',
            ]
        );

        $anio = 2025;
        $bimestres = $this->bimestresCame();

        // Limpia periodos previos de esta regla+anio
        CalendarioVerificacion::where('regla_id', $regla->id)
            ->where('anio', $anio)
            ->delete();

        $rows = [];
        foreach ($estados as $estadoNorm) {
            foreach ($bimestres as $bm) {
                $mi = $bm['mes_inicio'];
                $mf = $bm['mes_fin'];
                $desde = Carbon::create($anio, $mi, 1)->startOfDay();
                $hasta = Carbon::create($anio, $mf, 1)->endOfMonth()->endOfDay();

                foreach ($bm['terminaciones'] as $digit) {
                    $rows[] = [
                        'regla_id'       => $regla->id,
                        'estado'         => $estadoNorm, // <— NORMALIZADO
                        'terminacion'    => $digit,
                        'mes_inicio'     => $mi,
                        'mes_fin'        => $mf,
                        'semestre'       => $bm['semestre'],
                        'frecuencia'     => $regla->frecuencia,
                        'anio'           => $anio,
                        'vigente_desde'  => $desde->toDateString(),
                        'vigente_hasta'  => $hasta->toDateString(),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            CalendarioVerificacion::insert($chunk);
        }
    }

    /* ===================== Jalisco 2025 ===================== */
    protected function seedJalisco2025(): void
    {
        $estadoNorm = $this->normalizeEstado('Jalisco');

        $regla = VerificacionRegla::query()->updateOrCreate(
            ['nombre' => 'Jalisco 2025', 'version' => '2025'],
            [
                'status'          => 'published',
                'vigencia_inicio' => '2025-01-01',
                'vigencia_fin'    => '2025-12-31',
                'frecuencia'      => 'Anual',
                'estados'         => ['Jalisco'], // legible
                'notas'           => 'Verificación Responsable: mapeo bimestral 1..0 (Ene-Feb=1, Feb-Mar=2, …, Nov-Dic=0).',
            ]
        );

        $anio = 2025;
        $bimestres = [
            ['mes_inicio'=>1,  'mes_fin'=>2,  'semestre'=>1, 'terminaciones'=>[1]],
            ['mes_inicio'=>2,  'mes_fin'=>3,  'semestre'=>1, 'terminaciones'=>[2]],
            ['mes_inicio'=>3,  'mes_fin'=>4,  'semestre'=>1, 'terminaciones'=>[3]],
            ['mes_inicio'=>4,  'mes_fin'=>5,  'semestre'=>1, 'terminaciones'=>[4]],
            ['mes_inicio'=>5,  'mes_fin'=>6,  'semestre'=>1, 'terminaciones'=>[5]],
            ['mes_inicio'=>7,  'mes_fin'=>8,  'semestre'=>2, 'terminaciones'=>[6]],
            ['mes_inicio'=>8,  'mes_fin'=>9,  'semestre'=>2, 'terminaciones'=>[7]],
            ['mes_inicio'=>9,  'mes_fin'=>10, 'semestre'=>2, 'terminaciones'=>[8]],
            ['mes_inicio'=>10, 'mes_fin'=>11, 'semestre'=>2, 'terminaciones'=>[9]],
            ['mes_inicio'=>11, 'mes_fin'=>12, 'semestre'=>2, 'terminaciones'=>[0]],
        ];

        // Limpia periodos previos de esta regla+anio
        CalendarioVerificacion::where('regla_id', $regla->id)
            ->where('anio', $anio)
            ->delete();

        $rows = [];
        foreach ($bimestres as $bm) {
            $mi = $bm['mes_inicio'];
            $mf = $bm['mes_fin'];
            $desde = Carbon::create($anio, $mi, 1)->startOfDay();
            $hasta = Carbon::create($anio, $mf, 1)->endOfMonth()->endOfDay();

            foreach ($bm['terminaciones'] as $digit) {
                $rows[] = [
                    'regla_id'       => $regla->id,
                    'estado'         => $estadoNorm, // <— NORMALIZADO
                    'terminacion'    => $digit,
                    'mes_inicio'     => $mi,
                    'mes_fin'        => $mf,
                    'semestre'       => $bm['semestre'],
                    'frecuencia'     => $regla->frecuencia,
                    'anio'           => $anio,
                    'vigente_desde'  => $desde->toDateString(),
                    'vigente_hasta'  => $hasta->toDateString(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            CalendarioVerificacion::insert($chunk);
        }
    }
}
