<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcurementProject;

class ProcurementProjectSeeder extends Seeder
{
    public function run(): void
    {
        // 🌟 FIX: It will now look for multiple possible file names!
        $possibleFiles = [
            database_path('seeders/procurement_report.csv'),
            database_path('seeders/Procurement Status as of March 25, 2026.csv'),
        ];

        $filePath = null;
        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                $filePath = $file;
                break;
            }
        }

        // If it STILL can't find it, it will loudly warn you!
        if (!$filePath) {
            $this->command->error("❌ ERROR: Could not find the CSV file!");
            $this->command->line("Please make sure you placed your Excel file inside the 'database/seeders/' folder and name it 'procurement_report.csv'.");
            return;
        }

        $this->command->info("✅ Found CSV file: " . basename($filePath));
        $this->command->info("⏳ Seeding Procurement Data...");

        ProcurementProject::truncate();
        $csvFile = fopen($filePath, 'r');
        $currentCategory = 'Uncategorized';
        $count = 0;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            // Sanitize encoding to prevent crash
            $data = array_map(function ($value) {
                return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1, Windows-1252, auto');
            }, $data);

            $col0 = trim($data[0] ?? '');
            $col1 = trim($data[1] ?? '');
            $col2 = trim($data[2] ?? '');

            // Stop condition (If we hit the footer signatures)
            if (str_contains(strtoupper($col1), 'PREPARED BY'))
                break;

            // Skip blanks and TOTAL headers
            if ((empty($col0) && empty($col1)) || str_contains(strtoupper($col1), 'TOTAL'))
                continue;

            // Detect Categories vs Sub-Projects
            // Categories have a Number and Name, but NO Municipality
            if (is_numeric($col0) && empty($col2)) {
                $currentCategory = $col1;
                continue;
            }

            // Projects have a Number AND a Municipality
            if (is_numeric($col0) && !empty($col2)) {
                ProcurementProject::create([
                    'category' => $currentCategory,
                    'proj_no' => $col0,
                    'name_of_project' => $col1,
                    'municipality' => $col2,
                    'allocation' => trim($data[3] ?? ''),
                    'abc' => trim($data[4] ?? ''),
                    'bid_out' => trim($data[5] ?? ''),
                    'for_bidding' => trim($data[6] ?? ''),
                    'date_of_bidding' => trim($data[7] ?? ''),
                    'awarded' => trim($data[8] ?? ''),
                    'date_of_award' => trim($data[9] ?? ''),
                    'contract_no' => trim($data[10] ?? ''),
                    'contract_amount' => trim($data[11] ?? ''),
                    'name_of_contractor' => trim($data[12] ?? ''),
                    'remarks' => trim($data[13] ?? ''),
                    'project_description' => trim($data[14] ?? ''),
                ]);
                $count++;
            }
        }

        fclose($csvFile);

        // 🌟 FIX: It will now output the success message with the row count!
        $this->command->info("🎉 SUCCESS! Uploaded {$count} Procurement projects into the database!");
    }
}