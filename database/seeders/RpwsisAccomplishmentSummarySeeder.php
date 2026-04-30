<?php

namespace Database\Seeders;

use App\Models\RpwsisAccomplishmentSummary;
use Illuminate\Database\Seeder;

class RpwsisAccomplishmentSummarySeeder extends Seeder
{
    public function run(): void
    {
        RpwsisAccomplishmentSummary::truncate();

        $rows = [
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'San Fabian',
                'barangay' => 'Bolasi',
                'plantation_type' => 'Riverbank Stabilization',
                'year_established' => '2024',
                'target_area_1' => '2.50 ha',
                'area_planted' => '2.10 ha',
                'species_planted' => 'Bamboo - 800 hills; Narra - 120 seedlings',
                'spacing' => '3m x 3m',
                'maintenance' => 'Quarterly maintenance',
                'target_area_2' => '0.40 ha',
                'actual_area' => '0.35 ha',
                'mortality_rate' => '8%',
                'species_replanted' => 'Bamboo - 60 hills',
                'nis_name' => 'San Fabian RIS',
                'remarks' => 'Good survival rate.',
            ],
            [
                'region' => 'Region I',
                'province' => 'Pangasinan',
                'municipality' => 'Bayambang',
                'barangay' => 'Bani',
                'plantation_type' => 'Watershed Rehabilitation',
                'year_established' => '2025',
                'target_area_1' => '3.00 ha',
                'area_planted' => '2.60 ha',
                'species_planted' => 'Mahogany - 500 seedlings; Acacia - 350 seedlings',
                'spacing' => '2m x 2m',
                'maintenance' => 'Monthly clearing',
                'target_area_2' => '0.60 ha',
                'actual_area' => '0.50 ha',
                'mortality_rate' => '12%',
                'species_replanted' => 'Mahogany - 40 seedlings',
                'nis_name' => 'Agno RIS',
                'remarks' => 'Portion affected by flooding.',
            ],
        ];

        foreach ($rows as $row) {
            RpwsisAccomplishmentSummary::create($row);
        }

        $this->command?->info('Seeded RP-WSIS accomplishment summary records.');
    }
}
