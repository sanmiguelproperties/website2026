<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ColorTheme;

class ColorThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Default',
                'description' => 'Tema por defecto del sistema',
                'colors' => [
                    'bg' => 'oklch(0.17 0.02 255)',
                    'surface' => 'oklch(0.21 0.02 255)',
                    'elev' => 'oklch(0.25 0.02 255)',
                    'text' => 'oklch(0.93 0.02 255)',
                    'muted' => 'oklch(0.74 0.02 255)',
                    'border' => 'oklch(0.35 0.02 255)',
                    'primary' => 'oklch(0.72 0.14 260)',
                    'primary-ink' => 'oklch(0.12 0.02 260)',
                    'accent' => 'oklch(0.75 0.13 170)',
                    'danger' => 'oklch(0.68 0.21 25)',
                ],
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Azul Oscuro',
                'description' => 'Tema con tonos azules oscuros',
                'colors' => [
                    'bg' => 'oklch(0.15 0.02 255)',
                    'surface' => 'oklch(0.18 0.02 255)',
                    'elev' => 'oklch(0.22 0.02 255)',
                    'text' => 'oklch(0.95 0.02 255)',
                    'muted' => 'oklch(0.75 0.02 255)',
                    'border' => 'oklch(0.35 0.02 255)',
                    'primary' => 'oklch(0.65 0.15 240)',
                    'primary-ink' => 'oklch(0.15 0.02 240)',
                    'accent' => 'oklch(0.75 0.14 200)',
                    'danger' => 'oklch(0.68 0.21 25)',
                ],
                'is_active' => false,
                'is_default' => false,
            ],
            [
                'name' => 'Verde Natural',
                'description' => 'Tema con tonos verdes naturales',
                'colors' => [
                    'bg' => 'oklch(0.97 0.008 120)',
                    'surface' => 'oklch(0.96 0.008 120)',
                    'elev' => 'oklch(0.94 0.01 120)',
                    'text' => 'oklch(0.25 0.02 120)',
                    'muted' => 'oklch(0.5 0.02 120)',
                    'border' => 'oklch(0.87 0.015 120)',
                    'primary' => 'oklch(0.55 0.15 140)',
                    'primary-ink' => 'oklch(0.2 0.03 140)',
                    'accent' => 'oklch(0.65 0.12 160)',
                    'danger' => 'oklch(0.65 0.18 25)',
                ],
                'is_active' => false,
                'is_default' => false,
            ],
            [
                'name' => 'Púrpura Elegante',
                'description' => 'Tema con tonos púrpuras elegantes',
                'colors' => [
                    'bg' => 'oklch(0.97 0.01 300)',
                    'surface' => 'oklch(0.96 0.01 300)',
                    'elev' => 'oklch(0.94 0.015 300)',
                    'text' => 'oklch(0.22 0.02 300)',
                    'muted' => 'oklch(0.48 0.02 300)',
                    'border' => 'oklch(0.87 0.02 300)',
                    'primary' => 'oklch(0.6 0.18 280)',
                    'primary-ink' => 'oklch(0.18 0.03 280)',
                    'accent' => 'oklch(0.7 0.16 320)',
                    'danger' => 'oklch(0.65 0.18 25)',
                ],
                'is_active' => false,
                'is_default' => false,
            ],
        ];

        foreach ($themes as $theme) {
            ColorTheme::create($theme);
        }
    }
}
