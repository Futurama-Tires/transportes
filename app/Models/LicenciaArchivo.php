<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenciaArchivo extends Model
{
    use HasFactory;

    protected $table = 'licencia_archivos';

    protected $fillable = [
        'licencia_id',
        'nombre_original',
        'ruta',          // ruta relativa dentro del DISK privado
        'mime',
        'size',
        'orden',
    ];

    protected $casts = [
        'size'  => 'integer',
        'orden' => 'integer',
    ];

    public function licencia()
    {
        return $this->belongsTo(LicenciaConducir::class, 'licencia_id');
    }

    /* Helpers opcionales */
    public function getExtensionAttribute(): ?string
    {
        if (!$this->nombre_original) return null;
        return strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));
    }
}
