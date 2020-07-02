<?php

namespace App\Modelos;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Actividades  extends Model
{
   
    protected $table='Actividades';
    protected $primaryKey = 'id_actividad';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'titulo','archivo','estado_actividad','descripcion','user_creador', 'fk_area_actividad',
    ];

    public function userEncargado()
    {
        return $this->belongsToMany('App\User','actividaddetalle','id_actividad','id_usuario');
       // ->withPivot('estado_actividad_creador', 'estado_actividad_encargado');
    }
    
    public function usuario (){
        return $this->belongsTo('App\User','user_creador');
    }



    public function area(){
        return $this->belongsTo('App\Modelos\AreaActividad','fk_area_actividad');
    }

   
    

}

//
