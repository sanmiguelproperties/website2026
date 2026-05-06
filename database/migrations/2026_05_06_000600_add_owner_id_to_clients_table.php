<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('property_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('clients', 'owner_id')) {
            DB::table('clients')
                ->whereNull('owner_id')
                ->whereNotNull('contact_request_id')
                ->orderBy('id')
                ->select(['id', 'contact_request_id'])
                ->chunkById(100, function ($clients): void {
                    foreach ($clients as $client) {
                        $ownerId = DB::table('contact_requests')
                            ->where('id', $client->contact_request_id)
                            ->value('owner_id');

                        if ($ownerId) {
                            DB::table('clients')
                                ->where('id', $client->id)
                                ->update(['owner_id' => $ownerId]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }
        });
    }
};
