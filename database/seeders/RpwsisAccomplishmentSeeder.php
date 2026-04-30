<?php

namespace Database\Seeders;

use App\Models\RpwsisAccomplishment;
use Illuminate\Database\Seeder;

class RpwsisAccomplishmentSeeder extends Seeder
{
    public function run(): void
    {
        RpwsisAccomplishment::truncate();

        $rows = [
            [
                'region' => 'Region I',
                'batch' => 'Batch 1',
                'allocation' => '2026 GAA',
                'nis' => 'Agno RIS',
                'activity' => 'Watershed rehabilitation and seedling establishment',
                'remarks' => 'Ongoing implementation',
                'amount' => 250000.00,
                'c1' => 'Completed',
                'c2' => 'Completed',
                'c3' => '85%',
                'c4' => 'For delivery',
                'c5' => '70%',
                'c6' => '60%',
                'c7' => '45%',
                'c8' => '30%',
                'c9' => '2 meetings',
                'c10' => '150 copies',
                'c11' => '3 installed',
                'c12' => 'Monthly',
                'phy' => '78.50',
                'fin' => '72.10',
                'exp' => '180250.00',
            ],
            [
                'region' => 'Region I',
                'batch' => 'Batch 2',
                'allocation' => '2026 SARO',
                'nis' => 'Bued RIS',
                'activity' => 'IEC and plantation maintenance',
                'remarks' => 'Awaiting additional laborers',
                'amount' => 175000.00,
                'c1' => 'Completed',
                'c2' => 'Completed',
                'c3' => '100%',
                'c4' => 'Completed',
                'c5' => '100%',
                'c6' => '90%',
                'c7' => '100%',
                'c8' => '100%',
                'c9' => '4 meetings',
                'c10' => '220 copies',
                'c11' => '5 installed',
                'c12' => 'Bi-weekly',
                'phy' => '91.00',
                'fin' => '88.75',
                'exp' => '155312.40',
            ],
            [
                'region' => 'Region I',
                'batch' => 'Batch 1',
                'allocation' => 'Continuing',
                'nis' => 'San Fabian RIS',
                'activity' => 'Monitoring and evaluation',
                'remarks' => 'For validation of mortality rate',
                'amount' => 98000.00,
                'c1' => 'Completed',
                'c2' => 'N/A',
                'c3' => 'N/A',
                'c4' => 'N/A',
                'c5' => 'N/A',
                'c6' => 'N/A',
                'c7' => 'N/A',
                'c8' => 'N/A',
                'c9' => '1 meeting',
                'c10' => '50 copies',
                'c11' => '1 installed',
                'c12' => 'Quarterly',
                'phy' => '64.00',
                'fin' => '59.50',
                'exp' => '58310.00',
            ],
        ];

        foreach ($rows as $row) {
            RpwsisAccomplishment::create($row);
        }

        $this->command?->info('Seeded RP-WSIS accomplishment records.');
    }
}
