<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CalendarioVerificacion extends Model
{
    protected $table = 'calendario_verificacion';

    protected $fillable = [
        'estado','terminacion','mes_inicio','mes_fin','semestre',
        'frecuencia','anio','vigente_desde','vigente_hasta'
    ];

    protected $casts = [
        'terminacion' => 'integer',
        'mes_inicio'  => 'integer',
        'mes_fin'     => 'integer',
        'semestre'    => 'integer',
        'anio'        => 'integer',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function scopeDeEstadoYTerminacion(Builder $q, string $estado, int $terminacion): Builder
    {
        return $q->where('estado', $estado)->where('terminacion', $terminacion);
    }

    public function getPeriodoLabelAttribute(): string
    {
        $mes = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
        $a = $mes[$this->mes_inicio] ?? $this->mes_inicio;
        $b = $mes[$this->mes_fin]    ?? $this->mes_fin;
        return "{$a}-{$b}".($this->anio ? " {$this->anio}" : '');
    }
}
