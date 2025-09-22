<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property string      $nombre
 * @property string|null $version
 * @property string      $status         draft|published|archived
 * @property Carbon|null $vigencia_inicio
 * @property Carbon|null $vigencia_fin
 * @property string      $frecuencia     Semestral|Anual
 * @property array|null  $estados        Lista de estados (strings) aplicada a esta regla
 * @property string|null $notas
 */
class VerificacionRegla extends Model
{
    protected $table = 'verificacion_reglas';

    protected $fillable = [
        'nombre','version','status',
        'vigencia_inicio','vigencia_fin',
        'frecuencia','estados','notas',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin'    => 'date',
        'estados'         => 'array',     // JSON <-> array
    ];

    // Relaciones
    public function periodos()
    {
        // Reutilizamos tu tabla existente como almacenamiento de periodos
        return $this->hasMany(CalendarioVerificacion::class, 'regla_id');
    }

    // Scopes útiles
    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeVigenteHoy($q, ?Carbon $hoy = null)
    {
        $hoy = $hoy ?: now()->startOfDay();
        return $q->where(function($w) use ($hoy) {
            $w->whereNull('vigencia_inicio')->orWhere('vigencia_inicio', '<=', $hoy);
        })->where(function($w) use ($hoy) {
            $w->whereNull('vigencia_fin')->orWhere('vigencia_fin', '>=', $hoy);
        });
    }
}
