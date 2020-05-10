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
        'titulo','archivo','estado_actividad','descripcion', 'estado_confirmacion_creador', 'fk_area_actividad','fk_user_encargado'
    ];


    public function usuario (){
        return $this->belongsTo('App\User','fk_user_encargado');
    }

    public function area(){
        return $this->belongsTo('App\Modelos\AreaActividad','fk_area_actividad');
    }

}
