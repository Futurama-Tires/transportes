<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AperturaVerificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $vehiculos,
        public string $fechaObjetivo // YYYY-MM-DD
    ) {}

    public function via(object $notifiable): array
    {
        // Por ahora, solo guardamos en BD. (Luego si quieres, añadimos mail/telegram)
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo'      => 'verificacion_apertura_7d',
            'titulo'    => 'Vehículos próximos a verificar',
            'mensaje'   => "La ventana abre el {$this->fechaObjetivo}",
            'fecha'     => now()->toDateString(),
            'vehiculos' => $this->vehiculos, // [{vehiculo_id, unidad, placa, estado, desde, hasta}]
            'url'       => url('/programa-verificacion'),
        ];
    }
}
