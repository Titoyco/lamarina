<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('presentaciones', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('numero')->unique(); // Número de la presentación
        $table->date('fecha_inicio'); // Fecha de inicio de la presentación
        $table->boolean('actual')->default(false); // Indicador de presentación actual
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentaciones');
    }
};
