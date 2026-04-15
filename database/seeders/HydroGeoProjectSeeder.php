<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HydroGeoProject;

class HydroGeoProjectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Look for the CSV file inside the database/seeders folder
        $filePath = database_path('seeders/hydro_summary.csv');

        if (!file_exists($filePath)) {
            $this->command->error("Could not find hydro_summary.csv in the database/seeders folder!");
            return;
        }

        $csvFile = fopen($filePath, 'r');
        $firstline = true;

        // 2. Loop through every single row in the CSV
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            // Skip the first row (the Header titles like YEAR, DISTRICT, etc.)
            if ($firstline) {
                $firstline = false;
                continue;
            }

            // Skip empty rows at the bottom of the excel file
            if (empty($data[3])) {
                continue;
            }

            // 3. Insert the row into the database
            HydroGeoProject::create([
                'year' => $data[0] ?? null,
                'district' => $data[1] ?? null,
                'project_code' => $data[2] ?? null,
                'system_name' => $data[3] ?? null,
                'description' => $data[4] ?? null,
                'municipality' => $data[5] ?? null,
                'status' => $data[6] ?? null,
                'result' => $data[7] ?? null,
            ]);
        }

        fclose($csvFile);

        $this->command->info('Successfully imported all 35 projects from the CSV!');
    }
}