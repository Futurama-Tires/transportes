<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TarjetaComodin extends Model
{
    use HasFactory;

    protected $table = 'tarjetas_comodin';

    protected $fillable = [
        'numero_tarjeta',
        'nip',
        'fecha_vencimiento',
        'descripcion',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date', // lo formateas en la vista con ->format('Y-m-d')
    ];

    protected $appends = ['last4', 'numero_enmascarado'];

    /* =========================
     | Relaciones
     * ========================= */
    public function gastos()
    {
        return $this->hasMany(ComodinGasto::class, 'tarjeta_comodin_id');
    }

    /* =========================
     | Accessors
     * ========================= */
    /** Últimos 4 dígitos (solo números) */
    public function getLast4Attribute(): ?string
    {
        $num = preg_replace('/\D+/', '', (string) $this->numero_tarjeta);
        return $num ? substr($num, -4) : null;
    }

    /** Número enmascarado con •••• */
    public function getNumeroEnmascaradoAttribute(): string
    {
        $num = preg_replace('/\D+/', '', (string) $this->numero_tarjeta);
        if (!$num) return '—';
        $len = strlen($num);
        return str_repeat('•', max(0, $len - 4)) . substr($num, -4);
    }

    /* =========================
     | Mutators (normalización)
     * ========================= */
    /** Guarda solo dígitos en numero_tarjeta (seguro incluso si ya validaste). */
    public function setNumeroTarjetaAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['numero_tarjeta'] = $digits ?: null;
    }

    /** Guarda NIP como null si viene vacío. */
    public function setNipAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['nip'] = ($v === '') ? null : $v;
    }

    /**
     * Acepta 'YYYY-MM' (guarda fin de mes) o cualquier fecha parseable por Carbon.
     * Si viene vacío o no parseable -> null.
     */
    public function setFechaVencimientoAttribute($value): void
    {
        if ($value === null) { $this->attributes['fecha_vencimiento'] = null; return; }

        $raw = trim((string) $value);
        if ($raw === '') { $this->attributes['fecha_vencimiento'] = null; return; }

        // YYYY-MM => último día del mes
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $raw)) {
            $date = Carbon::createFromFormat('Y-m', $raw)->endOfMonth();
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
            return;
        }

        try {
            $date = Carbon::parse($raw);
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
        } catch (\Throwable $e) {
            $this->attributes['fecha_vencimiento'] = null;
        }
    }

    /* =========================
     | Query Scopes (búsqueda/filtros/orden)
     * ========================= */

    /**
     * Búsqueda global por descripción, NIP y número (solo dígitos).
     * Evita iniciar el grupo con OR.
     */
    public function scopeSearch(Builder $q, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') return $q;

        $digits = preg_replace('/\D+/', '', $search);

        return $q->where(function (Builder $qq) use ($search, $digits) {
            $qq->where('descripcion', 'like', "%{$search}%")
               ->orWhere('nip', 'like', "%{$search}%");

            if ($digits !== '') {
                $qq->orWhere('numero_tarjeta', 'like', "%{$digits}%");
            }
        });
    }

    /** Coincidencia por últimos 4 (portable: LIKE '%1234'). */
    public function scopeUltimos4(Builder $q, ?string $val): Builder
    {
        $digits = preg_replace('/\D+/', '', (string) $val);
        if ($digits === '') return $q;
        $last4 = substr($digits, -4);
        return $q->where('numero_tarjeta', 'like', "%{$last4}");
    }

    /** '1' => con NIP (no null/ni vacío), '0' => sin NIP (null o ''). */
    public function scopeTieneNip(Builder $q, $flag): Builder
    {
        if ($flag === '1') {
            return $q->whereNotNull('nip')->where('nip', '!=', '');
        }
        if ($flag === '0') {
            return $q->where(function (Builder $qq) {
                $qq->whereNull('nip')->orWhere('nip', '=', '');
            });
        }
        return $q;
    }

    public function scopeVenceFrom(Builder $q, ?string $from): Builder
    {
        return $from ? $q->whereDate('fecha_vencimiento', '>=', $from) : $q;
    }

    public function scopeVenceTo(Builder $q, ?string $to): Builder
    {
        return $to ? $q->whereDate('fecha_vencimiento', '<=', $to) : $q;
    }

    /** $estado: vencida | por_vencer (≤30d) | vigente (>30d) */
    public function scopeEstado(Builder $q, ?string $estado): Builder
    {
        if (!$estado) return $q;

        $today = now()->startOfDay();
        $limit = $today->copy()->addDays(30)->endOfDay();

        if ($estado === 'vencida') {
            return $q->whereDate('fecha_vencimiento', '<', $today);
        }
        if ($estado === 'por_vencer') {
            return $q->whereBetween('fecha_vencimiento', [$today, $limit]);
        }
        if ($estado === 'vigente') {
            return $q->whereDate('fecha_vencimiento', '>', $limit);
        }
        return $q;
    }

    public function scopeOrdenar(Builder $q, ?string $by, ?string $dir): Builder
    {
        $by  = in_array($by, ['numero_tarjeta','fecha_vencimiento','id'], true) ? $by : 'numero_tarjeta';
        $dir = in_array($dir, ['asc','desc'], true) ? $dir : 'asc';
        return $q->orderBy($by, $dir);
    }
}
