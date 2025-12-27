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
        Schema::table('users', function (Blueprint $table) {
            // Asociación (opcional) del usuario-agente a una agencia.
            $table->unsignedBigInteger('agency_id')->nullable()->after('id');
            $table->index('agency_id');

            // Perfil “público” del agente (solo se usa si el usuario tiene rol `agent`).
            $table->string('agent_phone', 50)->nullable();
            $table->string('agent_public_email')->nullable();
            $table->text('agent_bio')->nullable();

            // Foto opcional específica para el perfil de agente.
            $table->foreignId('agent_profile_media_asset_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();

            // Identificador del agente en EasyBroker (ej: edi_3).
            $table->string('easybroker_agent_id', 50)->nullable()->unique();
            $table->json('easybroker_agent_payload')->nullable();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropForeign(['agent_profile_media_asset_id']);

            $table->dropUnique(['easybroker_agent_id']);
            $table->dropIndex(['agency_id']);

            $table->dropColumn([
                'agency_id',
                'agent_phone',
                'agent_public_email',
                'agent_bio',
                'agent_profile_media_asset_id',
                'easybroker_agent_id',
                'easybroker_agent_payload',
            ]);
        });
    }
};

