<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaFoto extends Model
{
    use HasFactory;

    protected $table = 'carga_fotos';

    public const TICKET   = 'ticket';
    public const VOUCHER  = 'voucher';
    public const ODOMETRO = 'odometro';
    public const EXTRA    = 'extra';

    protected $fillable = [
        'carga_id',
        'tipo',
        'path',
        'mime',
        'size',
        'original_name',
    ];

    // Seguimos exponiendo 'url' para que tu UI/APP tenga algo que abrir,
    // pero ahora será una RUTA PROTEGIDA (no una URL pública /storage/...).
    protected $appends = ['url'];

    public function carga()
    {
        return $this->belongsTo(CargaCombustible::class, 'carga_id');
    }

    /**
     * Devuelve la ruta protegida para ver la imagen.
     * Asegúrate de tener nombrada la ruta web como 'cargas.fotos.show'.
     * GET /cargas/fotos/{foto} (auth)
     */
    public function getUrlAttribute(): ?string
    {
        return route('cargas.fotos.show', ['foto' => $this->id]);
    }
}
