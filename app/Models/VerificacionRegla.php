<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'estados',           // (legibles) - opcionalmente seguir usándolo
        'notas',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin'    => 'date',
        'estados'         => 'array',
    ];

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
}
