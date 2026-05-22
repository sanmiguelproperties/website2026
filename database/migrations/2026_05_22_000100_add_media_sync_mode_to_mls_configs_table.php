<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mls_configs', function (Blueprint $table) {
            $table->string('media_sync_mode', 20)
                ->default('download')
                ->after('images_base_url');
        });
    }

    public function down(): void
    {
        Schema::table('mls_configs', function (Blueprint $table) {
            $table->dropColumn('media_sync_mode');
        });
    }
};
