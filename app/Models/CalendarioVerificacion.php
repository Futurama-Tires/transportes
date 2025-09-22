<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cada fila representa una "ventana" de verificación para un estado y una terminación.
 *
 * Campos esperados en la tabla:
 * - id (bigint)
 * - regla_id (bigint, nullable)  => FK a verificacion_reglas.id
 * - estado (string)              => nombre del estado (ej. "CDMX", "México")
 * - terminacion (tinyint)        => 0..9
 * - mes_inicio (tinyint)         => 1..12
 * - mes_fin (tinyint)            => 1..12
 * - semestre (tinyint|null)
 * - frecuencia (enum)            => 'Semestral'|'Anual'
 * - anio (smallint|null)
 * - vigente_desde (date|null)
 * - vigente_hasta (date|null)
 * - timestamps
 */
class CalendarioVerificacion extends Model
{
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

    // Relaciones
    public function regla()
    {
        return $this->belongsTo(VerificacionRegla::class, 'regla_id');
    }

    // Helpers para UI
    public function getEtiquetaBimestreAttribute(): string
    {
        $meses = [null,'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $ini = $meses[$this->mes_inicio] ?? $this->mes_inicio;
        $fin = $meses[$this->mes_fin]    ?? $this->mes_fin;
        return "{$ini}-{$fin}";
    }

    public function getEtiquetaSemestreAttribute(): ?string
    {
        return $this->semestre ? ($this->semestre === 1 ? '1er semestre' : '2º semestre') : null;
    }
}
