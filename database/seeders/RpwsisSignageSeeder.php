<?php

namespace Database\Seeders;

use App\Models\RpwsisSignage;
use Illuminate\Database\Seeder;

class RpwsisSignageSeeder extends Seeder
{
    public function run(): void
    {
        RpwsisSignage::truncate();

        $rows = [
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Lingayen',
                'barangay' => 'Domalandan',
                'x_coordinates' => '16.0217',
                'y_coordinates' => '120.2316',
                'signage_type' => 'Project Information Board',
                'nis_name' => 'Bued RIS',
                'remarks' => 'Installed near access road.',
            ],
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Calasiao',
                'barangay' => 'Nalsian',
                'x_coordinates' => '16.0091',
                'y_coordinates' => '120.3574',
                'signage_type' => 'Watershed Protection Signage',
                'nis_name' => 'San Fabian RIS',
                'remarks' => 'Visible from diversion point.',
            ],
        ];

        foreach ($rows as $row) {
            RpwsisSignage::create($row);
        }

        $this->command?->info('Seeded RP-WSIS signage records.');
    }
}
