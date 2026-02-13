<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El API del MLS AMPI identifica propiedades con:
     * - `id` (ID interno numérico)
     * - `mls_id` (ID público string, ej: "SMA-1200")
     *
     * En nuestra BD:
     * - properties.mls_id        = ID interno numérico (`id` en el API)
     * - properties.mls_public_id = ID público string (`mls_id` en el API)
     *
     * Para evitar duplicados (y no depender de agency_id como parte de la unicidad),
     * agregamos índices únicos directos para MLS.
     */
    public function up(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        $driver = DB::getDriverName();

        $indexExists = function (string $indexName) use ($driver): bool {
            // En sqlite no existe information_schema; asumimos que no existe para
            // permitir que el Schema builder maneje el error si aplica.
            if ($driver === 'sqlite') {
                return false;
            }

            try {
                $row = DB::selectOne(
                    "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'properties' AND index_name = ? LIMIT 1",
                    [$indexName]
                );
                return (bool) $row;
            } catch (\Throwable $e) {
                return false;
            }
        };

        // 1) Remover índice heredado que amarraba MLS a agency_id.
        if ($indexExists('properties_agency_mls_unique')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropUnique('properties_agency_mls_unique');
            });
        }

        // 2) Agregar índices únicos directos.
        // Nota: en MySQL, UNIQUE permite múltiples NULL, lo cual es correcto para
        // registros no-MLS (manual / easybroker) donde mls_id/mls_public_id son null.
        if (!$indexExists('properties_mls_id_unique')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unique('mls_id', 'properties_mls_id_unique');
            });
        }

        if (!$indexExists('properties_mls_public_id_unique')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unique('mls_public_id', 'properties_mls_public_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        // Revertir únicos creados.
        Schema::table('properties', function (Blueprint $table) {
            // dropUnique acepta nombre de índice
            try {
                $table->dropUnique('properties_mls_id_unique');
            } catch (\Throwable $e) {
                // noop
            }

            try {
                $table->dropUnique('properties_mls_public_id_unique');
            } catch (\Throwable $e) {
                // noop
            }
        });

        // Intentar restaurar el índice original (agency_id + mls_public_id)
        // si ambos campos existen.
        if (Schema::hasColumn('properties', 'agency_id') && Schema::hasColumn('properties', 'mls_public_id')) {
            Schema::table('properties', function (Blueprint $table) {
                try {
                    $table->unique(['agency_id', 'mls_public_id'], 'properties_agency_mls_unique');
                } catch (\Throwable $e) {
                    // noop
                }
            });
        }
    }
};

