<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaCombustible extends Model
{
    use HasFactory;

    protected $table = 'cargas_combustible';

    // Si ya tienes $fillable definido, puedes conservarlo y quitar este bloque.
    protected $fillable = [
        'ubicacion',
        'fecha',
        'precio',
        'tipo_combustible',
        'litros',
        'custodio',
        'operador_id',
        'vehiculo_id',
        'km_inicial',
        'km_final',
        'recorrido',
        'rendimiento',
        'diferencia',
        'total',
        'destino',
        'observaciones',
    ];

    // Opciones fijas conocidas
    public const UBICACIONES = ['Cuernavaca', 'Ixtapaluca', 'Queretaro', 'Vallejo', 'Guadalajara'];
    public const TIPOS_COMBUSTIBLE = ['Magna', 'Diesel', 'Premium'];

    public function operador()
    {
        return $this->belongsTo(Operador::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /**
     * Scope principal: búsqueda global + filtros + ordenamiento.
     *
     * Acepta en $filters:
     * - search (string)
     * - vehiculo_id (int)
     * - operador_id (int)
     * - ubicacion (string)
     * - tipo_combustible (string)
     * - from (YYYY-MM-DD)
     * - to   (YYYY-MM-DD)
     * - litros_min, litros_max (numeric)
     * - precio_min, precio_max (numeric)
     * - total_min,  total_max  (numeric)
     * - rend_min,   rend_max   (numeric)
     * - km_ini_min, km_ini_max (int)
     * - km_fin_min, km_fin_max (int)
     * - destino (string, like)
     * - custodio (string, like)
     * - sort_by  (string)
     * - sort_dir ('asc'|'desc')
     */
    public function scopeFilter($query, array $filters)
    {
        // Joins para poder buscar/ordenar por campos relacionados
        $query->leftJoin('vehiculos', 'vehiculos.id', '=', 'cargas_combustible.vehiculo_id')
              ->leftJoin('operadores', 'operadores.id', '=', 'cargas_combustible.operador_id')
              ->select('cargas_combustible.*');

        // --- BÚSQUEDA GLOBAL ---
        $query->when($filters['search'] ?? null, function ($q, $term) {
            $term = trim($term);
            $like = '%' . $term . '%';

            $q->where(function ($qq) use ($like) {
                $qq->where('cargas_combustible.ubicacion', 'like', $like)
                   ->orWhere('cargas_combustible.destino', 'like', $like)
                   ->orWhere('cargas_combustible.observaciones', 'like', $like)
                   ->orWhere('cargas_combustible.custodio', 'like', $like)
                   ->orWhere('cargas_combustible.tipo_combustible', 'like', $like)
                   ->orWhereRaw("CAST(cargas_combustible.id AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("DATE_FORMAT(cargas_combustible.fecha, '%Y-%m-%d') LIKE ?", [$like])
                   // numéricos como texto (útil cuando se pega un número en la búsqueda)
                   ->orWhereRaw("CAST(cargas_combustible.litros AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.precio AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.total  AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.rendimiento AS CHAR) LIKE ?", [$like])
                   // Relacionados
                   ->orWhere('vehiculos.unidad', 'like', $like)
                   ->orWhere('vehiculos.placa',  'like', $like)
                   ->orWhereRaw("CONCAT_WS(' ', operadores.nombre, operadores.apellido_paterno, operadores.apellido_materno) LIKE ?", [$like]);
            });
        });

        // --- FILTROS ---
        $query->when($filters['vehiculo_id'] ?? null, fn($q, $id) => $q->where('cargas_combustible.vehiculo_id', $id));
        $query->when($filters['operador_id'] ?? null, fn($q, $id) => $q->where('cargas_combustible.operador_id', $id));

        $query->when($filters['ubicacion'] ?? null, function ($q, $u) {
            if ($u !== '') $q->where('cargas_combustible.ubicacion', $u);
        });

        $query->when($filters['tipo_combustible'] ?? null, function ($q, $t) {
            if ($t !== '') $q->where('cargas_combustible.tipo_combustible', $t);
        });

        // Rango de fechas (inclusive)
        if (!empty($filters['from'])) {
            $query->whereDate('cargas_combustible.fecha', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('cargas_combustible.fecha', '<=', $filters['to']);
        }

        // Rangos numéricos
        $ranges = [
            ['litros', 'litros_min', '>='], ['litros', 'litros_max', '<='],
            ['precio', 'precio_min', '>='], ['precio', 'precio_max', '<='],
            ['total',  'total_min',  '>='], ['total',  'total_max',  '<='],
            ['rendimiento', 'rend_min', '>='], ['rendimiento', 'rend_max', '<='],
            ['km_inicial',  'km_ini_min', '>='], ['km_inicial',  'km_ini_max', '<='],
            ['km_final',    'km_fin_min', '>='], ['km_final',    'km_fin_max', '<='],
        ];
        foreach ($ranges as [$col, $key, $op]) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $query->where("cargas_combustible.$col", $op, $filters[$key]);
            }
        }

        // Campos de texto adicionales
        $query->when($filters['destino']   ?? null, fn($q, $v) => $q->where('cargas_combustible.destino', 'like', '%'.$v.'%'));
        $query->when($filters['custodio']  ?? null, fn($q, $v) => $q->where('cargas_combustible.custodio','like', '%'.$v.'%'));

        // --- ORDENAMIENTO ---
        $map = [
            'id'               => 'cargas_combustible.id',
            'fecha'            => 'cargas_combustible.fecha',
            'ubicacion'        => 'cargas_combustible.ubicacion',
            'tipo_combustible' => 'cargas_combustible.tipo_combustible',
            'litros'           => 'cargas_combustible.litros',
            'precio'           => 'cargas_combustible.precio',
            'total'            => 'cargas_combustible.total',
            'rendimiento'      => 'cargas_combustible.rendimiento',
            'km_inicial'       => 'cargas_combustible.km_inicial',
            'km_final'         => 'cargas_combustible.km_final',
            'vehiculo'         => 'vehiculos.unidad',
            'placa'            => 'vehiculos.placa',
            'operador'         => null, // se ordena por nombre completo con raw
        ];

        $by  = $filters['sort_by'] ?? 'fecha';
        $dir = strtolower($filters['sort_dir'] ?? 'desc');
        $dir = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

        if ($by === 'operador') {
            $query->orderByRaw("CONCAT_WS(' ', operadores.nombre, operadores.apellido_paterno, operadores.apellido_materno) {$dir}");
        } else {
            $col = $map[$by] ?? $map['fecha'];
            $query->orderBy($col, $dir);
        }

        return $query;
    }
}
