<?php

namespace App\Observers;

use App\Models\CargaCombustible;
use App\Services\TelegramNotifier;
use Illuminate\Support\Carbon;

class CargaCombustibleObserver
{
    public function created(CargaCombustible $carga)
    {
        // Carga relaciones necesarias
        $carga->loadMissing(['vehiculo', 'operador']);

        // Fecha
        $fecha = $carga->fecha instanceof \DateTimeInterface
            ? $carga->fecha->format('d/m/Y')
            : Carbon::parse($carga->fecha)->format('d/m/Y');

        // Operador
        $operadorObj = $carga->operador;
        $operador = $operadorObj->nombre_completo
            ?? trim(implode(' ', array_filter([
                $operadorObj->nombres ?? null,
                $operadorObj->apellidos ?? null,
            ]))) ?: 'N/D';

        // *** Unidad (nombre de la unidad) ***
        $vehiculoObj = $carga->vehiculo;
        $unidad = $vehiculoObj->unidad ?? 'N/D'; // <- aquí forzamos el nombre de unidad

        // Litros y costo
        $litros = number_format((float) $carga->litros, 2);
        $total  = $carga->total ?? ((float) ($carga->precio ?? 0) * (float) ($carga->litros ?? 0));
        $costo  = '$' . number_format((float) $total, 2);

        // Mensaje
        $mensaje = "⛽ Nueva carga registrada\n"
                 . "📅 Fecha: <b>{$fecha}</b>\n"
                 . "👤 Operador: {$operador}\n"
                 . "🚚 Unidad: {$unidad}\n"
                 . "⛽ Litros: {$litros}\n"
                 . "💰 Costo: {$costo}";

        app(TelegramNotifier::class)->send($mensaje);
    }
}
