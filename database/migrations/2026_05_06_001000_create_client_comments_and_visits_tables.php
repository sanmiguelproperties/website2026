<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_comments')) {
            Schema::create('client_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')
                    ->constrained('clients')
                    ->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->text('body');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['client_id', 'created_at']);
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('client_visits')) {
            Schema::create('client_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')
                    ->constrained('clients')
                    ->cascadeOnDelete();
                $table->foreignId('property_id')
                    ->nullable()
                    ->constrained('properties')
                    ->nullOnDelete();
                $table->foreignId('assigned_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->dateTime('scheduled_at');
                $table->unsignedSmallInteger('duration_minutes')->default(60);
                $table->string('reason', 255);
                $table->string('status', 50)->default('scheduled');
                $table->string('location', 255)->nullable();
                $table->text('notes')->nullable();
                $table->text('outcome')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['client_id', 'scheduled_at']);
                $table->index(['status', 'scheduled_at']);
                $table->index('assigned_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_visits');
        Schema::dropIfExists('client_comments');
    }
};
