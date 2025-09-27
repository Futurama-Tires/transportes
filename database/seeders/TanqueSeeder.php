<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Vehiculo;
use App\Models\Tanque;
use App\Models\CargaCombustible;

class TanqueSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Vehiculo::query()->orderBy('id')->chunk(200, function ($vehiculos) {
                foreach ($vehiculos as $vehiculo) {
                    // Idempotente: si ya existe, no duplica
                    if (Tanque::where('vehiculo_id', $vehiculo->id)->exists()) {
                        $this->command?->warn("Vehículo {$vehiculo->id}: ya tiene tanque, se omite.");
                        continue;
                    }

                    $cargas = CargaCombustible::where('vehiculo_id', $vehiculo->id);

                    // Tipo de combustible más usado
                    $tipo = (clone $cargas)
                        ->select('tipo_combustible', DB::raw('COUNT(*) as total'))
                        ->groupBy('tipo_combustible')
                        ->orderByDesc('total')
                        ->value('tipo_combustible') ?? 'Magna';

                    // Estimaciones desde el histórico
                    $avgRend   = (clone $cargas)->whereNotNull('rendimiento')->avg('rendimiento'); // km/L
                    $maxLitros = (clone $cargas)->max('litros');                                   // L
                    $avgPrecio = (clone $cargas)->avg('precio');                                   // $/L

                    $capacidad   = $maxLitros ? round($maxLitros, 2) : 50.00; // fallback 50 L
                    $rendimiento = $avgRend ? round($avgRend, 2) : null;
                    $kmRecorre   = $rendimiento ? round($capacidad * $rendimiento, 2) : null;
                    $costoLleno  = $avgPrecio ? round($capacidad * $avgPrecio, 2) : null;

                    Tanque::create([
                        'vehiculo_id'          => $vehiculo->id,
                        'cantidad_tanques'     => 1,           // 1 tanque por vehículo (modelo 1–1)
                        'capacidad_litros'     => $capacidad,
                        'rendimiento_estimado' => $rendimiento,
                        'km_recorre'           => $kmRecorre,
                        'costo_tanque_lleno'   => $costoLleno,
                        'tipo_combustible'     => $tipo,
                    ]);

                    $this->command?->info("Vehículo {$vehiculo->id}: tanque creado.");
                }
            });
        });
    }
}
