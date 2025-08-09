<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarjetaSiVale extends Model
{
    use HasFactory;

    protected $table = 'tarjetasSiVale';

    protected $fillable = [
        'numero_tarjeta',
        'nip',
        'fecha_vencimiento',
    ];

    // Relación con vehículos
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tarjeta_si_vale_id');
    }
}
