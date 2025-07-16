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
        Schema::create('tipo_credito', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del tipo de crédito
            $table->string('codigo')->unique(); // Código de la terminal (T, I, U)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_credito');
    }
};
