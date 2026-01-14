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
        Schema::table('frontend_color_settings', function (Blueprint $table) {
            $table->string('view_slug', 50)->default('global')->after('description');
            $table->index('view_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frontend_color_settings', function (Blueprint $table) {
            $table->dropIndex(['view_slug']);
            $table->dropColumn('view_slug');
        });
    }
};
