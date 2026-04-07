<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mls_offices', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('is_managed_by_us');
            $table->index('is_primary');
        });

        // Garantiza a nivel DB que solo exista UNA office principal.
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('mls_offices', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_primary_unique_key')
                    ->nullable()
                    ->storedAs('IF(is_primary = 1, 1, NULL)')
                    ->after('is_primary');

                $table->unique('is_primary_unique_key', 'mls_offices_single_primary_unique');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX mls_offices_single_primary_unique ON mls_offices ((CASE WHEN is_primary IS TRUE THEN 1 ELSE NULL END));');
        } elseif ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS mls_offices_single_primary_unique ON mls_offices(is_primary) WHERE is_primary = 1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('mls_offices', function (Blueprint $table) {
                $table->dropUnique('mls_offices_single_primary_unique');
                $table->dropColumn('is_primary_unique_key');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS mls_offices_single_primary_unique;');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS mls_offices_single_primary_unique;');
        }

        Schema::table('mls_offices', function (Blueprint $table) {
            $table->dropIndex(['is_primary']);
            $table->dropColumn('is_primary');
        });
    }
};

