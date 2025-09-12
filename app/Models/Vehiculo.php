<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // <— AÑADIDO
use App\Models\VehiculoFoto;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    // Atributos que deseas siempre en MAYÚSCULAS
    public const UPPERCASE = [
        'ubicacion',
        'propietario',
        'unidad',
        'marca',
        'serie',
        'motor',
        'placa',
        'estado',
        'poliza_hdi',
    ];

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
        // Si quieres, puedes castear fechas:
        // 'fec_vencimiento' => 'date',
        // 'vencimiento_t_circulacion' => 'date',
        // 'cambio_placas' => 'date',
    ];

    // ====== Normalización a MAYÚSCULAS ======
    protected static function booted()
    {
        static::saving(function (self $vehiculo) {
            foreach (self::UPPERCASE as $attr) {
                $value = $vehiculo->{$attr};
                if (is_string($value)) {
                    // recorta y normaliza espacios
                    $value = preg_replace('/\s+/u', ' ', trim($value));
                    // convierte respetando acentos/ñ
                    $vehiculo->{$attr} = Str::upper($value);
                }
            }
        });
    }

    // ====== RELACIONES ======
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
        return $this->hasMany(VehiculoFoto::class)
                    ->orderBy('orden')
                    ->orderByDesc('created_at');
    }

    // ====== SCOPES ======
    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $like = '%' . str_replace('%', '\\%', $term) . '%';
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

        if (!empty($filters['id']))        $query->where('id', (int)$filters['id']);
        if (!empty($filters['unidad']))    $query->where('unidad', 'like', '%' . $filters['unidad'] . '%');
        if (!empty($filters['placa']))     $query->where('placa', 'like', '%' . $filters['placa'] . '%');
        if (!empty($filters['serie']))     $query->where('serie', 'like', '%' . $filters['serie'] . '%');
        if (!empty($filters['propietario'])) $query->where('propietario', 'like', '%' . $filters['propietario'] . '%');
        if (!empty($filters['marca']))     $query->where('marca', $filters['marca']);

        if (!empty($filters['anio'])) {
            $query->where('anio', $filters['anio']);
        } else {
            if (!empty($filters['anio_min'])) $query->where('anio', '>=', (int) $filters['anio_min']);
            if (!empty($filters['anio_max'])) $query->where('anio', '<=', (int) $filters['anio_max']);
        }

        return $query;
    }

    public function scopeSort($query, ?string $by, ?string $dir = 'asc')
    {
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $whitelist = ['id','unidad','placa','serie','anio','propietario','marca','created_at'];

        if ($by && in_array($by, $whitelist, true)) {
            return $query->orderBy($by, $dir);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
