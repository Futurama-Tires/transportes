<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Vehiculo;
use App\Models\CargaCombustible;
use Carbon\Carbon;

class TarjetaSiVale extends Model
{
    use HasFactory;

    protected $table = 'tarjetassivale';

    protected $fillable = [
        'numero_tarjeta',
        'nip',
        'fecha_vencimiento',
        'descripcion',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    // =========================
    // Relaciones
    // =========================

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tarjeta_si_vale_id');
    }

    public function cargas()
    {
        return $this->hasManyThrough(
            CargaCombustible::class,
            Vehiculo::class,
            'tarjeta_si_vale_id',
            'vehiculo_id',
            'id',
            'id'
        );
    }

    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'tarjeta_si_vale_id');
    }

    // =========================
    // Mutators (normalización)
    // =========================

    public function setNumeroTarjetaAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['numero_tarjeta'] = $digits ?: null;
    }

    public function setNipAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['nip'] = ($digits !== '' ? $digits : null);
    }

    public function setFechaVencimientoAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['fecha_vencimiento'] = null;
            return;
        }

        $raw = trim((string) $value);

        // 1) VEN-09-27 / 09-27  (MM-YY)
        if (preg_match('/^(?:VEN-)?(?<mm>0[1-9]|1[0-2])-(?<yy>\d{2})$/i', $raw, $m)) {
            $year  = 2000 + (int) $m['yy'];
            $month = (int) $m['mm'];
            $date  = Carbon::create($year, $month, 1)->endOfMonth();
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
            return;
        }

        // 2) Mmm-YY en español
        if (preg_match('/^(ene|feb|mar|abr|may|jun|jul|ago|sep|oct|nov|dic)-(?<yy>\d{2})$/i', $raw, $m2)) {
            $map = [
                'ene'=>1,'feb'=>2,'mar'=>3,'abr'=>4,'may'=>5,'jun'=>6,
                'jul'=>7,'ago'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dic'=>12
            ];
            $abbr  = strtolower(substr($raw, 0, 3));
            $month = $map[$abbr] ?? null;
            if ($month) {
                $year = 2000 + (int) $m2['yy'];
                $date = Carbon::create($year, $month, 1)->endOfMonth();
                $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
                return;
            }
        }

        // 3) YYYY-MM  (→ fin de mes)
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $raw)) {
            $date = Carbon::createFromFormat('Y-m', $raw)->endOfMonth();
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
            return;
        }

        // 4) YYYY-MM-DD  (o parseable por Carbon)
        try {
            $date = Carbon::parse($raw);
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
        } catch (\Throwable $e) {
            $this->attributes['fecha_vencimiento'] = null;
        }
    }

    // =========================
    // Query Scopes (búsqueda/filtros/orden)
    // =========================

    /**
     * Búsqueda global: numero (normalizado), descripción y NIP.
     * Corrige el bug de iniciar el grupo con orWhere: siempre arranco con where().
     */
    public function scopeSearch(Builder $q, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') return $q;

        $digits = preg_replace('/\D+/', '', $search);

        return $q->where(function (Builder $qq) use ($search, $digits) {
            // Arranco con where para evitar un grupo que empiece con OR
            $qq->where('descripcion', 'like', "%{$search}%")
               ->orWhere('nip', 'like', "%{$search}%");

            if ($digits !== '') {
                $qq->orWhere('numero_tarjeta', 'like', "%{$digits}%")
                   ->orWhereRaw('RIGHT(numero_tarjeta,4) = ?', [substr($digits, -4)]);
            }
        });
    }

    public function scopeUltimos4(Builder $q, ?string $val): Builder
    {
        $digits = preg_replace('/\D+/', '', (string) $val);
        if ($digits === '') return $q;
        return $q->whereRaw('RIGHT(numero_tarjeta,4) = ?', [substr($digits, -4)]);
    }

    /** $flag: '1' con NIP | '0' sin NIP */
    public function scopeTieneNip(Builder $q, $flag): Builder
    {
        if ($flag === '1')  return $q->whereNotNull('nip');
        if ($flag === '0')  return $q->whereNull('nip');
        return $q;
    }

    public function scopeVenceFrom(Builder $q, ?string $from): Builder
    {
        if (!$from) return $q;
        return $q->whereDate('fecha_vencimiento', '>=', $from);
    }

    public function scopeVenceTo(Builder $q, ?string $to): Builder
    {
        if (!$to) return $q;
        return $q->whereDate('fecha_vencimiento', '<=', $to);
    }

    /** $estado: vencida | por_vencer (≤30d) | vigente (>30d) */
    public function scopeEstado(Builder $q, ?string $estado): Builder
    {
        if (!$estado) return $q;

        $today = now()->startOfDay();
        $limit = $today->copy()->addDays(30)->endOfDay();

        if ($estado === 'vencida') {
            return $q->whereNotNull('fecha_vencimiento')
                     ->whereDate('fecha_vencimiento', '<', $today);
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
