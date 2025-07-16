<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->integer('legajo');
            $table->string('nombre');
            $table->string('direccion', 255);
            $table->foreignId('id_localidad')->constrained('localidades'); // Clave foránea a localidades
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->foreignId('id_iva')->constrained('condiciones_iva'); // Clave foránea
            $table->bigInteger('cuit');
            $table->string('email')->nullable();
            $table->double('Saldo', 15, 2)->default(0);
            $table->string('web')->nullable();
            $table->longText('comentario')->nullable();
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
        Schema::table('proveedores', function (Blueprint $table) {
            // Eliminar las claves foráneas antes de eliminar la tabla
            $table->dropForeign(['id_localidad']);
            $table->dropForeign(['id_iva']);
        });
        Schema::dropIfExists('proveedores');
    }
}