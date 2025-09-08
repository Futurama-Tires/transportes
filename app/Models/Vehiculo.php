<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'ubicacion',
        'propietario',
        'unidad',
        'marca',
        'anio',
        'serie',
        'motor',
        'placa',
        'estado',
        'tarjeta_si_vale_id',
        'nip',
        'fec_vencimiento',
        'vencimiento_t_circulacion',
        'cambio_placas',
        'poliza_hdi',
        'rend',
    ];

    protected $casts = [
        'rend' => 'float',
    ];

    // Relaciones
    public function tanques()
    {
        return $this->hasMany('App\Models\Tanque');
    }

    public function verificaciones()
    {
        return $this->hasMany('App\Models\Verificacion');
    }

    public function cargasCombustible()
    {
        return $this->hasMany('App\Models\CargaCombustible');
    }

    public function tarjetaSiVale()
    {
        return $this->belongsTo(TarjetaSiVale::class, 'tarjeta_si_vale_id');
    }

    // ========== SCOPES ==========
    /**
     * Filtro "todo en uno".
     * Acepta llaves: search, ubicacion (string|array), propietario, marca, estado,
     * anio | anio_min | anio_max, placa, serie, unidad, tarjeta ('con'|'sin'|id),
     * poliza ('con'|'sin'|texto), fec_vencimiento_desde/_hasta (Y-m-d),
     * vtc_desde/_hasta (Y-m-d), rend_min/_max (float), created_from/_to (Y-m-d)
     */
    public function scopeFilter($query, array $filters = [])
    {
        // Búsqueda global
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $like = '%' . str_replace('%', '\\%', $term) . '%';
                $q->where('id', $term)
                  ->orWhere('ubicacion', 'like', $like)
                  ->orWhere('propietario', 'like', $like)
                  ->orWhere('unidad', 'like', $like)
                  ->orWhere('marca', 'like', $like)
                  ->orWhere('anio', 'like', $like)
                  ->orWhere('serie', 'like', $like)
                  ->orWhere('motor', 'like', $like)
                  ->orWhere('placa', 'like', $like)
                  ->orWhere('estado', 'like', $like)
                  ->orWhere('nip', 'like', $like)
                  ->orWhere('poliza_hdi', 'like', $like)
                  ->orWhere('tarjeta_si_vale_id', 'like', $like)
                  ->orWhere('fec_vencimiento', 'like', $like)
                  ->orWhere('vencimiento_t_circulacion', 'like', $like)
                  ->orWhere('cambio_placas', 'like', $like)
                  ->orWhere('rend', 'like', $like);
            });
        }

        // Campos simples
        if (!empty($filters['propietario'])) {
            $query->where('propietario', 'like', '%' . $filters['propietario'] . '%');
        }
        if (!empty($filters['marca'])) {
            $query->where('marca', $filters['marca']);
        }
        if (!empty($filters['placa'])) {
            $query->where('placa', 'like', '%' . $filters['placa'] . '%');
        }
        if (!empty($filters['serie'])) {
            $query->where('serie', 'like', '%' . $filters['serie'] . '%');
        }
        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }
        if (!empty($filters['unidad'])) {
            $query->where('unidad', 'like', '%' . $filters['unidad'] . '%');
        }

        // Ubicación (arreglo o string)
        if (!empty($filters['ubicacion'])) {
            $ubic = is_array($filters['ubicacion']) ? $filters['ubicacion'] : [$filters['ubicacion']];
            $query->whereIn('ubicacion', $ubic);
        }

        // Año (exacto o rango)
        if (!empty($filters['anio'])) {
            $query->where('anio', $filters['anio']);
        } else {
            if (!empty($filters['anio_min'])) {
                $query->where('anio', '>=', (int) $filters['anio_min']);
            }
            if (!empty($filters['anio_max'])) {
                $query->where('anio', '<=', (int) $filters['anio_max']);
            }
        }

        // Tarjeta SiVale
        if (isset($filters['tarjeta'])) {
            if ($filters['tarjeta'] === 'con') {
                $query->whereNotNull('tarjeta_si_vale_id');
            } elseif ($filters['tarjeta'] === 'sin') {
                $query->whereNull('tarjeta_si_vale_id');
            } elseif (is_numeric($filters['tarjeta'])) {
                $query->where('tarjeta_si_vale_id', (int) $filters['tarjeta']);
            }
        }

        // Póliza HDI
        if (isset($filters['poliza'])) {
            if ($filters['poliza'] === 'con') {
                $query->whereNotNull('poliza_hdi')->where('poliza_hdi', '!=', '');
            } elseif ($filters['poliza'] === 'sin') {
                $query->where(function ($q) {
                    $q->whereNull('poliza_hdi')->orWhere('poliza_hdi', '');
                });
            } else {
                $query->where('poliza_hdi', 'like', '%' . $filters['poliza'] . '%');
            }
        }

        // Fechas de vencimiento
        if (!empty($filters['fec_vencimiento_desde'])) {
            $query->whereDate('fec_vencimiento', '>=', $filters['fec_vencimiento_desde']);
        }
        if (!empty($filters['fec_vencimiento_hasta'])) {
            $query->whereDate('fec_vencimiento', '<=', $filters['fec_vencimiento_hasta']);
        }
        if (!empty($filters['vtc_desde'])) {
            $query->whereDate('vencimiento_t_circulacion', '>=', $filters['vtc_desde']);
        }
        if (!empty($filters['vtc_hasta'])) {
            $query->whereDate('vencimiento_t_circulacion', '<=', $filters['vtc_hasta']);
        }

        // Rendimiento
        if (isset($filters['rend_min']) && $filters['rend_min'] !== '') {
            $query->where('rend', '>=', (float) $filters['rend_min']);
        }
        if (isset($filters['rend_max']) && $filters['rend_max'] !== '') {
            $query->where('rend', '<=', (float) $filters['rend_max']);
        }

        // Rango de creación
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query;
    }

    /**
     * Ordenamiento con whitelist de columnas.
     */
    public function scopeSort($query, ?string $by, ?string $dir = 'asc')
    {
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $whitelist = [
            'id', 'ubicacion', 'propietario', 'unidad', 'marca', 'anio',
            'serie', 'motor', 'placa', 'estado', 'tarjeta_si_vale_id',
            'fec_vencimiento', 'vencimiento_t_circulacion', 'cambio_placas',
            'poliza_hdi', 'rend', 'created_at',
        ];

        if ($by && in_array($by, $whitelist, true)) {
            return $query->orderBy($by, $dir);
        }

        return $query->orderBy('created_at', 'desc'); // por defecto, recientes primero
    }
}
