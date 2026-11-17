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
        Schema::table('cajas_sesiones', function (Blueprint $table) {
            $table->json('resumen_fiscal')->nullable()->after('observaciones_cierre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas_sesiones', function (Blueprint $table) {
            $table->dropColumn('resumen_fiscal');
        });
    }
};
