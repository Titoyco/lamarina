<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 255);
            $table->string('descripcion', 255);
            $table->longText('observaciones')->nullable();
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('subcategoria_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->longText('Imagen')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete column (baja)

            // Foreign keys
            $table->foreign('categoria_id')->references('id')->on('productos_categorias')->onDelete('cascade');
            $table->foreign('subcategoria_id')->references('id')->on('productos_subcategorias')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
