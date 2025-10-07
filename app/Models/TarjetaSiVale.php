<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo;
use App\Models\CargaCombustible;
use Carbon\Carbon;

/**
 * Representa una tarjeta SiVale asignada a uno o varios vehículos.
 * Desde aquí podemos llegar a todas las cargas realizadas por esos vehículos.
 */
class TarjetaSiVale extends Model
{
    use HasFactory;

    /**
     * Ajusta este nombre al de tu BD real.
     * Basado en tus inserts previos: "tarjetassivale".
     */
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

    /**
     * 1:N Tarjeta → Vehículos
     * FK en vehiculos: vehiculos.tarjeta_si_vale_id
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tarjeta_si_vale_id');
    }

    /**
     * Tarjeta → Vehículos → Cargas (hasManyThrough)
     */
    public function cargas()
    {
        return $this->hasManyThrough(
            CargaCombustible::class, // Modelo final
            Vehiculo::class,         // Modelo intermedio
            'tarjeta_si_vale_id',    // FK en Vehiculo que apunta a TarjetaSiVale
            'vehiculo_id',           // FK en CargaCombustible que apunta a Vehiculo
            'id',                    // PK local en TarjetaSiVale
            'id'                     // PK local en Vehiculo
        );
    }

    /**
     * Atajo 1:1 (si aplica en tu negocio)
     */
    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'tarjeta_si_vale_id');
    }

    // =========================
    // Mutators (normalización)
    // =========================

    /**
     * Guarda solo dígitos en numero_tarjeta.
     */
    public function setNumeroTarjetaAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['numero_tarjeta'] = $digits ?: null;
    }

    /**
     * Guarda nip como 4 dígitos (o null si viene vacío).
     */
    public function setNipAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['nip'] = ($digits !== '' ? $digits : null);
    }

    /**
     * Normaliza múltiples formatos a 'YYYY-MM-DD'.
     * Soporta:
     *  - 'YYYY-MM'        => último día del mes
     *  - 'YYYY-MM-DD'     => se respeta
     *  - 'VEN-09-27'      => MM-YY → último día del mes (año 20YY)
     *  - '09-27'          => MM-YY → último día del mes (año 20YY)
     *  - 'jul-26'/'dic-25'=> Mmm-YY en español → último día del mes (año 20YY)
     */
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

        // 2) Mmm-YY en español (ene,feb,mar,abr,may,jun,jul,ago,sep,oct,nov,dic)
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

        // 4) YYYY-MM-DD  (o algo parseable por Carbon)
        try {
            $date = Carbon::parse($raw);
            $this->attributes['fecha_vencimiento'] = $date->format('Y-m-d');
        } catch (\Throwable $e) {
            // Si no es parseable, preferimos null antes que guardar un valor corrupto
            $this->attributes['fecha_vencimiento'] = null;
        }
    }
}
