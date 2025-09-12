<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para la entidad "Capturista".
 *
 * Responsabilidades:
 * - Representar a un capturista y sus atributos primarios.
 * - Exponer la relación con User (pertenece a un usuario del sistema).
 * - Proveer un accesor calculado para "nombre_completo".
 * - Ofrecer un alcance (scope) de filtrado y ordenamiento seguro para listados.
 *
 * Consideraciones de rendimiento (producción):
 * - La búsqueda global usa LIKE con comodines (%…%), lo cual puede ser costoso en tablas grandes.
 *   Para alto volumen, evalúa índices compuestos/funcionales y/o FULLTEXT (MySQL) sobre los campos
 *   consultados frecuentemente (nombre/apellidos/email) según tu motor y versión.
 * - El ordenamiento por email hace un LEFT JOIN con "users"; asegurar índice sobre users.email y FK capturistas.user_id.
 * - Se usa select('capturistas.*') al ordenar por email para evitar colisión de columnas en JOINS.
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property string|null         $nombre
 * @property string|null         $apellido_paterno
 * @property string|null         $apellido_materno
 * @property-read string         $nombre_completo  Accesor calculado y incluido en arrays/JSON por $appends.
 * @property-read \App\Models\User $user
 *
 * @method static Builder|self filter(array $filters)  // Scope para búsqueda y ordenamiento
 */
class Capturista extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla explícito (buena práctica para evitar acoplamiento implícito).
     * @var string
     */
    protected $table = 'capturistas';

    /**
     * Atributos habilitados para asignación masiva (mass assignment).
     * Mantén esta lista estricta para evitar vulnerabilidades de sobreasignación.
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
    ];

    /**
     * Atributos accesores que se anexarán automáticamente al convertir el modelo a array/JSON.
     * @var array<int, string>
     */
    protected $appends = ['nombre_completo'];

    /**
     * Relación: un capturista pertenece a un usuario del sistema.
     *
     * Nota operativa:
     * - Si necesitas eliminar en cascada, configura la FK en la migración con onDelete('cascade')
     *   o maneja la lógica correspondiente en servicios/observers.
     *
     * @return BelongsTo<\App\Models\User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accesor: compone el "nombre completo" con tolerancia a valores nulos/vacíos.
     *
     * - array_filter() elimina null/'' evitando dobles espacios.
     * - implode() arma el string final con separador único.
     * - trim() asegura no dejar espacios al inicio/fin.
     *
     * @return string
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
     * Scope de filtrado y ordenamiento para listados.
     *
     * Parámetros aceptados en $filters:
     * - search   (string)   Búsqueda global (id, nombre(s), apellidos, email y name del usuario relacionado).
     * - sort_by  (string)   Campo permitido para ordenar: 'nombre_completo' | 'email'. Por defecto: 'nombre_completo'.
     * - sort_dir (string)   Dirección de orden: 'asc' | 'desc'. Por defecto: 'asc'.
     *
     * Seguridad:
     * - Se valida sort_by contra una whitelist para evitar inyección por columnas.
     * - sort_dir se normaliza a 'asc'|'desc' antes de interpolar en orderByRaw.
     * - Las consultas LIKE utilizan bindings (?) cuando corresponde.
     *
     * Rendimiento:
     * - Considera agregar índices sobre (apellido_paterno, apellido_materno, nombre) y users(email)
     *   dependiendo del patrón de acceso.
     *
     * @param  Builder $query
     * @param  array<string, mixed> $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // --- BÚSQUEDA GLOBAL (campos locales + relación user) ---
        $query->when($filters['search'] ?? null, function (Builder $q, $term) {
            $term = trim((string) $term);
            $like = '%' . $term . '%';

            $q->where(function (Builder $qq) use ($like) {
                // Campos locales
                $qq->where('capturistas.nombre', 'like', $like)
                   ->orWhere('capturistas.apellido_paterno', 'like', $like)
                   ->orWhere('capturistas.apellido_materno', 'like', $like)
                   // Nombre completo (evita concatenación en PHP para permitir orden/búsqueda en SQL)
                   ->orWhereRaw(
                       "CONCAT_WS(' ', capturistas.nombre, capturistas.apellido_paterno, capturistas.apellido_materno) LIKE ?",
                       [$like]
                   )
                   // ID como texto (útil cuando el usuario pega un número parcial)
                   ->orWhereRaw("CAST(capturistas.id AS CHAR) LIKE ?", [$like])
                   // Relación user: email / name
                   ->orWhereHas('user', function (Builder $uq) use ($like) {
                       $uq->where('email', 'like', $like)
                          ->orWhere('name', 'like', $like);
                   });
            });
        });

        // --- ORDENAMIENTO ---
        $allowedSorts = ['nombre_completo', 'email'];

        // Columna de orden: restringida a la whitelist
        $by = $filters['sort_by'] ?? 'nombre_completo';
        $by = in_array($by, $allowedSorts, true) ? $by : 'nombre_completo';

        // Dirección de orden: normalizada
        $dir = strtolower((string) ($filters['sort_dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($by === 'email') {
            // Orden por correo del usuario (LEFT JOIN para incluir capturistas sin usuario, si aplicara)
            // select('capturistas.*') previene ambigüedad de columnas al hacer join.
            $query->leftJoin('users', 'users.id', '=', 'capturistas.user_id')
                  ->select('capturistas.*')
                  ->orderBy('users.email', $dir);
        } else {
            // Orden por nombre completo (se arma en SQL para permitir index/funciones del motor)
            // Interpolación segura: $dir está validado a 'asc'|'desc'.
            $query->orderByRaw(
                "CONCAT_WS(' ', capturistas.nombre, capturistas.apellido_paterno, capturistas.apellido_materno) {$dir}"
            );
        }

        return $query;
    }
}
