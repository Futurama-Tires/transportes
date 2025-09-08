<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capturista extends Model
{
    use HasFactory;

    protected $table = 'capturistas';

    protected $fillable = [
        'user_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
    ];

    protected $appends = ['nombre_completo'];

    /**
     * Relación: un capturista pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accesor para "nombre_completo".
     */
    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
        ]);
        return trim(implode(' ', $partes));
    }

    /**
     * Filtro principal: búsqueda global + ordenamiento.
     *
     * Acepta:
     * - search   (string)   // búsqueda global
     * - sort_by  (string)   // 'nombre_completo' | 'email'
     * - sort_dir (string)   // 'asc' | 'desc'
     */
    public function scopeFilter($query, array $filters)
    {
        // --- BÚSQUEDA GLOBAL (todos los campos relevantes) ---
        $query->when($filters['search'] ?? null, function ($q, $term) {
            $term = trim($term);
            $like = '%' . $term . '%';

            $q->where(function ($qq) use ($like) {
                // Campos locales
                $qq->where('capturistas.nombre', 'like', $like)
                   ->orWhere('capturistas.apellido_paterno', 'like', $like)
                   ->orWhere('capturistas.apellido_materno', 'like', $like)
                   // Nombre completo
                   ->orWhereRaw("CONCAT_WS(' ', capturistas.nombre, capturistas.apellido_paterno, capturistas.apellido_materno) LIKE ?", [$like])
                   // ID (como texto)
                   ->orWhereRaw("CAST(capturistas.id AS CHAR) LIKE ?", [$like])
                   // Relación user: email / name
                   ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('email', 'like', $like)
                           ->orWhere('name', 'like', $like);
                   });
            });
        });

        // --- ORDENAMIENTO ---
        $allowedSorts = ['nombre_completo', 'email'];
        $by  = $filters['sort_by'] ?? 'nombre_completo';
        $by  = in_array($by, $allowedSorts, true) ? $by : 'nombre_completo';

        $dir = strtolower($filters['sort_dir'] ?? 'asc');
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        if ($by === 'email') {
            // Orden por correo del usuario
            $query->leftJoin('users', 'users.id', '=', 'capturistas.user_id')
                  ->select('capturistas.*')
                  ->orderBy('users.email', $dir);
        } else {
            // Orden por nombre completo
            $query->orderByRaw("CONCAT_WS(' ', capturistas.nombre, capturistas.apellido_paterno, capturistas.apellido_materno) {$dir}");
        }

        return $query;
    }
}
