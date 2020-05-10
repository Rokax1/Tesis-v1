<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActividadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->integer('fk_area_actividad')->unsigned();
            $table->string('titulo');
            $table->string('archivo');
            $table->string('estado_actividad');
            $table->string('estado_confirmacion_creador');
            $table->timestamps();

            $table->foreign('fk_area_actividad')->references('id')->on('area_actividad');      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actividades');
    }
}
