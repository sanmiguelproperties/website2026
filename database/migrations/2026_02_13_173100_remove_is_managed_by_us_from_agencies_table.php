<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El flag "is_managed_by_us" fue agregado por error a agencies (EasyBroker).
     * La intención real es usarlo en mls_offices (agencias del MLS/LMS).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('agencies', 'is_managed_by_us')) {
            return;
        }

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropIndex(['is_managed_by_us']);
            $table->dropColumn('is_managed_by_us');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('agencies', 'is_managed_by_us')) {
            return;
        }

        Schema::table('agencies', function (Blueprint $table) {
            $table->boolean('is_managed_by_us')->default(false)->after('raw_payload');
            $table->index('is_managed_by_us');
        });
    }
};

