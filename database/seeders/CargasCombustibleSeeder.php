<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class CargasCombustibleSeeder extends Seeder
{
    /**
     * Genera 100 cargas de combustible con:
     * - tipo_combustible: Magna/Diesel/Premium
     * - precio realista por tipo
     * - litros y total consistentes
     * - kms coherentes por vehículo (incrementales en el tiempo)
     * - rendimiento = recorrido / litros
     */
    public function run(): void
    {
        // Vehículos y sus kilómetros actuales (para anclar la línea de tiempo de kms)
        $vehiculos = DB::table('vehiculos')
            ->select('id', 'kilometros', 'unidad')
            ->get()
            ->keyBy('id');

        if ($vehiculos->isEmpty()) {
            $this->command?->warn('No hay vehículos; se omite CargasCombustibleSeeder.');
            return;
        }

        // Operadores disponibles
        $operadores = DB::table('operadores')->pluck('id')->all();
        if (empty($operadores)) {
            $this->command?->warn('No hay operadores; se omite CargasCombustibleSeeder.');
            return;
        }

        $tipos = ['Magna', 'Diesel', 'Premium'];

        // Rango de fechas (enero 2025 a ayer)
        $ini = Carbon::create(2025, 1, 1);
        $fin = Carbon::yesterday();

        // 1) Generamos 100 "bosquejos" de cargas sin km todavía
        $drafts = [];
        $porVehiculo = []; // para luego encadenar kms por vehículo

        for ($i = 0; $i < 100; $i++) {
            $vehiculoId = Arr::random($vehiculos->keys()->all());
            $tipo = Arr::random($tipos);

            // Precio por tipo (MXN/L) — rangos aproximados 2025
            $precio = match ($tipo) {
                'Magna'   => round(mt_rand(2200, 2550) / 100, 2), // 22.00–25.50
                'Premium' => round(mt_rand(2500, 2800) / 100, 2), // 25.00–28.00
                'Diesel'  => round(mt_rand(2400, 2750) / 100, 2), // 24.00–27.50
            };

            // Litros (3 decimales)
            $litros = round(mt_rand(15000, 60000) / 1000, 3); // 15.000–60.000 L

            // Total = precio * litros (2 decimales)
            $total = round($litros * $precio, 2);

            // Fecha aleatoria
            $fecha = Carbon::createFromTimestamp(mt_rand($ini->timestamp, $fin->timestamp))->startOfDay();

            // Estimamos recorrido según tipo (km/l) y algo de variación
            $rendEstim = match ($tipo) {
                'Diesel'  => mt_rand(80, 120) / 10,  // 8.0–12.0
                'Magna'   => mt_rand(90, 140) / 10,  // 9.0–14.0
                'Premium' => mt_rand(100, 140) / 10, // 10.0–14.0
            };

            $recorrido = max(1, (int) round($litros * $rendEstim));

            $drafts[] = [
                'vehiculo_id'      => $vehiculoId,
                'operador_id'      => Arr::random($operadores),
                'tipo_combustible' => $tipo,
                'precio'           => $precio,
                'litros'           => $litros,
                'total'            => $total,
                'fecha'            => $fecha, // Carbon
                'recorrido'        => $recorrido,
                // se completan más adelante: km_inicial, km_final, rendimiento, mes, etc.
            ];

            $porVehiculo[$vehiculoId][] = count($drafts) - 1; // índices en $drafts
        }

        // 2) Para cada vehículo: ordenar por fecha y encadenar kms coherentes
        $rows = [];
        foreach ($porVehiculo as $vehiculoId => $idxs) {
            // Ordenamos los índices por fecha ascendente
            usort($idxs, function ($a, $b) use ($drafts) {
                return $drafts[$a]['fecha'] <=> $drafts[$b]['fecha'];
            });

            // Kilómetros del vehículo "hoy"
            $kmActualVehiculo = (int) ($vehiculos[$vehiculoId]->kilometros ?? 0);

            // Sumatoria de recorridos programados
            $sumaRecorridos = array_sum(array_map(fn ($i) => $drafts[$i]['recorrido'], $idxs));

            // Arrancamos atrás en el tiempo (para que los km de las cargas sean <= km actuales)
            $slack = mt_rand(100, 5000);
            $kmCorriente = max(0, $kmActualVehiculo - $sumaRecorridos - $slack);

            foreach ($idxs as $i) {
                $d = $drafts[$i];

                $kmInicial = $kmCorriente;
                $kmFinal = $kmInicial + $d['recorrido'];
                $kmCorriente = $kmFinal;

                $rendimiento = round($d['recorrido'] / max(0.001, $d['litros']), 2);

                $fecha = $d['fecha'];
                $mes = $fecha->format('Y-m');

                $rows[] = [
                    'fecha'            => $fecha->toDateString(),
                    'mes'              => $mes,
                    'precio'           => $d['precio'],
                    'tipo_combustible' => $d['tipo_combustible'],
                    'litros'           => $d['litros'],
                    'total'            => $d['total'],
                    'custodio'         => null,
                    'operador_id'      => $d['operador_id'],
                    'vehiculo_id'      => $d['vehiculo_id'],
                    'km_inicial'       => $kmInicial,
                    'km_final'         => $kmFinal,
                    'recorrido'        => $d['recorrido'],
                    'rendimiento'      => $rendimiento,
                    'diferencia'       => null,
                    'destino'          => null,
                    'observaciones'    => 'Seeder de prueba',
                    'created_at'       => $fecha->copy()->setTime(12, 0),
                    'updated_at'       => $fecha->copy()->setTime(12, 0),
                ];
            }
        }

        // 3) Insert masivo (en chunks por seguridad)
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('cargas_combustible')->insert($chunk);
        }

        $this->command?->info('Se generaron 100 cargas de combustible.');
    }
}
