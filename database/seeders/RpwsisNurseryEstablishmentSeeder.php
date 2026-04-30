<?php

namespace Database\Seeders;

use App\Models\RpwsisNurseryEstablishment;
use Illuminate\Database\Seeder;

class RpwsisNurseryEstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        RpwsisNurseryEstablishment::truncate();

        $rows = [
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Umingan',
                'barangay' => 'Buenavista',
                'x_coordinates' => '15.9304',
                'y_coordinates' => '120.8431',
                'seedlings_produced' => '4500',
                'nursery_type' => 'Central Nursery',
                'nis_name' => 'Agno RIS',
                'remarks' => 'Operational for 2026 planting season.',
            ],
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Manaoag',
                'barangay' => 'Pugaro',
                'x_coordinates' => '16.0420',
                'y_coordinates' => '120.4868',
                'seedlings_produced' => '1800',
                'nursery_type' => 'Satellite Nursery',
                'nis_name' => 'Bued RIS',
                'remarks' => 'Needs additional seed trays.',
            ],
        ];

        foreach ($rows as $row) {
            RpwsisNurseryEstablishment::create($row);
        }

        $this->command?->info('Seeded RP-WSIS nursery establishment records.');
    }
}
