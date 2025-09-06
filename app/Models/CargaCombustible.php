<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CargaCombustible extends Model
{
    use HasFactory;

    protected $table = 'cargas_combustible';

    protected $fillable = [
        'ubicacion',
        'fecha',
        'mes',
        'precio',
        'tipo_combustible',
        'litros',
        'total',
        'custodio',
        'operador_id',
        'vehiculo_id',
        'km_inicial',
        'km_final',
        'recorrido',
        'rendimiento',
        'diferencia',
        'destino',
        'observaciones',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'precio'      => 'decimal:2',
        'litros'      => 'decimal:3',
        'total'       => 'decimal:2',
        'rendimiento' => 'decimal:2',
        'diferencia'  => 'decimal:2',
    ];

    // Opciones fijas para R: Ubicacion
    public const UBICACIONES = ['Cuernavaca', 'Ixtapaluca', 'Queretaro', 'Vallejo', 'Guadalajara'];

    public function operador()
    {
        return $this->belongsTo(Operador::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
