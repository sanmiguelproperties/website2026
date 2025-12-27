<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CleanUploadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Borrar todo el contenido del directorio uploads
        Storage::disk('public')->deleteDirectory('uploads');

        // Recrear el directorio vacío
        Storage::disk('public')->makeDirectory('uploads');

        $this->command->info('Todos los archivos subidos han sido eliminados y el directorio uploads ha sido recreado.');
    }
}