<?php

namespace Database\Seeders;

use App\Models\PcrStatusReport;
use Illuminate\Database\Seeder;

class PcrStatusReportSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/pcr_status_report.csv');

        if (!file_exists($filePath)) {
            $this->command->error("Could not find pcr_status_report.csv in the database/seeders folder!");
            return;
        }

        PcrStatusReport::truncate();

        $csvFile = fopen($filePath, 'r');
        $firstLine = true;
        $count = 0;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== false) {
            $data = array_map(function ($value) {
                return mb_convert_encoding((string) $value, 'UTF-8', 'ISO-8859-1, Windows-1252, auto');
            }, $data);

            if ($firstLine) {
                $firstLine = false;
                continue;
            }

            if (empty(trim($data[0] ?? ''))) {
                continue;
            }

            PcrStatusReport::create([
                'fund_source' => trim($data[0] ?? ''),
                'no_of_contracts' => (int) preg_replace('/[^\d-]/', '', (string) ($data[1] ?? 0)),
                'allocation' => (float) str_replace(',', '', (string) ($data[2] ?? 0)),
                'no_of_pcr_prepared' => (int) preg_replace('/[^\d-]/', '', (string) ($data[3] ?? 0)),
                'no_of_pcr_submitted_to_regional_office' => (int) preg_replace('/[^\d-]/', '', (string) ($data[4] ?? 0)),
                'accomplishment_percentage' => (float) str_replace('%', '', (string) ($data[5] ?? 0)),
                'for_signing_of_ia_chief_dm_rm' => (int) preg_replace('/[^\d-]/', '', (string) ($data[6] ?? 0)),
                'for_submission_to_ro1' => (int) preg_replace('/[^\d-]/', '', (string) ($data[7] ?? 0)),
                'not_yet_prepared_pending_details' => (int) preg_replace('/[^\d-]/', '', (string) ($data[8] ?? 0)),
            ]);

            $count++;
        }

        fclose($csvFile);

        $this->command->info("Successfully imported {$count} PCR status rows from pcr_status_report.csv.");
    }
}
