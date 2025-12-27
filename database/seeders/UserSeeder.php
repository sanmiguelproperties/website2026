<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'gusgusnoriega@gmail.com',
        ], [
            'name' => 'Gus Gus Noriega',
            'password' => Hash::make('12345678'),
        ]);

        User::firstOrCreate([
            'email' => 'prueba1@example.com',
        ], [
            'name' => 'Usuario Prueba 1',
            'password' => Hash::make('12345678'),
        ]);

        User::firstOrCreate([
            'email' => 'prueba2@example.com',
        ], [
            'name' => 'Usuario Prueba 2',
            'password' => Hash::make('12345678'),
        ]);
    }
}
