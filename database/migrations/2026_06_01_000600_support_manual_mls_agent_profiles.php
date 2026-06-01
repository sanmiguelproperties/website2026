<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('mls_agent_id')->nullable()->change();
            $table->boolean('is_manual')->default(false)->after('mls_agent_id')->index();
        });

        DB::table('mls_agents')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id')
            ->each(function ($userId): void {
                $linkedIds = DB::table('mls_agents')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->pluck('id');

                DB::table('mls_agents')
                    ->whereIn('id', $linkedIds->slice(1))
                    ->update(['user_id' => null]);
            });

        Schema::table('mls_agents', function (Blueprint $table) {
            $table->unique('user_id', 'mls_agents_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mls_agents', function (Blueprint $table) {
            $table->dropUnique('mls_agents_user_id_unique');
            $table->dropIndex(['is_manual']);
            $table->dropColumn('is_manual');
        });

        if (! DB::table('mls_agents')->whereNull('mls_agent_id')->exists()) {
            Schema::table('mls_agents', function (Blueprint $table) {
                $table->unsignedBigInteger('mls_agent_id')->nullable(false)->change();
            });
        }
    }
};
