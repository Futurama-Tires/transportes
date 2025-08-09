<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'ubicacion',
        'propietario',
        'unidad',
        'marca',
        'anio',
        'serie',
        'motor',
        'placa',
        'estado',
        'tarjeta_siVale',
        'nip',
        'fec_vencimiento',
        'vencimiento_t_circulacion',
        'cambio_placas',
        'poliza_hdi',
        'rend',
    ];

    protected $casts = [
        'rend' => 'float',
    ];

    // Relaciones (opcionales; úsalas cuando tengas esos modelos)
    public function tanques()
    {
        return $this->hasMany('App\Models\Tanque');
    }

    public function verificaciones()
    {
        return $this->hasMany('App\Models\Verificacion');
    }

    public function cargasCombustible()
    {
        return $this->hasMany('App\Models\CargaCombustible');
    }
}
