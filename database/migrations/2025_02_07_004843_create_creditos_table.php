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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cliente')->constrained()->onDelete('cascade'); // Relación con la tabla clientes
            $table->foreignId('id_venta')->nullable()->constrained()->onDelete('cascade'); // Relación con la tabla ventas
            $table->integer('credito'); // numero que identifica el credito (este id sumado a la terminal deben ser unicos)
            $table->string('codigo_sucursal', 2); // Terminal del crédito
            $table->string('tipo_credito', 2); // Tipo de venta
            $table->decimal('importe', 10, 2); // Importe total del crédito
            $table->decimal('valor_cuota', 10, 2); // Valor de cada cuota
            $table->integer('cantidad_cuotas'); // Cantidad total de cuotas
            $table->foreignId('id_autorizado')->constrained()->onDelete('cascade'); // Relación con la tabla de usuarios autorizados
            $table->dateTime('fecha_credito'); // Fecha de la compra
            $table->dateTime('fecha_cancelacion')->nullable(); // Fecha de cancelación, puede ser nula
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique(['credito', 'codigo_sucursal', 'tipo_credito']); // Combinación única
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditos');
    }
};