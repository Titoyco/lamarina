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
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('id_punto_venta')
                ->nullable()
                ->after('id_caja_sesion')
                ->constrained('puntos_venta')
                ->nullOnDelete();

            $table->enum('tipo_emision', ['fiscal_hasar', 'arca_wsfe'])
                ->nullable()
                ->after('id_punto_venta');

            $table->string('cae', 14)->nullable()->after('numero_factura');
            $table->date('cae_vencimiento')->nullable()->after('cae');
            $table->text('afip_qr')->nullable()->after('cae_vencimiento');
            $table->json('datos_fiscales')->nullable()->after('afip_qr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_punto_venta']);
            $table->dropColumn([
                'id_punto_venta',
                'tipo_emision',
                'cae',
                'cae_vencimiento',
                'afip_qr',
                'datos_fiscales',
            ]);
        });
    }
};

