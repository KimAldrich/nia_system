<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FsdeProject;

class FsdeProjectSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/fsde_report.csv');

        if (!file_exists($filePath)) {
            $this->command->error("Could not find fsde_report.csv in database/seeders folder!");
            return;
        }

        // Wipe the old incomplete data
        FsdeProject::truncate();

        $csvFile = fopen($filePath, 'r');
        $currentYear = null;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            // 🌟 FIX 1: Convert every cell to UTF-8 to prevent database crashes on letters like 'Ñ' 🌟
            $data = array_map(function ($value) {
                return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1, Windows-1252, auto');
            }, $data);

            if (!empty(trim($data[0])) && is_numeric(trim($data[0]))) {
                $currentYear = trim($data[0]);
            }

            $projectName = trim($data[1] ?? '');

            // 🌟 FIX 2: Stop the entire seeder if we hit the footer/signatures! 🌟
            if (str_contains($projectName, 'Prepared by') || str_contains($projectName, 'ENGR.')) {
                break;
            }

            if (
                empty($projectName) ||
                $projectName == 'Proposed Project Name' ||
                $projectName == 'Feasibility Study and Detailed Engineering' ||
                str_contains($projectName, 'Summary')
            ) {
                continue;
            }

            FsdeProject::create([
                'year' => $currentYear,
                'project_name' => $projectName,
                'municipality' => trim($data[2] ?? ''),
                'type_of_study' => trim($data[3] ?? ''),
                'budget' => trim($data[5] ?? ''),
                'consultant' => trim($data[6] ?? ''),
                'period_start' => trim($data[7] ?? ''),
                'period_end' => trim($data[8] ?? ''),
                'contract_amount' => trim($data[9] ?? ''),
                'actual_obligation' => trim($data[10] ?? ''),
                'value_of_acc' => trim($data[11] ?? ''),
                'actual_expenditures' => trim($data[12] ?? ''),
                'jan_phy' => trim($data[13] ?? ''),
                'jan_fin' => trim($data[14] ?? ''),
                'feb_phy' => trim($data[15] ?? ''),
                'feb_fin' => trim($data[16] ?? ''),
                'remarks' => trim($data[17] ?? ''),
            ]);
        }

        fclose($csvFile);
        $this->command->info('Successfully imported FULL FSDE rows (Signatures ignored successfully)!');
    }
}