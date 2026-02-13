<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mls_configs', function (Blueprint $table) {
            // URL base para construir URLs absolutas de imágenes (agentes, etc.) cuando el API devuelve rutas relativas.
            // Ej: https://ampisanmigueldeallende.com
            $table->string('images_base_url')->nullable()->after('base_url');
        });
    }

    public function down(): void
    {
        Schema::table('mls_configs', function (Blueprint $table) {
            $table->dropColumn('images_base_url');
        });
    }
};
