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
        Schema::create('agencies', function (Blueprint $table) {
            // Usamos el id de EasyBroker como PK (no autoincrement).
            $table->unsignedBigInteger('id')->primary();

            $table->string('name');
            $table->string('account_owner')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};

