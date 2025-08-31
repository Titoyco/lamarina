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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('dni')->unique();
            $table->string('nombre');
            $table->integer('medio_pago_preferido')->default(0);
            $table->string('cbu', 25)->nullable();
            $table->foreignId('id_sucursal')->constrained('sucursales')->onDelete('cascade'); // Clave foránea a sucursales
            $table->foreignId('tipo_haber')->constrained('tipo_haberes')->onDelete('cascade'); // Clave foránea a tipo_haberes
            $table->decimal('saldo', 10, 0)->default(0);
            $table->double('limite')->default(0);
            $table->integer('max_cuotas')->default(3); 
            $table->boolean('suspendido')->default(false);
            $table->text('obs')->nullable()->default(null);
            $table->string('estado', 3)->nullable()->default(null);
            $table->date('fecha_estado')->nullable()->default(null);
            $table->string('direccion');
            $table->foreignId('id_localidad')->constrained('localidades');
            $table->string('email')->nullable()->default(null);
            $table->string('tel1');
            $table->string('tel2')->nullable()->default(null);
            $table->string('familiar')->nullable()->default(null);
            $table->string('tipo_familiar')->nullable()->default(null);
            $table->string('direccion_familiar')->nullable()->default(null);
            $table->foreignId('id_localidad_familiar')->nullable()->constrained('localidades')->default(null);
            $table->string('email_familiar')->nullable()->default(null);
            $table->string('tel_familiar')->nullable()->default(null);
            $table->string('trabajo')->nullable()->default(null);
            $table->string('direccion_trabajo')->nullable()->default(null);
            $table->foreignId('id_localidad_trabajo')->nullable()->constrained('localidades')->default(null);
            $table->string('tel_trabajo')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminar las claves foráneas antes de eliminar la tabla
            $table->dropForeign(['sucursal']);
            $table->dropForeign(['tipo_haber']);
            $table->dropForeign(['id_localidad']);
            $table->dropForeign(['id_localidad_familiar']);
            $table->dropForeign(['id_localidad_trabajo']);
        });

        Schema::dropIfExists('clientes');
    }
};
