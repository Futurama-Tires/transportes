<?php

namespace App\Console\Commands;

use App\Models\Vehiculo;
use App\Models\CalendarioVerificacion;
use App\Models\Verificacion;
use App\Models\User;
use App\Notifications\VerificacionDigest;
use App\Services\TelegramNotifier; // 👈 servicio de Telegram
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class VerificacionDigestCommand extends Command
{
    protected $signature = 'verificacion:digest {--dry-run} {--fecha=}';
    protected $description = 'Notificación por día con vehículos en pre-open (7,1), pre-close (14,7,1) y overdue (+1,+7) usando bordes de mes y período anterior.';

    public function handle(): int
    {
        $tz   = config('verificacion.timezone', 'America/Mexico_City');
        $hoy  = $this->option('fecha')
            ? Carbon::parse($this->option('fecha'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfDay();

        $preOpenDays   = collect(config('verificacion.pre_open_days', [7,1]))->unique()->sort()->values()->all();
        $preCloseDays  = collect(config('verificacion.pre_close_days', [14,7,1]))->unique()->sort()->values()->all();
        $overdueDays   = collect(config('verificacion.overdue_days', [1,7]))->unique()->sort()->values()->all();

        $bloques = [
            'pre_open'  => [],
            'pre_close' => [],
            'overdue'   => [],
        ];

        $anio = (int) $hoy->year;
        $mes  = (int) $hoy->month;

        // Cache por (ESTADO#TERMINACION) => [periodoActual, periodoSiguiente, periodoAnterior]
        $cachePeriodos = [];

        Vehiculo::query()
            ->select(['id','unidad','placa','estado'])
            ->orderBy('id')
            ->chunkById(500, function ($vehiculos) use ($hoy, $tz, $anio, $mes, $preOpenDays, $preCloseDays, $overdueDays, &$bloques, &$cachePeriodos) {

                foreach ($vehiculos as $v) {
                    $estado = $this->norm($v->estado);
                    $placa  = $this->norm($v->placa);
                    if (!$estado || !$placa) continue;

                    $term = CalendarioVerificacion::terminacionDePlaca($placa);
                    if ($term === null) continue;

                    $key = $estado.'#'.$term;

                    if (!array_key_exists($key, $cachePeriodos)) {
                        // Periodo ACTUAL por mes (independiente de vigente_desde/hasta)
                        $periodoActual = CalendarioVerificacion::query()
                            ->whereRaw('UPPER(estado) = ?', [$estado])
                            ->where('terminacion', $term)
                            ->where('anio', $anio)
                            ->where('mes_inicio', '<=', $mes)
                            ->where('mes_fin', '>=', $mes)
                            ->orderBy('mes_inicio')
                            ->first();

                        // Periodo SIGUIENTE: primer periodo con mes_inicio > mes actual
                        $periodoSiguiente = CalendarioVerificacion::query()
                            ->whereRaw('UPPER(estado) = ?', [$estado])
                            ->where('terminacion', $term)
                            ->where('anio', $anio)
                            ->where('mes_inicio', '>', $mes)
                            ->orderBy('mes_inicio')
                            ->first();

                        // Periodo ANTERIOR: último con mes_fin < mes actual
                        $periodoAnterior = CalendarioVerificacion::query()
                            ->whereRaw('UPPER(estado) = ?', [$estado])
                            ->where('terminacion', $term)
                            ->where('anio', $anio)
                            ->where('mes_fin', '<', $mes)
                            ->orderByDesc('mes_fin')
                            ->first();

                        $cachePeriodos[$key] = [$periodoActual, $periodoSiguiente, $periodoAnterior];
                    } else {
                        [$periodoActual, $periodoSiguiente, $periodoAnterior] = $cachePeriodos[$key];
                    }

                    $row = function ($cv, string $fase, ?int $dias) use ($v, $tz) {
                        $meses = [null,'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                        return [
                            'vehiculo_id' => $v->id,
                            'unidad'      => $v->unidad ?? ('Vehículo #'.$v->id),
                            'placa'       => $v->placa,
                            'estado'      => $cv->estado,
                            'anio'        => (int) $cv->anio,
                            'mes_inicio'  => (int) $cv->mes_inicio,
                            'mes_fin'     => (int) $cv->mes_fin,
                            'desde'       => $cv->inicioPeriodo($tz)?->toDateString(),
                            'hasta'       => $cv->finPeriodo($tz)?->toDateString(),
                            'ventana'     => ($meses[$cv->mes_inicio] ?? $cv->mes_inicio).'-'.($meses[$cv->mes_fin] ?? $cv->mes_fin),
                            'fase'        => $fase,
                            'dias'        => $dias,
                            'url'         => route(config('verificacion.dashboard_route', 'programa-verificacion.index')),
                        ];
                    };

                    // Helper: ¿ya cumplió el periodo?
                    $estaCumplida = function ($cv) use ($v): bool {
                        if (!$cv) return false;
                        return Verificacion::query()
                            ->where('vehiculo_id', $v->id)
                            ->where('anio', (int) $cv->anio)
                            ->where('mes_inicio', (int) $cv->mes_inicio)
                            ->where('mes_fin', (int) $cv->mes_fin)
                            ->whereIn('resultado', ['APROBADO','EXENTO','NO_APLICA'])
                            ->exists();
                    };

                    // ====== PERIODO ACTUAL: pre-close exacto ======
                    if ($periodoActual && !$estaCumplida($periodoActual)) {
                        $ini = $periodoActual->inicioPeriodo($tz);
                        $fin = $periodoActual->finPeriodo($tz);
                        if ($ini && $fin && $hoy->between($ini, $fin)) {
                            $diasCierre = $hoy->diffInDays($fin, false); // >=0
                            if (in_array($diasCierre, $preCloseDays, true)) {
                                $bloques['pre_close'][] = $row($periodoActual, 'pre_close', $diasCierre);
                            }
                        }
                    }

                    // ====== PERIODO ANTERIOR: overdue exacto (+1,+7) ======
                    if ($periodoAnterior && !$estaCumplida($periodoAnterior)) {
                        $finPrev = $periodoAnterior->finPeriodo($tz);
                        if ($finPrev && $hoy->gt($finPrev)) {
                            $diasVencPrev = $finPrev->diffInDays($hoy, false); // 1,2,3...
                            if (in_array($diasVencPrev, $overdueDays, true)) {
                                $bloques['overdue'][] = $row($periodoAnterior, 'overdue', $diasVencPrev);
                            }
                        }
                    }

                    // ====== PERIODO SIGUIENTE: pre-open exacto (7,1) ======
                    if ($periodoSiguiente && !$estaCumplida($periodoSiguiente)) {
                        $iniNext = $periodoSiguiente->inicioPeriodo($tz);
                        if ($iniNext && $hoy->lt($iniNext)) {
                            $diasApertura = $hoy->diffInDays($iniNext, false); // >0
                            if (in_array($diasApertura, $preOpenDays, true)) {
                                $bloques['pre_open'][] = $row($periodoSiguiente, 'pre_open', $diasApertura);
                            }
                        }
                    }
                }
            });

        // ¿Hay algo que avisar?
        $totalItems = collect($bloques)->flatten(1)->count();
        if ($totalItems === 0) {
            $this->info('Sin hitos hoy.');
            return self::SUCCESS;
        }

        // ===== Destinatarios: por permiso (si configuraste) o por roles =====
        $roles   = (array) config('verificacion.recipient_roles', ['administrador','capturista']);
        $permiso = config('verificacion.recipient_permission');

        $q = User::query();

        if ($permiso) {
            // Spatie: scope permission()
            $q->permission($permiso);
        } else {
            // Spatie: scope role()
            $q->role($roles);
        }

        $q->whereNotNull('email')->where('email', '!=', '');

        $count = 0;

        // DRY RUN (NO envía nada, solo tabla + conteo de destinatarios)
        if ($this->option('dry-run')) {
            $destPreview = (clone $q)->count();

            $this->table(
                ['Bloque','Vehículo','Placa','Estado','Ventana','Días'],
                collect($bloques)->flatMap(
                    fn($arr,$k)=>collect($arr)->map(fn($i)=>[
                        strtoupper($k), $i['unidad'], $i['placa'], $i['estado'], $i['ventana'], $i['dias'] ?? '',
                    ])
                )->all()
            );

            $this->info("Destinatarios potenciales (dry-run): {$destPreview}");
            return self::SUCCESS;
        }

        // =========== ENVÍO A TELEGRAM (si está habilitado) ===========
        $this->sendTelegramDigest($bloques, $hoy);

        // =========== NOTIFICACIÓN (DB / mail, según config) ===========
        $q->orderBy('id')->chunk(500, function ($chunk) use ($bloques, &$count) {
            Notification::send($chunk, new VerificacionDigest($bloques));
            $count += $chunk->count();
        });

        if ($count === 0) {
            $this->warn('No hay destinatarios con los roles/permiso configurados.');
        } else {
            $this->info("Digest enviado a {$count} usuario(s).");
        }

        return self::SUCCESS;
    }

    private function norm(?string $s): ?string
    {
        $s = is_string($s) ? trim($s) : null;
        return $s ? mb_strtoupper($s) : null;
    }

    /**
     * Envía un mensaje a Telegram (si está habilitado).
     */
    private function sendTelegramDigest(array $bloques, \Carbon\Carbon $fecha): void
    {
        $cfg = config('verificacion.telegram', []);
        if (!data_get($cfg, 'enabled', false)) {
            return; // feature flag apagado
        }

        /** @var TelegramNotifier $tg */
        $tg = app(TelegramNotifier::class);

        $max  = (int) data_get($cfg, 'max_items', 10);
        $date = $fecha->toDateString();

        $lines = [];
        $count = fn(string $k) => count($bloques[$k] ?? []);

        $lines[] = "📋 <b>Verificación — Resumen ($date)</b>";
        $lines[] = "• Próx. abrir: <b>{$count('pre_open')}</b>";
        $lines[] = "• Por cerrar: <b>{$count('pre_close')}</b>";
        $lines[] = "• Vencidas (+1/+7): <b>{$count('overdue')}</b>";
        $lines[] = "";

        $renderBlock = function (string $title, string $key) use (&$bloques, $max) {
            $out = [];
            $items = $bloques[$key] ?? [];
            if (!$items) return $out;
            $out[] = "<u>{$title}</u>";
            foreach (array_slice($items, 0, $max) as $it) {
                $u = e($it['unidad'] ?? ('Vehículo #'.$it['vehiculo_id']));
                $p = e($it['placa'] ?? 'S/placa');
                $v = e($it['ventana'] ?? '');
                $d = isset($it['dias']) && $it['dias'] !== null ? " ({$it['dias']} días)" : '';
                $out[] = "• <b>{$u}</b> ({$p}) — {$v}{$d}";
            }
            if (count($items) > $max) {
                $rest = count($items) - $max;
                $out[] = "… y <b>{$rest}</b> más.";
            }
            $out[] = ""; // separador
            return $out;
        };

        $lines = array_merge(
            $lines,
            $renderBlock('Próximas a abrir', 'pre_open'),
            $renderBlock('Por cerrar', 'pre_close'),
            $renderBlock('Vencidas (+1/+7)', 'overdue')
        );

        $url = route(config('verificacion.dashboard_route', 'programa-verificacion.index'));
        $lines[] = "➡️ <a href=\"{$url}\">Abrir tablero</a>";

        $msg = implode("\n", $lines);

        try {
            $ok = $tg->send($msg); // tu servicio acepta HTML
            if ($ok) {
                $this->info('Telegram enviado ✅');
            } else {
                $this->warn('No se pudo enviar a Telegram (ver logs) ❌');
            }
        } catch (\Throwable $e) {
            $this->warn('Telegram error: '.$e->getMessage());
        }
    }
}
