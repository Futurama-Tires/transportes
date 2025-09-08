<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verificacion extends Model
{
    use HasFactory;

    protected $table = 'verificaciones';

    protected $fillable = [
        'vehiculo_id',
        'estado',
        'comentarios',
        'fecha_verificacion',
    ];

    /**
     * Una verificación pertenece a un vehículo.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /**
     * Scope de filtros y búsqueda global.
     *
     * $filters:
     * - search      (string)  Búsqueda global (id, estado, comentarios, fecha, unidad, placa, propietario)
     * - vehiculo_id (int)     Filtrar por vehículo
     * - estado      (string)  Filtrar por estado exacto
     * - from        (date)    Fecha desde (YYYY-MM-DD)
     * - to          (date)    Fecha hasta (YYYY-MM-DD)
     * - sort_by     (string)  'fecha_verificacion' | 'vehiculo' | 'estado' | 'placa' | 'propietario' | 'id'
     * - sort_dir    (string)  'asc' | 'desc'
     */
    public function scopeFilter($query, array $filters)
    {
        // Unimos vehículos para poder buscar/ordenar por sus campos
        $query->leftJoin('vehiculos', 'vehiculos.id', '=', 'verificaciones.vehiculo_id')
              ->select('verificaciones.*');

        // --- BÚSQUEDA GLOBAL ---
        $query->when($filters['search'] ?? null, function ($q, $term) {
            $term = trim($term);
            $like = '%' . $term . '%';

            $q->where(function ($qq) use ($like) {
                $qq->where('verificaciones.estado', 'like', $like)
                  ->orWhere('verificaciones.comentarios', 'like', $like)
                  ->orWhereRaw("CAST(verificaciones.id AS CHAR) LIKE ?", [$like])
                  ->orWhereRaw("DATE_FORMAT(verificaciones.fecha_verificacion, '%Y-%m-%d') LIKE ?", [$like])
                  ->orWhere('vehiculos.unidad', 'like', $like)
                  ->orWhere('vehiculos.placa', 'like', $like)
                  ->orWhere('vehiculos.propietario', 'like', $like);
            });
        });

        // --- FILTROS ESPECÍFICOS ---
        $query->when($filters['vehiculo_id'] ?? null, function ($q, $vehiculoId) {
            $q->where('verificaciones.vehiculo_id', $vehiculoId);
        });

        $query->when($filters['estado'] ?? null, function ($q, $estado) {
            $q->where('verificaciones.estado', $estado);
        });

        // Rango de fechas (inclusive)
        $from = $filters['from'] ?? null;
        $to   = $filters['to'] ?? null;
        if ($from) {
            $query->whereDate('verificaciones.fecha_verificacion', '>=', $from);
        }
        if ($to) {
            $query->whereDate('verificaciones.fecha_verificacion', '<=', $to);
        }

        // --- ORDENAMIENTO ---
        $map = [
            'fecha'              => 'verificaciones.fecha_verificacion',
            'fecha_verificacion' => 'verificaciones.fecha_verificacion',
            'estado'             => 'verificaciones.estado',
            'vehiculo'           => 'vehiculos.unidad',
            'placa'              => 'vehiculos.placa',
            'propietario'        => 'vehiculos.propietario',
            'id'                 => 'verificaciones.id',
        ];

        $byKey = $filters['sort_by'] ?? 'fecha_verificacion';
        $byCol = $map[$byKey] ?? $map['fecha_verificacion'];

        $dir = strtolower($filters['sort_dir'] ?? 'desc');
        $dir = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

        return $query->orderBy($byCol, $dir);
    }
}
