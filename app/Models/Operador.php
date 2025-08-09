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

    /**
     * Relación: un operador pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
