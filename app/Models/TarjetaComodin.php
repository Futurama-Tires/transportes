<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TarjetaComodin
 *
 * @property int $id
 * @property string $numero_tarjeta
 * @property string|null $nip
 * @property \Illuminate\Support\Carbon|null $fecha_vencimiento
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string|null $last4
 * @property-read string $numero_enmascarado
 */
class TarjetaComodin extends Model
{
    use HasFactory;

    protected $table = 'tarjetas_comodin';

    protected $fillable = [
        'numero_tarjeta',
        'nip',
        'fecha_vencimiento',
        'descripcion',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date:Y-m-d',
    ];

    protected $appends = ['last4', 'numero_enmascarado'];

    /** Relación: una tarjeta tiene muchos gastos */
    public function gastos()
    {
        return $this->hasMany(ComodinGasto::class, 'tarjeta_comodin_id');
    }

    /** Últimos 4 dígitos (solo números) */
    public function getLast4Attribute(): ?string
    {
        $num = preg_replace('/\D+/', '', (string) $this->numero_tarjeta);
        return $num ? substr($num, -4) : null;
    }

    /** Número enmascarado con •••• */
    public function getNumeroEnmascaradoAttribute(): string
    {
        $num = preg_replace('/\D+/', '', (string) $this->numero_tarjeta);
        if (!$num) return '—';
        $len = strlen($num);
        return str_repeat('•', max(0, $len - 4)) . substr($num, -4);
    }
}
