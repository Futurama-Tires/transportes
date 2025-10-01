<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificacionReglaEstado extends Model
{
    use HasFactory;

    protected $table = 'verificacion_regla_estados';

    protected $fillable = [
        'regla_id',
        'anio',
        'estado',
    ];

    protected $casts = [
        'anio' => 'integer',
    ];

    public function regla()
    {
        return $this->belongsTo(VerificacionRegla::class, 'regla_id');
    }

    /* Normalización automática del estado al guardar */
    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado'] = VerificacionRegla::normalizeEstado($value);
    }

    /* Scopes útiles */
    public function scopePorAnio($q, ?int $anio)
    {
        return $anio ? $q->where('anio', $anio) : $q;
    }
}
