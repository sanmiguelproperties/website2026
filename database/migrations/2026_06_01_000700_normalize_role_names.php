<?php

use App\Services\RoleNameNormalizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(RoleNameNormalizer::class)->normalizeExistingRoles();
    }

    public function down(): void
    {
        // La capitalizacion original y los duplicados fusionados no se pueden reconstruir.
    }
};
