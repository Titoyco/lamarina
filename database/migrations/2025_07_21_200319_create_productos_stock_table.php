<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up():void
{
    Schema::create('productos_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->double('stock', 15, 2)->default(0);
            $table->double('punto_compra', 15, 2)->default(0);
            $table->double('precio_venta', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->unique(['producto_id', 'sucursal_id']);
    });
}
public function down():void
{
    Schema::dropIfExists('productos_stock');
}
};
