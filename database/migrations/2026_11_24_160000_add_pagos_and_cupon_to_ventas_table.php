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
            if (!Schema::hasColumn('ventas', 'pagos')) {
                $table->json('pagos')->nullable()->after('id_medio_pago');
            }
            if (!Schema::hasColumn('ventas', 'numero_cupon')) {
                $table->string('numero_cupon', 100)->nullable()->after('pagos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'numero_cupon')) {
                $table->dropColumn('numero_cupon');
            }
            if (Schema::hasColumn('ventas', 'pagos')) {
                $table->dropColumn('pagos');
            }
        });
    }
};
