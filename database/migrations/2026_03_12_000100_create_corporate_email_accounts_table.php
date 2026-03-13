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
        Schema::create('corporate_email_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('email_address');
            $table->string('from_name')->nullable();

            // IMAP (inbox sync)
            $table->string('imap_host');
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->string('imap_encryption', 20)->default('ssl'); // ssl|tls|none
            $table->boolean('imap_validate_cert')->default(false);
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable();

            // SMTP (send)
            $table->string('smtp_host');
            $table->unsignedSmallInteger('smtp_port')->default(587);
            $table->string('smtp_encryption', 20)->default('tls'); // ssl|tls|none
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();

            $table->boolean('is_active')->default(true);
            $table->dateTime('last_sync_at')->nullable();
            $table->string('last_sync_status', 50)->nullable();
            $table->text('last_sync_error')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['email_address', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_email_accounts');
    }
};
