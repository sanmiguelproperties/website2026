<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega FKs para relacionar Office ↔ Agents ↔ Properties.
     *
     * - properties.mls_office_id  → mls_offices.mls_office_id
     * - mls_agents.mls_office_id  → mls_offices.mls_office_id
     */
    public function up(): void
    {
        // Backfill: si ya existen propiedades/agentes con mls_office_id,
        // debemos crear placeholders en mls_offices para poder aplicar el FK.
        // (En ambientes con datos previos, esto evita el error 1452 al agregar la constraint).
        if (Schema::hasTable('mls_offices')) {
            $officeIds = collect();

            if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'mls_office_id')) {
                $officeIds = $officeIds->merge(
                    DB::table('properties')
                        ->whereNotNull('mls_office_id')
                        ->distinct()
                        ->pluck('mls_office_id')
                );
            }

            if (Schema::hasTable('mls_agents') && Schema::hasColumn('mls_agents', 'mls_office_id')) {
                $officeIds = $officeIds->merge(
                    DB::table('mls_agents')
                        ->whereNotNull('mls_office_id')
                        ->distinct()
                        ->pluck('mls_office_id')
                );
            }

            $officeIds = $officeIds
                ->filter(fn($v) => $v !== null && $v !== '')
                ->map(fn($v) => (int) $v)
                ->unique()
                ->values();

            if ($officeIds->isNotEmpty()) {
                $now = now();
                $officeIds->chunk(500)->each(function ($chunk) use ($now) {
                    $rows = $chunk->map(fn($id) => [
                        'mls_office_id' => $id,
                        'name' => "MLS Office #{$id}",
                        'paid' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('mls_offices')->insertOrIgnore($rows);
                });
            }
        }

        Schema::table('properties', function (Blueprint $table) {
            // Puede existir ya la columna; solo agregamos el FK.
            $table->foreign('mls_office_id', 'properties_mls_office_fk')
                ->references('mls_office_id')
                ->on('mls_offices')
                ->nullOnDelete();
        });

        Schema::table('mls_agents', function (Blueprint $table) {
            $table->foreign('mls_office_id', 'mls_agents_mls_office_fk')
                ->references('mls_office_id')
                ->on('mls_offices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign('properties_mls_office_fk');
        });

        Schema::table('mls_agents', function (Blueprint $table) {
            $table->dropForeign('mls_agents_mls_office_fk');
        });
    }
};

