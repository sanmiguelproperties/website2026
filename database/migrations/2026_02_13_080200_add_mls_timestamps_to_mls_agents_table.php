<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * En Swagger, los recursos típicamente incluyen timestamps (created_at/updated_at).
     *
     * Para agentes guardamos `last_synced_at`, pero también es útil persistir
     * los timestamps del MLS (si están presentes en payloads reales) para
     * trazabilidad/auditoría.
     */
    public function up(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('mls_agents', 'mls_created_at')) {
                $table->dateTime('mls_created_at')->nullable()->after('is_active');
                $table->index('mls_created_at');
            }

            if (!Schema::hasColumn('mls_agents', 'mls_updated_at')) {
                $table->dateTime('mls_updated_at')->nullable()->after('mls_created_at');
                $table->index('mls_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            if (Schema::hasColumn('mls_agents', 'mls_created_at')) {
                $table->dropIndex(['mls_created_at']);
                $table->dropColumn('mls_created_at');
            }

            if (Schema::hasColumn('mls_agents', 'mls_updated_at')) {
                $table->dropIndex(['mls_updated_at']);
                $table->dropColumn('mls_updated_at');
            }
        });
    }
};

