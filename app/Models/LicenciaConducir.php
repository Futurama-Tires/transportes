<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LicenciaConducir extends Model
{
    use HasFactory;

    protected $table = 'licencias_conducir';

    protected $fillable = [
        'operador_id',
        'ambito',               // 'federal' | 'estatal'
        'tipo',                 // Ej. A, B, C, Chofer
        'folio',
        'fecha_expedicion',
        'fecha_vencimiento',
        'emisor',               // SCT / Secretaría de Movilidad, etc.
        'estado_emision',       // Estado/Ciudad
        'observaciones',
    ];

    protected $casts = [
        'fecha_expedicion'  => 'date',
        'fecha_vencimiento' => 'date',
    ];

    protected $appends = [
        'esta_vencida',
        'dias_restantes',
        'estatus',
    ];

    /* =======================
     * Relaciones
     * ======================= */
    public function operador()
    {
        return $this->belongsTo(Operador::class);
    }

    public function archivos()
    {
        return $this->hasMany(LicenciaArchivo::class, 'licencia_id');
    }

    /* =======================
     * Mutators (normalización)
     * ======================= */
    public function setAmbitoAttribute($value): void
    {
        $this->attributes['ambito'] = is_string($value) ? strtolower(trim($value)) : $value;
    }

    public function setTipoAttribute($value): void
    {
        $this->attributes['tipo'] = is_string($value) ? strtoupper(trim($value)) : $value;
    }

    public function setFolioAttribute($value): void
    {
        $this->attributes['folio'] = $value !== null
            ? strtoupper(preg_replace('/\s+/', '', trim($value)))
            : null;
    }

    public function setEmisorAttribute($value): void
    {
        $this->attributes['emisor'] = $value !== null ? trim($value) : null;
    }

    public function setEstadoEmisionAttribute($value): void
    {
        $this->attributes['estado_emision'] = $value !== null ? trim($value) : null;
    }

    /* =======================
     * Accesores / Helpers
     * ======================= */
    public function getEstaVencidaAttribute(): bool
    {
        return (bool) ($this->fecha_vencimiento && now()->gt($this->fecha_vencimiento));
    }

    public function getDiasRestantesAttribute(): ?int
    {
        return $this->fecha_vencimiento
            ? now()->diffInDays($this->fecha_vencimiento, false) // negativo si ya venció
            : null;
    }

    public function getEstatusAttribute(): ?string
    {
        if (!$this->fecha_vencimiento) return null;

        $restantes = $this->dias_restantes;
        if ($restantes === null) return null;

        if ($restantes < 0)   return 'vencida';
        if ($restantes <= 30) return 'por_vencer';
        return 'vigente';
    }

    /* =======================
     * Scopes
     * ======================= */
    public function scopeVigentes(Builder $q): Builder
    {
        return $q->whereDate('fecha_vencimiento', '>=', now()->toDateString());
    }

    public function scopeVencidas(Builder $q): Builder
    {
        return $q->whereDate('fecha_vencimiento', '<', now()->toDateString());
    }

    public function scopePorVencer(Builder $q, int $dias = 30): Builder
    {
        $hoy = now()->startOfDay();
        $lim = now()->addDays($dias)->endOfDay();
        return $q->whereBetween('fecha_vencimiento', [$hoy, $lim]);
    }

    /** Búsqueda libre por folio, tipo, emisor, estado_emision */
    public function scopeSearch(Builder $q, ?string $s): Builder
    {
        $s = trim((string) $s);
        if ($s === '') return $q;

        $like = "%{$s}%";
        return $q->where(function (Builder $qq) use ($like) {
            $qq->where('folio', 'like', $like)
               ->orWhere('tipo', 'like', $like)
               ->orWhere('emisor', 'like', $like)
               ->orWhere('estado_emision', 'like', $like);
        });
    }

    /** Filtro por ámbito (federal/estatal) */
    public function scopeAmbito(Builder $q, ?string $ambito): Builder
    {
        $ambito = strtolower(trim((string) $ambito));
        if ($ambito === '') return $q;
        return $q->where('ambito', $ambito);
    }

    /** Filtro por estatus (vigente|por_vencer|vencida) */
    public function scopeEstatus(Builder $q, ?string $estatus, int $diasPorVencer = 30): Builder
    {
        $estatus = strtolower(trim((string) $estatus));
        return match ($estatus) {
            'vigente'    => $q->vigentes(),
            'por_vencer' => $q->porVencer($diasPorVencer),
            'vencida'    => $q->vencidas(),
            default      => $q,
        };
    }
}
