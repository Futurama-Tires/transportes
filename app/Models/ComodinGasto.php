<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ComodinGasto
 *
 * @property int $id
 * @property int $tarjeta_comodin_id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $concepto
 * @property string $monto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ComodinGasto extends Model
{
    use HasFactory;

    protected $table = 'comodin_gastos';

    protected $fillable = [
        'tarjeta_comodin_id',
        'fecha',
        'concepto',
        'monto',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'monto' => 'decimal:2',
    ];

    /** Relación: gasto pertenece a una tarjeta comodín */
    public function tarjeta()
    {
        return $this->belongsTo(TarjetaComodin::class, 'tarjeta_comodin_id');
    }

    /* --- Scopes útiles opcionales --- */

    /** Filtra por año (YYYY) */
    public function scopeDelAnio($q, int $year)
    {
        return $q->whereYear('fecha', $year);
    }

    /** Filtra entre dos fechas (inclusive) */
    public function scopeEntreFechas($q, $desde, $hasta)
    {
        return $q->whereDate('fecha', '>=', $desde)
                 ->whereDate('fecha', '<=', $hasta);
    }
}
