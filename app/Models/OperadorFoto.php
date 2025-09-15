<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class OperadorFoto extends Model
{
    use HasFactory;

    protected $table = 'operador_fotos';

    protected $fillable = [
        'operador_id',
        'ruta',
        'descripcion',
        'orden',
    ];

    protected $casts = [
        'operador_id' => 'integer',
        'orden'       => 'integer',
    ];

    public function operador()
    {
        return $this->belongsTo(Operador::class);
    }

    /** URL interna protegida (sirve la imagen por controlador). */
    public function getPrivateUrlAttribute(): string
    {
        return route('operadores.fotos.show', ['foto' => $this->id]);
    }

    /**
     * Nota: este hook borra el archivo cuando la foto se elimina vía Eloquent.
     * Si la fila se borra por *cascade en BD* (sin pasar por Eloquent), este hook NO se ejecuta.
     */
    protected static function booted()
    {
        static::deleting(function (self $foto) {
            if ($foto->ruta) {
                Storage::disk('local')->delete($foto->ruta);
            }
        });
    }
}
