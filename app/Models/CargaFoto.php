<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CargaFoto extends Model
{
    use HasFactory;

    protected $table = 'carga_fotos';

    // Tipos sugeridos (puedes agregar más)
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

    protected $appends = ['url'];

    public function carga()
    {
        return $this->belongsTo(CargaCombustible::class, 'carga_id');
    }

    // URL pública (requiere `php artisan storage:link`)
    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }
}
