<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerificacionDigest extends Notification
{
    use Queueable;

    /**
     * @param array $bloques ['pre_open'=>[], 'pre_close'=>[], 'overdue'=>[]]
     * Cada item: [
     *   'vehiculo_id','unidad','placa','estado',
     *   'anio','mes_inicio','mes_fin','desde','hasta',
     *   'ventana','fase','dias','url'
     * ]
     */
    public function __construct(public array $bloques) {}

    public function via($notifiable): array
    {
        return config('verificacion.channels', ['database']);
    }

    public function toArray($notifiable): array
    {
        $conteos = collect($this->bloques)->map(fn($v) => count($v))->all();

        return [
            'titulo'   => 'Verificación vehicular — Resumen',
            'mensaje'  => sprintf(
                'Próx. abrir: %d · Por cerrar: %d · Vencidas (+1/+7): %d',
                $conteos['pre_open'] ?? 0,
                $conteos['pre_close'] ?? 0,
                $conteos['overdue'] ?? 0
            ),
            'url'      => route(config('verificacion.dashboard_route', 'programa-verificacion.index')),
            'bloques'  => $this->bloques,
            'conteos'  => $conteos,
            'fecha'    => now(config('verificacion.timezone'))->toDateString(),
            'tipo'     => 'verificacion_digest',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $m = (new MailMessage)
            ->subject('Resumen de Verificaciones (hoy)')
            ->line('Hitos del programa de verificación (pre-open, pre-close y overdue).');

        $etiquetas = [
            'pre_open'  => 'Próximas a abrir (7 y 1 día)',
            'pre_close' => 'Por cerrar (14, 7 y 1 día)',
            'overdue'   => 'Vencidas (día +1 y +7)',
        ];

        foreach ($etiquetas as $k => $titulo) {
            $list = $this->bloques[$k] ?? [];
            if ($list) {
                $m->line("**{$titulo}** (".count($list).")");
                foreach (array_slice($list, 0, 15) as $it) {
                    $m->line(sprintf(
                        "- %s (%s) · %s a %s %s",
                        $it['unidad'] ?? ('Vehículo #'.$it['vehiculo_id']),
                        $it['placa'] ?? 's/placa',
                        $it['desde'] ?? '?',
                        $it['hasta'] ?? '?',
                        isset($it['dias']) && $it['dias'] !== null ? "({$it['dias']} días)" : ''
                    ));
                }
                if (count($list) > 15) $m->line('… y más.');
            }
        }

        return $m->action('Abrir tablero', route(config('verificacion.dashboard_route', 'programa-verificacion.index')));
    }
}
