<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tanque extends Model
{
    use HasFactory;

    protected $table = 'tanques';

    protected $fillable = [
        'vehiculo_id',
        'cantidad_tanques',         // opcional: 1, 2, etc. por vehículo
        'capacidad_litros',      // float
        'rendimiento_estimado',  // float (km/L)
        'km_recorre',            // float (capacidad * rendimiento)
        'costo_tanque_lleno',    // float
        'tipo_combustible',      // string: Magna | Diesel | Premium
    ];

    protected $casts = [
        'capacidad_litros'     => 'float',
        'rendimiento_estimado' => 'float',
        'km_recorre'           => 'float',
        'costo_tanque_lleno'   => 'float',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(\App\Models\Vehiculo::class);
    }
}
