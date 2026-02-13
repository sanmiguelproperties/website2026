<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega un flag manual para marcar las agencias/oficinas del MLS
     * que están a nuestro cargo.
     *
     * IMPORTANTE:
     * - Debe default=false.
     * - La sincronización desde el MLS NO debe modificar este campo.
     */
    public function up(): void
    {
        Schema::table('mls_offices', function (Blueprint $table) {
            $table->boolean('is_managed_by_us')
                ->default(false)
                ->after('paid');

            $table->index('is_managed_by_us');
        });
    }

    public function down(): void
    {
        Schema::table('mls_offices', function (Blueprint $table) {
            $table->dropIndex(['is_managed_by_us']);
            $table->dropColumn('is_managed_by_us');
        });
    }
};

