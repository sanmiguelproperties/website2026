<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('contact_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_requests', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('property_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('contact_requests', 'assignment_status')) {
                $table->string('assignment_status', 50)
                    ->default('pending_assignment')
                    ->after('status')
                    ->index();
            }

            if (!Schema::hasColumn('contact_requests', 'assigned_at')) {
                $table->dateTime('assigned_at')->nullable()->after('assignment_status');
            }

            if (!Schema::hasColumn('contact_requests', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            if (Schema::hasColumn('contact_requests', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }

            if (Schema::hasColumn('contact_requests', 'assignment_status')) {
                $table->dropColumn('assignment_status');
            }

            if (Schema::hasColumn('contact_requests', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }

            if (Schema::hasColumn('contact_requests', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
