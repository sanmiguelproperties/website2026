<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Sol Peruano',
                'code' => 'PEN',
                'symbol' => 'S/',
                'exchange_rate' => 1.000000,
                'is_base' => true,
            ],
            [
                'name' => 'Dólar Estadounidense',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 0.266667,
                'is_base' => false,
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 0.226667,
                'is_base' => false,
            ],
            [
                'name' => 'Libra Esterlina',
                'code' => 'GBP',
                'symbol' => '£',
                'exchange_rate' => 0.194667,
                'is_base' => false,
            ],
            [
                'name' => 'Yen Japonés',
                'code' => 'JPY',
                'symbol' => '¥',
                'exchange_rate' => 29.333333,
                'is_base' => false,
            ],
            [
                'name' => 'Peso Mexicano',
                'code' => 'MXN',
                'symbol' => '$',
                'exchange_rate' => 20.000000,
                'is_base' => false,
            ],
            [
                'name' => 'Real Brasileño',
                'code' => 'BRL',
                'symbol' => 'R$',
                'exchange_rate' => 5.200000,
                'is_base' => false,
            ],
            [
                'name' => 'Peso Colombiano',
                'code' => 'COP',
                'symbol' => '$',
                'exchange_rate' => 3800.000000,
                'is_base' => false,
            ],
            [
                'name' => 'Peso Chileno',
                'code' => 'CLP',
                'symbol' => '$',
                'exchange_rate' => 850.000000,
                'is_base' => false,
            ],
            [
                'name' => 'Peso Argentino',
                'code' => 'ARS',
                'symbol' => '$',
                'exchange_rate' => 900.000000,
                'is_base' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::create($currency);
        }
    }
}