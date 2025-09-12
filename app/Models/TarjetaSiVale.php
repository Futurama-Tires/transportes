<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo;
use App\Models\CargaCombustible;

/**
 * Class TarjetaSiVale
 *
 * Representa una tarjeta SiVale asignada a uno o varios vehículos.
 * Desde aquí podemos llegar a todas las cargas realizadas por esos vehículos.
 */
class TarjetaSiVale extends Model
{
    use HasFactory;

    /**
     * OJO: Ajusta este nombre si tu tabla real es snake_case.
     * Recomendado: 'tarjetas_sivale'
     */
    protected $table = 'tarjetasSiVale';

    protected $fillable = [
        'numero_tarjeta',
        'nip',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    /**
     * Relación: una tarjeta puede estar asociada a varios vehículos (o a uno).
     * FK en vehiculos: vehiculos.tarjeta_si_vale_id
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tarjeta_si_vale_id');
    }

    /**
     * Relación indirecta: Tarjeta → Vehículo(s) → Cargas de Combustible.
     * Esto permite $tarjeta->cargas para listar TODAS las cargas ligadas a la tarjeta.
     *
     * Claves:
     * - firstKey (en Vehiculo):      'tarjeta_si_vale_id' → referencia a TarjetaSiVale.id
     * - secondKey (en CargaComb.):   'vehiculo_id'        → referencia a Vehiculo.id
     * - localKey (en TarjetaSiVale): 'id'
     * - secondLocalKey (en Vehiculo):'id'
     */
    public function cargas()
    {
        return $this->hasManyThrough(
            CargaCombustible::class, // Modelo final
            Vehiculo::class,         // Modelo intermedio
            'tarjeta_si_vale_id',    // FK en Vehiculo que apunta a TarjetaSiVale
            'vehiculo_id',           // FK en CargaCombustible que apunta a Vehiculo
            'id',                    // PK local en TarjetaSiVale
            'id'                     // PK local en Vehiculo
        );
    }

    /**
     * (Opcional) Atajo si manejas 1:1 a nivel de negocio:
     * Devuelve el primer vehículo asociado (si solo hubiera uno).
     */
    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'tarjeta_si_vale_id');
    }
}
