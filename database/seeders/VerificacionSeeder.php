<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CalendarioVerificacion;


class VerificacionSeeder extends Seeder
{
    public function run(): void
    {
        $reglas = [
            // terminación => [mes_inicio, mes_fin]
            [ 'terminacion' => 5, 'mes_inicio' => 1, 'mes_fin' => 2 ],
            [ 'terminacion' => 6, 'mes_inicio' => 1, 'mes_fin' => 2 ],
            [ 'terminacion' => 7, 'mes_inicio' => 2, 'mes_fin' => 3 ],
            [ 'terminacion' => 8, 'mes_inicio' => 2, 'mes_fin' => 3 ],
            [ 'terminacion' => 3, 'mes_inicio' => 3, 'mes_fin' => 4 ],
            [ 'terminacion' => 4, 'mes_inicio' => 3, 'mes_fin' => 4 ],
            [ 'terminacion' => 1, 'mes_inicio' => 4, 'mes_fin' => 5 ],
            [ 'terminacion' => 2, 'mes_inicio' => 4, 'mes_fin' => 5 ],
            [ 'terminacion' => 9, 'mes_inicio' => 5, 'mes_fin' => 6 ],
            [ 'terminacion' => 0, 'mes_inicio' => 5, 'mes_fin' => 6 ],
        ];

        $estadosMegalopolis = ['CDMX','EDO MEX','HIDALGO','MORELOS','PUEBLA','TLAXCALA','QUERETARO'];

        foreach ($reglas as $r) {
            foreach ($estadosMegalopolis as $estado) {
                CalendarioVerificacion::create([
                    'estado'      => $estado,
                    'terminacion' => $r['terminacion'],
                    'mes_inicio'  => $r['mes_inicio'],
                    'mes_fin'     => $r['mes_fin'],
                    'semestre'    => null,
                    'frecuencia'  => 'Semestral',
                    'anio'        => null, // o year('now') si quieres fijar
                    'vigente_desde' => now()->startOfYear(),
                    'vigente_hasta' => now()->endOfYear(),
                ]);
            }
        }
    }
}
