<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fecha_verificacion',       // <— la que usamos para "marcar completada"
        'mes_inicio',
        'mes_fin',
        'anio',
        'calendario_id',            // nullable
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

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
