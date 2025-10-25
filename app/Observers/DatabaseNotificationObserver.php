<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use App\Services\TelegramNotifier;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        $data = (array) ($notification->data ?? []);
        $tipo = $data['tipo'] ?? null;

        // Solo estas dos
        if (!in_array($tipo, ['verificacion_apertura_7d', 'verificacion_cierre_7d'], true)) {
            return;
        }

        $titulo   = $data['titulo']   ?? 'Notificación';
        $url      = $data['url']      ?? null;
        $vehiculos= collect($data['vehiculos'] ?? []);

        if ($vehiculos->isEmpty()) {
            return;
        }

        // Fecha objetivo: si no viene en el payload, dedúcela de la ventana
        $fechaObjetivo = $data['fecha_objetivo']
            ?? ($tipo === 'verificacion_apertura_7d'
                ? ($vehiculos->first()['desde'] ?? null)
                : ($vehiculos->first()['hasta'] ?? null));

        // Encabezado
        $encabezado = $tipo === 'verificacion_apertura_7d'
            ? "📢 <b>VERIFICACIÓN · Apertura en 7 días</b>"
            : "⏰ <b>VERIFICACIÓN · Cierre en 7 días</b>";

        // Lista compacta
        $lista = $vehiculos->values()->map(function ($v, $idx) {
            $unidad = $v['unidad'] ?? 'N/D';
            $placa  = $v['placa']  ?? 'N/D';
            $edo    = $v['estado'] ?? 'N/D';
            return ($idx + 1) . ". {$unidad} ({$placa}) - {$edo}";
        })->implode("\n");

        $cuerpo = "📅 Fecha objetivo: <b>{$fechaObjetivo}</b>\n"
                . "🚚 Vehículos: <b>{$vehiculos->count()}</b>\n\n"
                . $lista;

        $footer = $url ? "\n\n🔗 <a href=\"{$url}\">Ver programa</a>" : '';

        $mensaje = "{$encabezado}\n\n{$cuerpo}{$footer}";

        // Enviar por Telegram (tu servicio ya maneja parse_mode HTML)
        app(TelegramNotifier::class)->send($mensaje);
    }
}
