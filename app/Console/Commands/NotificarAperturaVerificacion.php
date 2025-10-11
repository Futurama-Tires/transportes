<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AperturaVerificacion;
use App\Models\User;

class NotificarAperturaVerificacion extends Command
{
    protected $signature = 'verificacion:notificar-apertura {--dias=7} {--role=administrador}';
    protected $description = 'Notifica vehículos cuya ventana de verificación abre en N días.';

    public function handle(): int
    {
        $tz     = 'America/Mexico_City';
        $hoy    = CarbonImmutable::now($tz)->startOfDay();
        $target = $hoy->addDays((int)$this->option('dias'))->toDateString();

        // Consulta:
        //  - Une vehiculos con calendario_verificacion por estado y terminación de placa
        //  - Filtra reglas cuya ventana "vigente_desde" == target
        //  - Excluye los vehículos que ya están APROBADOS dentro de esa ventana
        //  - Requiere que la placa tenga al menos un dígito (para poder obtener terminación)
        $rows = DB::table('vehiculos as v')
            ->join('calendario_verificacion as c', function ($j) use ($target) {
                $j->on('v.estado', '=', 'c.estado')
                  ->whereDate('c.vigente_desde', '=', $target);
            })
            ->leftJoin('verificaciones as ver', function ($j) {
                // Ya verificado (aprobado) dentro de la misma ventana: excluir
                $j->on('ver.vehiculo_id', '=', 'v.id')
                  ->whereIn('ver.resultado', ['APROBADO','APROBADA'])
                  ->whereColumn('ver.fecha_verificacion', '>=', 'c.vigente_desde')
                  ->whereColumn('ver.fecha_verificacion', '<=', 'c.vigente_hasta');
            })
            ->whereNull('ver.id')
            ->whereNotNull('v.placa')
            ->whereNotNull('v.estado')
            // último dígito de la placa = terminación del calendario
            ->whereRaw("v.placa REGEXP '[0-9]'")
            ->whereRaw("CAST(REGEXP_SUBSTR(REVERSE(v.placa), '[0-9]') AS UNSIGNED) = c.terminacion")
            ->select(
                'v.id as vehiculo_id', 'v.unidad', 'v.placa', 'v.estado',
                'c.vigente_desde', 'c.vigente_hasta', 'c.mes_inicio', 'c.mes_fin'
            )
            ->orderBy('v.estado')->orderBy('v.unidad')
            ->get();

        if ($rows->isEmpty()) {
            $this->info("No hay vehículos cuya ventana abra el {$target}.");
            return self::SUCCESS;
        }

        // Arma el payload compacto para la notificación
        $payload = $rows->map(fn($r) => [
            'vehiculo_id' => $r->vehiculo_id,
            'unidad'      => $r->unidad,
            'placa'       => $r->placa,
            'estado'      => $r->estado,
            'desde'       => (string) $r->vigente_desde,
            'hasta'       => (string) $r->vigente_hasta,
        ])->values()->all();

        // Destinatarios (por rol de Spatie). Puedes ajustar a tu gusto.
        $destinatarios = User::role($this->option('role'))->get();

        if ($destinatarios->isEmpty()) {
            $this->warn('No hay usuarios con el rol indicado para notificar.');
            return self::SUCCESS;
        }

        Notification::send($destinatarios, new AperturaVerificacion($payload, $target));

        $this->info("Notificada apertura: {$rows->count()} vehículo(s), fecha {$target}, a {$destinatarios->count()} usuario(s).");
        return self::SUCCESS;
    }
}
