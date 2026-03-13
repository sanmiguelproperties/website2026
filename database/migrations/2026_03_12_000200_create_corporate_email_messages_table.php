<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('corporate_email_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('corporate_email_account_id')
                ->constrained('corporate_email_accounts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('direction', 20); // incoming|outgoing
            $table->string('folder', 100)->nullable();

            $table->string('external_uid', 191)->nullable();
            $table->string('external_message_id', 191)->nullable();

            $table->string('subject')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();

            $table->json('to_recipients')->nullable();
            $table->json('cc_recipients')->nullable();
            $table->json('bcc_recipients')->nullable();

            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('raw_headers')->nullable();

            $table->string('status', 50)->default('unread');
            $table->dateTime('received_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('read_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['corporate_email_account_id', 'direction'], 'cem_acc_dir_idx');
            $table->index(['status', 'created_at'], 'cem_status_created_idx');
            $table->index(['received_at'], 'cem_received_idx');
            $table->index(['sent_at'], 'cem_sent_idx');

            $table->unique(
                ['corporate_email_account_id', 'direction', 'external_uid'],
                'cem_acc_dir_uid_unq'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_email_messages');
    }
};
