<?php

namespace Database\Seeders;

use App\Models\RpwsisInfrastructure;
use Illuminate\Database\Seeder;

class RpwsisInfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        RpwsisInfrastructure::truncate();

        $rows = [
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Sison',
                'barangay' => 'Artacho',
                'x_coordinates' => '16.1712',
                'y_coordinates' => '120.5165',
                'infrastructure_type' => 'Bunk House',
                'nis_name' => 'Agno RIS',
                'remarks' => 'Used by caretakers during maintenance works.',
            ],
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Pozorrubio',
                'barangay' => 'Cablong',
                'x_coordinates' => '16.1118',
                'y_coordinates' => '120.5450',
                'infrastructure_type' => 'Small Water Impounding Tank',
                'nis_name' => 'Bued RIS',
                'remarks' => 'Supports seedling watering in dry months.',
            ],
        ];

        foreach ($rows as $row) {
            RpwsisInfrastructure::create($row);
        }

        $this->command?->info('Seeded RP-WSIS infrastructure records.');
    }
}
