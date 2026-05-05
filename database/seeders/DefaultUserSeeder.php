<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $guards = ['web', 'api'];

        if (
            !Role::where('name', 'super-admin')->where('guard_name', 'web')->exists()
            || !Role::where('name', 'super-admin')->where('guard_name', 'api')->exists()
        ) {
            $this->call(RbacSeeder::class);
        }

        $defaultAdminPassword = '852456357';

        $defaultAdminUsers = [
            // existentes
            [
                'email' => 'gusgusnoriega@gmail.com',
                'name'  => 'Gustavo Noriega',
            ],
            [
                'email' => 'atencion@britishhouseinternational.net',
                'name'  => 'Admin Atencion',
            ],
            [
                'email' => 'izamar_lucely@hotmail.com',
                'name'  => 'Izamar Lucely',
            ],

            // solicitados
            [
                'email' => 'admin@controldecierresinmobiliarios.com',
                'name'  => 'Admin Control de Cierres Inmobiliarios',
            ],
            [
                'email' => 'alex@smarternotharder.consulting',
                'name'  => 'Alex (Smarter Not Harder)',
            ],
            [
                'email' => 'sanmiguelpropertiesmkt@gmail.com',
                'name'  => 'San Miguel Properties MKT',
            ],
            [
                'email' => 'erwitr@gmail.com',
                'name'  => 'Erwitr',
            ],
        ];

        foreach ($defaultAdminUsers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make($defaultAdminPassword),
                    'email_verified_at' => now(),
                ]
            );

            // Assign full internal access for both guards.
            foreach ($guards as $guard) {
                $role = Role::findByName('super-admin', $guard);
                $user->assignRole($role);
            }
        }
    }
}
