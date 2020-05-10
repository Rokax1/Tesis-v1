<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetalleUserActividadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_user_actividad', function (Blueprint $table) {
            $table->id();

            $table->foreign('fk_actividad')->references('id')->on('actividades');
            $table->foreign('fk_user_creador')->references('id')->on('users');
            $table->foreign('fk_user_responsable')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_user_actividad');
    }
}
