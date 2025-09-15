<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operador extends Model
{
    use HasFactory;

    protected $table = 'operadores';

    protected $fillable = [
        'user_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
    ];

    protected $appends = ['nombre_completo'];

    /**
     * Relación: un operador pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: fotos asociadas al operador (para galería).
     * Ordena por 'orden' y luego por 'created_at' desc.
     */
    public function fotos()
    {
        return $this->hasMany(OperadorFoto::class)
            ->orderBy('orden')
            ->orderByDesc('created_at');
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
     * - search   (string) // búsqueda global
     * - sort_by  (string) // 'nombre_completo' | 'email'
     * - sort_dir (string) // 'asc' | 'desc'
     */
    public function scopeFilter($query, array $filters)
    {
        // --- BÚSQUEDA GLOBAL ---
        $query->when($filters['search'] ?? null, function ($q, $term) {
            $term = trim($term);
            $like = '%' . $term . '%';

            $q->where(function ($qq) use ($like) {
                // Campos locales
                $qq->where('operadores.nombre', 'like', $like)
                   ->orWhere('operadores.apellido_paterno', 'like', $like)
                   ->orWhere('operadores.apellido_materno', 'like', $like)
                   // Nombre completo
                   ->orWhereRaw("CONCAT_WS(' ', operadores.nombre, operadores.apellido_paterno, operadores.apellido_materno) LIKE ?", [$like])
                   // Relación user: email y (opcional) name
                   ->orWhereHas('user', function ($uq) use ($like) {
                       $uq->where('email', 'like', $like)
                          ->orWhere('name', 'like', $like);
                   });
            });
        });

        // --- ORDENAMIENTO ---
        $allowedSorts = ['nombre_completo', 'email'];
        $by = $filters['sort_by'] ?? 'nombre_completo';
        if (!in_array($by, $allowedSorts, true)) {
            $by = 'nombre_completo';
        }

        $dir = strtolower($filters['sort_dir'] ?? 'asc');
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        if ($by === 'email') {
            // ordenar por correo del usuario relacionado
            $query->leftJoin('users', 'users.id', '=', 'operadores.user_id')
                  ->select('operadores.*')
                  ->orderBy('users.email', $dir);
        } else {
            // ordenar por nombre completo (ignora NULL con CONCAT_WS)
            $query->orderByRaw("CONCAT_WS(' ', operadores.nombre, operadores.apellido_paterno, operadores.apellido_materno) {$dir}");
        }

        return $query;
    }
}
