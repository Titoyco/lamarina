<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->enum('tipo', [
                'ingreso',      // ingreso por compra
                'venta',        // salida por venta
                'baja',         // baja por descarte
                'correccion',   // ajuste por faltante o diferencia
                'traslado',     // traslado entre sucursales
                'devolucion',   // devolución de venta o compra
            ]);
            $table->morphs('referencia');
            $table->integer('cantidad'); // puede ser negativo en correccion/baja/traslado
            $table->decimal('precio_compra', 12, 2)->nullable();
            $table->decimal('precio_venta', 12, 2)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sucursal_origen_id')->nullable()->constrained('sucursales')->nullOnDelete(); // Para traslados
            $table->decimal('stock_actual', 12, 2)->nullable(); // Este campo se puede calcular dinámicamente
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_movimientos');
    }
}