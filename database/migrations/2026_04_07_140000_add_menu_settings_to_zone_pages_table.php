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
        Schema::table('zone_pages', function (Blueprint $table): void {
            $table->boolean('show_in_menu')->default(true);
            $table->unsignedInteger('menu_order')->nullable();

            $table->index(['show_in_menu']);
            $table->index(['menu_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zone_pages', function (Blueprint $table): void {
            $table->dropIndex(['show_in_menu']);
            $table->dropIndex(['menu_order']);

            $table->dropColumn(['show_in_menu', 'menu_order']);
        });
    }
};
