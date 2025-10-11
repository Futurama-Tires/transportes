<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CierreVerificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $vehiculos,      // [{vehiculo_id, unidad, placa, estado, desde, hasta}]
        public string $fechaObjetivo  // YYYY-MM-DD (cierre)
    ) {}

    public function via(object $notifiable): array
    {
        return ['database']; // luego puedes sumar mail/telegram si quieres
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo'          => 'verificacion_cierre_7d',
            'titulo'        => 'Vehículos con período de verificación por cerrar',
            'mensaje'       => "La ventana cierra el {$this->fechaObjetivo}",
            'fecha'         => now()->toDateString(),
            'fecha_objetivo'=> $this->fechaObjetivo,
            'vehiculos'     => $this->vehiculos,
            'url'           => url('/programa-verificacion'),
        ];
    }
}
