<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Cada fila representa una "ventana" de verificación para un estado y una terminación.
 *
 * Tabla: calendario_verificacion
 * Campos: id, regla_id, estado, terminacion, mes_inicio, mes_fin, semestre, frecuencia,
 *         anio, vigente_desde, vigente_hasta, timestamps
 */
class CalendarioVerificacion extends Model
{
    use HasFactory;

    protected $table = 'calendario_verificacion';

    protected $fillable = [
        'regla_id',
        'estado',
        'terminacion',
        'mes_inicio',
        'mes_fin',
        'semestre',
        'frecuencia',
        'anio',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $casts = [
        'semestre'      => 'integer',
        'terminacion'   => 'integer',
        'mes_inicio'    => 'integer',
        'mes_fin'       => 'integer',
        'anio'          => 'integer',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    /* ===================== Relaciones ===================== */
    public function regla()
    {
        return $this->belongsTo(\App\Models\VerificacionRegla::class, 'regla_id');
    }

    public function verificaciones()
    {
        return $this->hasMany(\App\Models\Verificacion::class, 'calendario_id');
    }

    /* ===================== Normalización ===================== */

    /** Normaliza nombre de estado de forma consistente con el resto del sistema. */
    public static function normalizeEstado(?string $s): string
    {
        $norm = Str::of($s ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        if (in_array($norm, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $norm;
    }

    /** Aplica normalización al asignar estado. */
    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado'] = static::normalizeEstado($value);
    }

    /* ===================== Accessors/Helpers ===================== */

    /** Etiqueta tipo "May-Jun". */
    public function getEtiquetaBimestreAttribute(): string
    {
        $meses = [null,'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $ini = $meses[$this->mes_inicio] ?? $this->mes_inicio;
        $fin = $meses[$this->mes_fin]    ?? $this->mes_fin;
        return "{$ini}-{$fin}";
    }

    /** "1er semestre" | "2º semestre" | null */
    public function getEtiquetaSemestreAttribute(): ?string
    {
        return $this->semestre ? ($this->semestre === 1 ? '1er semestre' : '2º semestre') : null;
    }

    /** Alias legible para mostrar la ventana. */
    public function getVentanaAttribute(): string
    {
        return $this->etiqueta_bimestre;
    }

    /** ¿La fecha dada cae dentro de la ventana vigente? */
    public function contieneFecha(null|string|\DateTimeInterface $fecha): bool
    {
        if (!$this->vigente_desde || !$this->vigente_hasta) return false;
        $f = $fecha ? Carbon::parse($fecha) : Carbon::now();
        return $f->between($this->vigente_desde, $this->vigente_hasta);
    }

    /** ¿La ventana ya venció respecto a la fecha dada (o hoy)? */
    public function estaVencida(null|string|\DateTimeInterface $ref = null): bool
    {
        if (!$this->vigente_hasta) return false;
        $f = $ref ? Carbon::parse($ref) : Carbon::now();
        return $f->gt($this->vigente_hasta);
    }

    /**
     * Último dígito de una placa.
     * Corrige el caso de placas que terminan en letra: busca el ÚLTIMO dígito en cualquier posición.
     */
    public static function terminacionDePlaca(?string $placa): ?int
    {
        if (!$placa) return null;
        // Normaliza: quita espacios y guiones, y mayúsculas para uniformidad
        $p = preg_replace('/[\s\-]/', '', mb_strtoupper($placa));
        // Captura el ÚLTIMO dígito que exista en la cadena (compatible con Unicode)
        if (preg_match('/.*?(\d)(?!.*\d)/u', $p, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * Bordes NORMALIZADOS del periodo (independientes de vigente_desde/hasta).
     * - Inicio: 1° de mes_inicio
     * - Fin: último día de mes_fin
     */
    public function inicioPeriodo(?string $tz = null): ?Carbon
    {
        $tz = $tz ?: config('verificacion.timezone');
        if (!$this->anio || !$this->mes_inicio) {
            return $this->vigente_desde ? Carbon::parse($this->vigente_desde, $tz)->startOfDay() : null;
        }
        return Carbon::create($this->anio, $this->mes_inicio, 1, 0, 0, 0, $tz)->startOfDay();
    }

    public function finPeriodo(?string $tz = null): ?Carbon
    {
        $tz = $tz ?: config('verificacion.timezone');
        if (!$this->anio || !$this->mes_fin) {
            return $this->vigente_hasta ? Carbon::parse($this->vigente_hasta, $tz)->startOfDay() : null;
        }
        return Carbon::create($this->anio, $this->mes_fin, 1, 0, 0, 0, $tz)
            ->endOfMonth()
            ->startOfDay();
    }

    /* ===================== Scopes ===================== */

    public function scopeAnio($q, ?int $anio)
    {
        return $anio ? $q->where('anio', $anio) : $q;
    }

    /** Nuevo: filtra por estado normalizado. */
    public function scopeDeEstado(Builder $q, string $estado): Builder
    {
        return $q->where('estado', static::normalizeEstado($estado));
    }

    /** Nuevo: filtra por terminación (0..9). */
    public function scopeDeTerminacion(Builder $q, int $terminacion): Builder
    {
        return $q->where('terminacion', $terminacion);
    }

    /**
     * Nuevo: filtra periodos cuya ventana [vigente_desde, vigente_hasta]
     * contiene la fecha dada.
     */
    public function scopeVigenteEn(Builder $q, $fecha): Builder
    {
        $f = $fecha instanceof Carbon ? $fecha->toDateString() : (string) $fecha;
        return $q->whereDate('vigente_desde', '<=', $f)
                 ->whereDate('vigente_hasta', '>=', $f);
    }

    /** Nuevo: atajo por mes (1..12) y año usando mes_inicio/mes_fin. */
    public function scopeDelMes(Builder $q, int $mes, int $anio): Builder
    {
        return $q->where('anio', $anio)
                 ->where('mes_inicio', '<=', $mes)
                 ->where('mes_fin', '>=', $mes);
    }

    /**
     * (Ajustado) Filtra opcionalmente por estado y/o terminación.
     * Usa normalización en vez de comparaciones UPPER().
     */
    public function scopeEstadoTerminacion($q, ?string $estado, ?int $terminacion)
    {
        if ($estado !== null && $estado !== '') {
            $q->where('estado', static::normalizeEstado($estado));
        }
        if ($terminacion !== null) {
            $q->where('terminacion', (int) $terminacion);
        }
        return $q;
    }
}
