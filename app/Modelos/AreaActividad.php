<?php

namespace App\Modelos;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class AreaActividad  extends Model
{
   
    protected $table='area_actividad';
    protected $primaryKey = 'id_area_actividad';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_area','descripcion'
    ];

}