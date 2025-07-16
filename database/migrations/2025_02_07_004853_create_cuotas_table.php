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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_credito')->constrained()->onDelete('cascade'); // Relación con id_credito de la tabla creditos
            $table->integer('cuota'); // Número de la cuota
            $table->decimal('intereses', 10, 2); // Intereses aplicados a la cuota
            $table->decimal('entrego', 10, 2)->nullable(); // Monto entregado, puede ser nulo
            $table->decimal('a_pagar', 10, 2); // Monto a pagar
            $table->integer('presentacion'); // presentación de la cuota
            $table->datetime('fecha_cobro')->nullable(); // Fecha de cobro, puede ser nula
            $table->string('medio_pago')->nullable(); // Medio de pago, puede ser nulo
            $table->string('banco_envio')->nullable(); // Banco de envío, puede ser nulo
            $table->timestamps(); // Campos created_at y updated_at
            $table->unique(['id_credito', 'cuota']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuotas');
    }
};
