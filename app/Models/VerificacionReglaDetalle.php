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
        'frecuencia',  // "Semestral" | "Anual"
        'terminacion',
        'semestre',    // 0 (anual), 1, 2 (semestral)
        'mes_inicio',
        'mes_fin',
    ];

    protected $casts = [
        'terminacion' => 'integer',
        'semestre'    => 'integer',
        'mes_inicio'  => 'integer',
        'mes_fin'     => 'integer',
    ];

    public function regla()
    {
        return $this->belongsTo(VerificacionRegla::class, 'regla_id');
    }
}
