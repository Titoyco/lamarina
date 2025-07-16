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
        Schema::create('autorizados', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('id_titular')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreignId('id_autorizado')->references('id')->on('clientes')->onDelete('cascade');
            $table->string('relación', 100); // Columna para la relación
            $table->decimal('limite', 10, 2); // Columna para el límite autorizado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('autorizados', function (Blueprint $table) {
            // Eliminar las claves foráneas antes de eliminar la tabla
            $table->dropForeign(['id_titular']);
            $table->dropForeign(['id_autorizado']);
        });

        Schema::dropIfExists('autorizados');
    }
};
