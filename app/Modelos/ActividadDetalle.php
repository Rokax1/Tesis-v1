<?php

namespace App\Modelos;
use App\Modelos\Actividades;

use Illuminate\Database\Eloquent\Model;

class ActividadDetalle extends Model
{
    
    protected $table='actividaddetalle';
    protected $primaryKey = 'id_actividad_detalle';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_actividad','id_usuario','estado_actividad_creador', 'estado_actividad_encargado'
    ];


/*
    public function actividades (){
        return $this->belongsTo('App\Modelos\Actividades','id_actividad');
    }
*/
    public function usuario (){
        return $this->belongsTo('App\User','id_usuario');
    }

    public function actividades()
    {
        return $this->hasMany('App\Modelos\Actividades','id_actividad');
    }
    
    /*
    public function area(){
        return $this->belongsTo('App\Modelos\AreaActividad','fk_area_actividad');
    }
*/
}
