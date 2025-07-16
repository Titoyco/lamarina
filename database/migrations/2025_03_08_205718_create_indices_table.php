<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indices', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Código de la terminal (T, I, U)
            $table->string('nombre'); // Nombre del indice
            $table->integer('indice');
            $table->unsignedBigInteger('sucursal_id'); // Sucursal a la que pertenece la terminal
            $table->timestamps();

            // Foreign keys
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');

        });


    }

    public function down()
    {
        Schema::dropIfExists('indices');
    }
};
