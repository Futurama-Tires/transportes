<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verificacion extends Model
{
    use HasFactory;

    protected $table = 'verificaciones';

    protected $fillable = [
        'vehiculo_id',
        'estado',
        'comentarios',
        'fecha_verificacion',
    ];

    /**
     * Relación con el vehículo.
     * Una verificación pertenece a un vehículo.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
