<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Mensajes extends Model
{
    protected $table='mensajes';
    protected $primaryKey = 'id_mensajes';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fk_actividad','fk_user','descripcion_mensaje'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    
}
