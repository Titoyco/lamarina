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
        Schema::create('puntos_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->restrictOnDelete();
            $table->unsignedInteger('numero');
            $table->string('nombre', 100);
            $table->enum('tipo_emision', ['fiscal_hasar', 'arca_wsfe']);
            $table->json('configuracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_sucursal', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_venta');
    }
};

