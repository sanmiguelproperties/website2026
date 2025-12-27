<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;

class PassportKeysSeeder extends Seeder
{
    public function run()
    {
        // Verificar si las claves ya existen (Passport guarda en storage/oauth-private.key y storage/oauth-public.key)
        if (file_exists(storage_path('oauth-private.key')) && file_exists(storage_path('oauth-public.key'))) {
            $this->command->info('Las claves RSA de Passport ya existen.');
        } else {
            // Generar claves usando el comando Artisan de Passport
            try {
                Artisan::call('passport:keys');
                $this->command->info('Claves RSA de Passport generadas exitosamente.');
            } catch (\Exception $e) {
                $this->command->error('Error al generar las claves RSA de Passport: ' . $e->getMessage());
            }
        }

        // Verificar si ya existe un cliente de acceso personal
        $personalClientExists = DB::table('oauth_clients')
            ->whereJsonContains('grant_types', 'personal_access')
            ->exists();

        if ($personalClientExists) {
            $this->command->info('El cliente de acceso personal ya existe.');
        } else {
            // Crear cliente de acceso personal usando el comando Artisan
            try {
                Artisan::call('passport:client --personal --no-interaction');
                $this->command->info('Cliente de acceso personal creado exitosamente.');
            } catch (\Exception $e) {
                $this->command->error('Error al crear el cliente de acceso personal: ' . $e->getMessage());
            }
        }
    }
}