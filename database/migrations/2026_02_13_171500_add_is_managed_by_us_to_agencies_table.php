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
        Schema::table('agencies', function (Blueprint $table) {
            // Agencia "a nuestro cargo" (solo editable manualmente desde el dashboard)
            $table->boolean('is_managed_by_us')->default(false)->after('raw_payload');
            $table->index('is_managed_by_us');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropIndex(['is_managed_by_us']);
            $table->dropColumn('is_managed_by_us');
        });
    }
};

