<?php

namespace Database\Seeders;

use App\Models\PaoPowData;
use Illuminate\Database\Seeder;

class PaoPowDataSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/pao_pow_report.csv');

        if (!file_exists($filePath)) {
            $this->command->error("Could not find pao_pow_report.csv in the database/seeders folder!");
            return;
        }

        PaoPowData::truncate();

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

            PaoPowData::create([
                'district' => trim($data[0] ?? ''),
                'no_of_projects' => (int) preg_replace('/[^\d-]/', '', (string) ($data[1] ?? 0)),
                'total_allocation' => (float) str_replace(',', '', (string) ($data[2] ?? 0)),
                'no_of_plans_received' => (int) preg_replace('/[^\d-]/', '', (string) ($data[3] ?? 0)),
                'no_of_project_estimate_received' => (int) preg_replace('/[^\d-]/', '', (string) ($data[4] ?? 0)),
                'pow_received' => (int) preg_replace('/[^\d-]/', '', (string) ($data[5] ?? 0)),
                'pow_approved' => (int) preg_replace('/[^\d-]/', '', (string) ($data[6] ?? 0)),
                'pow_submitted' => (int) preg_replace('/[^\d-]/', '', (string) ($data[7] ?? 0)),
                'ongoing_pow_preparation' => (int) preg_replace('/[^\d-]/', '', (string) ($data[8] ?? 0)),
                'pow_for_submission' => (int) preg_replace('/[^\d-]/', '', (string) ($data[9] ?? 0)),
                'remarks' => trim($data[10] ?? ''),
            ]);

            $count++;
        }

        fclose($csvFile);

        $this->command->info("Successfully imported {$count} PAO Program of Works rows from pao_pow_report.csv.");
    }
}
