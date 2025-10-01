<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VerificacionRegla extends Model
{
    use HasFactory;

    protected $table = 'verificacion_reglas';

    protected $fillable = [
        'nombre',
        'version',
        'status',            // draft|published|archived
        'vigencia_inicio',
        'vigencia_fin',
        'frecuencia',        // Semestral|Anual
        'estados',           // (JSON legible) - opcional
        'notas',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin'    => 'date',
        'estados'         => 'array',
    ];

    /* ===================== Constantes / Helpers ===================== */

    public const STATUS = ['draft','published','archived'];
    public const FRECUENCIAS = ['Semestral','Anual'];

    public static function normalizeEstado(?string $s): string
    {
        $norm = Str::of($s ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        if (in_array($norm, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $norm;
    }

    /** Estados normalizados para un año dado (prefiere tabla puente). */
    public function estadosParaAnio(int $anio): \Illuminate\Support\Collection
    {
        $fromPivot = $this->estadosAsignados()
            ->where('anio', $anio)
            ->pluck('estado')
            ->map(fn($e) => self::normalizeEstado($e))
            ->unique()
            ->values();

        if ($fromPivot->isNotEmpty()) {
            return $fromPivot;
        }

        // Backward-compat (si guardabas estados en JSON)
        $json = collect($this->estados ?? [])
            ->map(fn($e) => self::normalizeEstado($e))
            ->unique()
            ->values();

        return $json;
    }

    /* ===================== Relaciones ===================== */

    public function periodos()
    {
        return $this->hasMany(CalendarioVerificacion::class, 'regla_id');
    }

    public function detalles()
    {
        return $this->hasMany(VerificacionReglaDetalle::class, 'regla_id');
    }

    public function estadosAsignados()
    {
        return $this->hasMany(VerificacionReglaEstado::class, 'regla_id');
    }

    /* ===================== Scopes ===================== */

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }
}
