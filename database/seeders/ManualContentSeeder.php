<?php

namespace Database\Seeders;

use App\Support\ManualContent;
use Illuminate\Database\Seeder;

class ManualContentSeeder extends Seeder
{
    public function run(): void
    {
        ManualContent::seedDefaults();
    }
}
