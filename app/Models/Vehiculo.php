<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\VehiculoFoto;
use App\Models\TarjetaSiVale; // ← importe explícito para la relación belongsTo

/**
 * Class Vehiculo
 *
 * @property int         $id
 * @property string|null $ubicacion
 * @property string|null $propietario
 * @property string|null $unidad
 * @property string|null $marca
 * @property int|null    $anio
 * @property string|null $serie
 * @property string|null $motor
 * @property string|null $placa
 * @property string|null $estado
 * @property int|null    $tarjeta_si_vale_id
 * @property string|null $nip
 * @property string|null $fec_vencimiento
 * @property string|null $vencimiento_t_circulacion
 * @property string|null $cambio_placas
 * @property string|null $poliza_hdi
 * @property float|null  $rend
 * @property int|null    $kilometros  Odómetro actual del vehículo
 */
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
        'kilometros', // 👈 nuevo campo (odómetro)
    ];

    protected $casts = [
        'rend'       => 'float',
        'kilometros' => 'integer', // 👈 cast del odómetro
        // Descomenta si esas columnas son DATE/DATETIME en BD:
        // 'fec_vencimiento' => 'date',
        // 'vencimiento_t_circulacion' => 'date',
        // 'cambio_placas' => 'date',
    ];

    /**
     * Normalización a MAYÚSCULAS antes de guardar.
     */
    protected static function booted()
    {
        static::saving(function (self $vehiculo) {
            foreach (self::UPPERCASE as $attr) {
                $value = $vehiculo->{$attr};
                if (is_string($value)) {
                    $value = preg_replace('/\s+/u', ' ', trim($value)); // normaliza espacios
                    $vehiculo->{$attr} = Str::upper($value);            // respeta acentos/ñ
                }
            }
        });
    }

    // ========= RELACIONES =========

    /**
     * Un vehículo puede tener varios tanques (si manejas ese módulo).
     */
    public function tanques()
    {
        return $this->hasMany('App\Models\Tanque');
    }

    /**
     * Verificaciones vehiculares asociadas.
     */
    public function verificaciones()
    {
        return $this->hasMany('App\Models\Verificacion');
    }

    /**
     * Cargas de combustible realizadas por este vehículo.
     * Clave foránea por convención: cargas_combustible.vehiculo_id
     */
    public function cargasCombustible()
    {
        return $this->hasMany('App\Models\CargaCombustible');
    }

    /**
     * La tarjeta SiVale asignada al vehículo.
     * Clave foránea: vehiculos.tarjeta_si_vale_id → tarjetas_sivale.id
     */
    public function tarjetaSiVale()
    {
        return $this->belongsTo(TarjetaSiVale::class, 'tarjeta_si_vale_id');
    }

    /**
     * Fotos del vehículo (ordenadas por 'orden' y luego por fecha desc).
     */
    public function fotos()
    {
        return $this->hasMany(VehiculoFoto::class)
                    ->orderBy('orden')
                    ->orderByDesc('created_at');
    }

    // ========= SCOPES =========

    /**
     * Filtro principal (búsqueda y filtros por campo).
     */
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

        if (!empty($filters['id']))          $query->where('id', (int)$filters['id']);
        if (!empty($filters['unidad']))      $query->where('unidad', 'like', '%' . $filters['unidad'] . '%');
        if (!empty($filters['placa']))       $query->where('placa', 'like', '%' . $filters['placa'] . '%');
        if (!empty($filters['serie']))       $query->where('serie', 'like', '%' . $filters['serie'] . '%');
        if (!empty($filters['propietario'])) $query->where('propietario', 'like', '%' . $filters['propietario'] . '%');
        if (!empty($filters['marca']))       $query->where('marca', $filters['marca']);

        if (!empty($filters['anio'])) {
            $query->where('anio', $filters['anio']);
        } else {
            if (!empty($filters['anio_min'])) $query->where('anio', '>=', (int) $filters['anio_min']);
            if (!empty($filters['anio_max'])) $query->where('anio', '<=', (int) $filters['anio_max']);
        }

        // 👇 Filtros por odómetro
        if (isset($filters['kilometros']) && $filters['kilometros'] !== '') {
            $query->where('kilometros', (int)$filters['kilometros']);
        }
        if (isset($filters['km_min']) && $filters['km_min'] !== '') {
            $query->where('kilometros', '>=', (int)$filters['km_min']);
        }
        if (isset($filters['km_max']) && $filters['km_max'] !== '') {
            $query->where('kilometros', '<=', (int)$filters['km_max']);
        }

        return $query;
    }

    /**
     * Ordenamiento seguro por columnas permitidas.
     */
    public function scopeSort($query, ?string $by, ?string $dir = 'asc')
    {
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $whitelist = ['id','unidad','placa','serie','anio','propietario','marca','kilometros','created_at'];

        if ($by && in_array($by, $whitelist, true)) {
            return $query->orderBy($by, $dir);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
