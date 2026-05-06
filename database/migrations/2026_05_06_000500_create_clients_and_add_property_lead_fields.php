<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_request_id')
                    ->nullable()
                    ->constrained('contact_requests')
                    ->nullOnDelete();
                $table->foreignId('property_id')
                    ->nullable()
                    ->constrained('properties')
                    ->nullOnDelete();
                $table->foreignId('mls_agent_id')
                    ->nullable()
                    ->constrained('mls_agents')
                    ->nullOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('source', 100)->default('property_form');
                $table->string('status', 50)->default('active');
                $table->text('notes')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('email');
                $table->index('phone');
                $table->index('source');
                $table->index('status');
            });
        }

        Schema::table('contact_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_requests', 'mls_agent_id')) {
                $table->foreignId('mls_agent_id')
                    ->nullable()
                    ->after('owner_id')
                    ->constrained('mls_agents')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('contact_requests', 'converted_client_id')) {
                $table->foreignId('converted_client_id')
                    ->nullable()
                    ->after('mls_agent_id')
                    ->constrained('clients')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('contact_requests', 'converted_at')) {
                $table->dateTime('converted_at')->nullable()->after('converted_client_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            if (Schema::hasColumn('contact_requests', 'converted_client_id')) {
                $table->dropConstrainedForeignId('converted_client_id');
            }

            if (Schema::hasColumn('contact_requests', 'mls_agent_id')) {
                $table->dropConstrainedForeignId('mls_agent_id');
            }

            if (Schema::hasColumn('contact_requests', 'converted_at')) {
                $table->dropColumn('converted_at');
            }
        });

        Schema::dropIfExists('clients');
    }
};
