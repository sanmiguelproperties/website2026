<?php

use App\Support\ManualContent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ManualContent::seedDefaults(true);
    }

    public function down(): void
    {
        //
    }
};
