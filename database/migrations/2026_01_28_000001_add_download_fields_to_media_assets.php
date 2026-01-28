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
        Schema::table('media_assets', function (Blueprint $table) {
            $table->char('checksum', 32)->nullable()->after('size_bytes')
                ->comment('Checksum MD5 del archivo');
            $table->timestamp('downloaded_at')->nullable()->after('created_at')
                ->comment('Fecha de descarga del archivo remoto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropColumn(['checksum', 'downloaded_at']);
        });
    }
};
