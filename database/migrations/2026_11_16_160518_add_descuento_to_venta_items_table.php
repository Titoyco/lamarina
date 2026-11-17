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
        Schema::table('venta_items', function (Blueprint $table) {
            if (!Schema::hasColumn('venta_items', 'descuento_porcentaje')) {
                $table->decimal('descuento_porcentaje', 5, 2)->default(0.00)->after('precio_unitario');
            }
            if (!Schema::hasColumn('venta_items', 'descuento')) {
                $table->decimal('descuento', 12, 2)->default(0.00)->after('descuento_porcentaje');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            if (Schema::hasColumn('venta_items', 'descuento')) {
                $table->dropColumn('descuento');
            }
            if (Schema::hasColumn('venta_items', 'descuento_porcentaje')) {
                $table->dropColumn('descuento_porcentaje');
            }
        });
    }
};
