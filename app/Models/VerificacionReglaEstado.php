<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificacionReglaEstado extends Model
{
    use HasFactory;

    protected $table = 'verificacion_regla_estados';

    protected $fillable = [
        'regla_id',
        'anio',
        'estado',
    ];

    public function regla()
    {
        return $this->belongsTo(VerificacionRegla::class, 'regla_id');
    }

    /* Normalización automática del estado al guardar */
    public function setEstadoAttribute($value)
    {
        $norm = Str::of($value ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        if (in_array($norm, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            $norm = 'EDO MEX';
        }
        $this->attributes['estado'] = $norm;
    }
}
