<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoFoto extends Model
{
    // Tabla: vehiculo_fotos (coincide con la migración del paso 1)
    protected $table = 'vehiculo_fotos';

    protected $fillable = [
        'vehiculo_id',
        'ruta',         // p. ej. "vehiculos/abc123_foto1.jpg"
        'descripcion',
        'orden',
    ];

    // Relación inversa
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /**
     * Accesor opcional para obtener una URL firmada/privada.
     * (La implementaremos en el Paso 4 con un controlador que sirva el archivo.)
     * Uso en Blade: $foto->private_url
     */
    public function getPrivateUrlAttribute(): string
    {
        // Definiremos esta ruta en el Paso 4
        return route('vehiculos.fotos.show', ['foto' => $this->id]);
    }
}
