<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_notes')) {
            return;
        }

        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_request_id')
                ->constrained('contact_requests')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('body');
            $table->string('note_type', 50)->default('internal');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['contact_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
    }
};
