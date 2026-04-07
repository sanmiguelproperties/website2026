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
        Schema::table('agencies', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('email');
        });

        // Garantizar a nivel DB que solo exista una agencia principal.
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedTinyInteger('primary_unique_key')
                    ->nullable()
                    ->storedAs('IF(is_primary = 1, 1, NULL)')
                    ->after('is_primary');

                $table->unique('primary_unique_key', 'agencies_single_primary_unique');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX agencies_single_primary_unique ON agencies ((CASE WHEN is_primary IS TRUE THEN 1 ELSE NULL END));');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropUnique('agencies_single_primary_unique');
                $table->dropColumn('primary_unique_key');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS agencies_single_primary_unique;');
        }

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
