<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificacionReglaDetalle extends Model
{
    use HasFactory;

    protected $table = 'verificacion_regla_detalles';

    protected $fillable = [
        'regla_id',
        'frecuencia',
        'terminacion',
        'semestre',
        'mes_inicio',
        'mes_fin',
    ];

    public function regla()
    {
        return $this->belongsTo(VerificacionRegla::class, 'regla_id');
    }
}
