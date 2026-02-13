<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos observados en el payload real del MLS para agentes.
     *
     * Basado en:
     * - GET /api/v1/agents
     * - GET /api/v1/agent/{id}
     * - GET /api/v1/offices/{id}/agents
     */
    public function up(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            $table->string('fax', 50)->nullable()->after('mobile');
            $table->text('address')->nullable()->after('fax');
            $table->string('state_province', 100)->nullable()->after('address');
            $table->string('city', 100)->nullable()->after('state_province');

            $table->string('facebook')->nullable()->after('website');
            $table->string('instagram')->nullable()->after('facebook');
            $table->string('x_twitter')->nullable()->after('instagram');
            $table->string('tiktok')->nullable()->after('x_twitter');
            $table->string('youtube')->nullable()->after('tiktok');
            $table->string('pinterest')->nullable()->after('youtube');
            $table->string('linkedin')->nullable()->after('pinterest');

            // Bio en español (el API expone biography_es)
            $table->text('bio_es')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            $table->dropColumn([
                'fax',
                'address',
                'state_province',
                'city',
                'facebook',
                'instagram',
                'x_twitter',
                'tiktok',
                'youtube',
                'pinterest',
                'linkedin',
                'bio_es',
            ]);
        });
    }
};

