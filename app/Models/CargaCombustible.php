<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaCombustible extends Model
{
    use HasFactory;

    protected $table = 'cargas_combustible';

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
        // 'mes' se calcula en el controlador
    ];

    protected $casts = [
        'fecha'       => 'date:Y-m-d',
        'litros'      => 'decimal:3',
        'precio'      => 'decimal:2',
        'total'       => 'decimal:2',
        'rendimiento' => 'decimal:2',
        'diferencia'  => 'decimal:2',
        'km_inicial'  => 'integer',
        'km_final'    => 'integer',
        'recorrido'   => 'integer',
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

    /** Fotos 1:N (igual patrón que vehículos/operadores) */
    public function fotos()
    {
        return $this->hasMany(CargaFoto::class, 'carga_id');
    }

    /** Atajos por tipo (opcional) */
    public function fotosTicket()
    {
        return $this->fotos()->where('tipo', CargaFoto::TICKET);
    }
    public function fotosVoucher()
    {
        return $this->fotos()->where('tipo', CargaFoto::VOUCHER);
    }
    public function fotosOdometro()
    {
        return $this->fotos()->where('tipo', CargaFoto::ODOMETRO);
    }

    /** Scope de filtros y ordenamiento (para la vista web) */
    public function scopeFilter($query, array $filters)
    {
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
                   ->orWhereRaw("CAST(cargas_combustible.litros AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.precio AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.total  AS CHAR) LIKE ?", [$like])
                   ->orWhereRaw("CAST(cargas_combustible.rendimiento AS CHAR) LIKE ?", [$like])
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

        if (!empty($filters['from'])) $query->whereDate('cargas_combustible.fecha', '>=', $filters['from']);
        if (!empty($filters['to']))   $query->whereDate('cargas_combustible.fecha', '<=', $filters['to']);

        $ranges = [
            ['litros', 'litros_min', '>='], ['litros', 'litros_max', '<='],
            ['precio', 'precio_min', '>='], ['precio', 'precio_max', '<='],
            ['total',  'total_min',  '>='], ['total',  'total_max',  '<='],
            ['rendimiento', 'rend_min', '>='], ['rendimiento', 'rend_max', '<='],
            ['km_inicial',  'km_ini_min', '>='], ['km_inicial',  'km_ini_max', '<='],
            ['km_final',    'km_fin_min', '>='], ['km_final',    'km_fin_max', '<='],
            ['recorrido',   'rec_min', '>='],   ['recorrido',    'rec_max', '<='],
        ];
        foreach ($ranges as [$col, $key, $op]) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $query->where("cargas_combustible.$col", $op, $filters[$key]);
            }
        }

        $query->when($filters['destino']  ?? null, fn($q, $v) => $q->where('cargas_combustible.destino', 'like', '%'.$v.'%'));
        $query->when($filters['custodio'] ?? null, fn($q, $v) => $q->where('cargas_combustible.custodio','like', '%'.$v.'%'));

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
            'recorrido'        => 'cargas_combustible.recorrido',
            'vehiculo'         => 'vehiculos.unidad',
            'placa'            => 'vehiculos.placa',
            'operador'         => null,
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
