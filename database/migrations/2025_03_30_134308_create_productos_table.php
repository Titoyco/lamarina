<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 255);
            $table->string('descripcion', 255);
            $table->longText('observaciones')->nullable();
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('subcategoria_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->double('stock', 15, 2)->default(0);
            $table->double('punto_compra', 15, 2)->default(0);
            $table->double('precio_compra', 15, 2)->default(0);
            $table->double('iva', 15, 2)->default(0);
            $table->double('descuento', 15, 2)->default(0);
            $table->double('ganancia', 15, 2)->default(0);
            $table->double('precio_venta', 15, 2)->default(0);
            $table->longText('Imagen')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('categoria')->references('id')->on('productos_categorias')->onDelete('cascade');
            $table->foreign('subcategoria')->references('id')->on('productos_subcategorias')->onDelete('cascade');
            $table->foreign('proveedor')->references('id')->on('proveedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}