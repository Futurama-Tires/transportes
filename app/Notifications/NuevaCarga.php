<?php

namespace App\Notifications;

use App\Models\CargaCombustible;
use Illuminate\Bus\Queueable;
// Si vas a encolar notificaciones, descomenta la siguiente línea y usa ShouldQueue
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NuevaCarga extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(public CargaCombustible $carga)
    {
        //
    }

    public function via(object $notifiable): array
    {
        // Solo guardamos en BD (para el navbar + polling)
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $c = $this->carga;

        return [
            'titulo'  => 'Nueva carga registrada',
            'mensaje' => sprintf(
                'Vehículo %s • %0.2f L • $%0.2f',
                optional($c->vehiculo)->unidad ?? ('ID '.$c->vehiculo_id),
                (float) $c->litros,
                (float) $c->total
            ),
            // Requisito: al hacer clic te manda al index de cargas
            'url'     => route('cargas.index'),

            // Extras útiles (por si luego filtras o vas a edit)
            'carga_id'     => $c->id,
            'vehiculo_id'  => $c->vehiculo_id,
            'operador_id'  => $c->operador_id,
            'fecha'        => $c->fecha,
            'tipo'         => $c->tipo_combustible,
            'litros'       => (float) $c->litros,
            'total'        => (float) $c->total,
        ];
    }

    // Opcional: para notificaciones serializadas genéricas
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
