<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehiculoFoto;

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

    public function fotos()
    {
        // Orden por 'orden' y luego por fecha (útil para galerías)
        return $this->hasMany(VehiculoFoto::class)
                    ->orderBy('orden')
                    ->orderByDesc('created_at');
    }

    // ========== SCOPES ==========
    /**
     * Filtro "todo en uno".
     * Acepta llaves: search, id, unidad, placa, serie, propietario,
     * anio | anio_min | anio_max, marca
     */
    public function scopeFilter($query, array $filters = [])
    {
        // Búsqueda global (sólo los campos solicitados)
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $like = '%' . str_replace('%', '\\%', $term) . '%';
                // id: además de like, intentamos match exacto si es numérico
                if (is_numeric($term)) {
                    $q->orWhere('id', (int)$term);
                }
                $q->orWhere('unidad', 'like', $like)
                  ->orWhere('placa', 'like', $like)
                  ->orWhere('serie', 'like', $like)
                  ->orWhere('anio', 'like', $like)
                  ->orWhere('propietario', 'like', $like);
            });
        }

        // Filtros simples (sólo los solicitados)
        if (!empty($filters['id'])) {
            $query->where('id', (int)$filters['id']);
        }
        if (!empty($filters['unidad'])) {
            $query->where('unidad', 'like', '%' . $filters['unidad'] . '%');
        }
        if (!empty($filters['placa'])) {
            $query->where('placa', 'like', '%' . $filters['placa'] . '%');
        }
        if (!empty($filters['serie'])) {
            $query->where('serie', 'like', '%' . $filters['serie'] . '%');
        }
        if (!empty($filters['propietario'])) {
            $query->where('propietario', 'like', '%' . $filters['propietario'] . '%');
        }
        if (!empty($filters['marca'])) {
            $query->where('marca', $filters['marca']);
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

        return $query;
    }

    /**
     * Ordenamiento con whitelist de columnas.
     */
    public function scopeSort($query, ?string $by, ?string $dir = 'asc')
    {
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $whitelist = [
            'id', 'unidad', 'placa', 'serie', 'anio', 'propietario', 'marca', 'created_at',
        ];

        if ($by && in_array($by, $whitelist, true)) {
            return $query->orderBy($by, $dir);
        }

        return $query->orderBy('created_at', 'desc'); // por defecto, recientes primero
    }
}
