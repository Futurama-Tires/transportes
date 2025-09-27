<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabla: verificaciones
 * Campos usados en reportes:
 *  id, vehiculo_id, estado, comentarios,
 *  fecha_programada_inicio, fecha_programada_fin,
 *  anio, resultado, fecha_verificacion
 *
 * Extras soportados: holograma, fecha_real, mes_inicio, mes_fin, calendario_id
 */
class Verificacion extends Model
{
    protected $table = 'verificaciones';
    public $timestamps = true;

    protected $fillable = [
        'vehiculo_id',
        'estado',
        'comentarios',
        'resultado',                // PENDIENTE|APROBADO|RECHAZADO|EXENTO|NO_APLICA
        'holograma',                // 00|0|1|2 (nullable)
        'fecha_programada_inicio',
        'fecha_programada_fin',
        'fecha_real',
        'fecha_verificacion',       // la que usamos para "marcar completada"
        'mes_inicio',
        'mes_fin',
        'anio',
        'calendario_id',
    ];

    protected $casts = [
        'fecha_programada_inicio' => 'date',
        'fecha_programada_fin'    => 'date',
        'fecha_real'              => 'date',
        'fecha_verificacion'      => 'date',
        'anio'                    => 'integer',
        'mes_inicio'              => 'integer',
        'mes_fin'                 => 'integer',
    ];

    /* ===================== Relaciones ===================== */

    public function vehiculo()
    {
        return $this->belongsTo(\App\Models\Vehiculo::class, 'vehiculo_id');
    }

    public function calendario()
    {
        return $this->belongsTo(\App\Models\CalendarioVerificacion::class, 'calendario_id');
    }

    /* ===================== Constantes / Helpers ===================== */

    /** Resultados que consideramos "cumplida" para el reporte. */
    public const RESULTADOS_APROBADOS = ['APROBADO', 'EXENTO', 'NO_APLICA'];

    /** ¿La verificación está aprobada/aceptable y con fecha asentada? */
    public function esAprobada(): bool
    {
        return in_array((string) $this->resultado, self::RESULTADOS_APROBADOS, true)
            && !empty($this->fecha_verificacion);
    }

    /**
     * ¿Cumple con una ventana de calendario específica?
     * - Prioriza match por calendario_id.
     * - Si no hay, valida que fecha_verificacion caiga dentro de la ventana.
     */
    public function coincideConCalendario(?CalendarioVerificacion $cal): bool
    {
        if (!$cal) return false;
        if ($this->calendario_id && (int)$this->calendario_id === (int)$cal->id) {
            return $this->esAprobada();
        }
        if (!$this->esAprobada()) return false;
        return $cal->contieneFecha($this->fecha_verificacion);
    }

    /* ===================== Scopes ===================== */

    public function scopeAnio($q, ?int $anio)
    {
        return $anio ? $q->where('anio', $anio) : $q;
    }

    public function scopeAprobada($q)
    {
        return $q->whereIn('resultado', self::RESULTADOS_APROBADOS)
                 ->whereNotNull('fecha_verificacion');
    }

    public function scopeDeVehiculo($q, ?int $vehiculoId)
    {
        return $vehiculoId ? $q->where('vehiculo_id', $vehiculoId) : $q;
    }

    public function scopeEstado($q, ?string $estado)
    {
        return $estado ? $q->whereRaw('UPPER(estado) = ?', [mb_strtoupper($estado)]) : $q;
    }
}
