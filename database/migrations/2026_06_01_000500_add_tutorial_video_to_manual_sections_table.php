<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_sections', function (Blueprint $table): void {
            $table->foreignId('tutorial_video_id')
                ->nullable()
                ->after('required_permission')
                ->constrained('tutorial_videos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('manual_sections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tutorial_video_id');
        });
    }
};
