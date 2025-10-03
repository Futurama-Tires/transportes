<?php
// app/Models/PrecioCombustible.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrecioCombustible extends Model
{
    use HasFactory;

    // Nombre explícito por si cambias el pluralizer
    protected $table = 'precios_combustible';

    protected $fillable = [
        'combustible',       // 'Magna', 'Premium', 'Diesel' (o los que uses)
        'precio_por_litro',  // decimal(8,3)
    ];

    // Nota: 'decimal:3' devuelve string para preservar precisión
    protected $casts = [
        'precio_por_litro' => 'decimal:3',
    ];

    /**
     * Normaliza el campo 'combustible' al guardar (trim + Title Case).
     */
    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if ($m->combustible) {
                $m->combustible = Str::title(trim($m->combustible));
            }
        });
    }

    /**
     * Scope por tipo de combustible.
     */
    public function scopeCombustible($query, string $tipo)
    {
        return $query->where('combustible', Str::title(trim($tipo)));
    }

    /**
     * Helper rápido: obtiene el precio por litro para un combustible.
     * Retorna null si no existe (o $default si lo pasas).
     */
    public static function priceFor(string $combustible, ?float $default = null): ?float
    {
        $row = static::query()
            ->where('combustible', Str::title(trim($combustible)))
            ->first();

        // Cast a float para facilitar cálculos posteriores
        return $row ? (float) $row->precio_por_litro : $default;
    }

    /**
     * (Opcional) Formato amigable para UI.
     */
    public function getPrecioPorLitroMxAttribute(): string
    {
        // Asegura string con 3 decimales (ej. "22.990")
        $val = number_format((float) $this->precio_por_litro, 3, '.', '');
        return '$' . $val . ' MXN/L';
    }
}
