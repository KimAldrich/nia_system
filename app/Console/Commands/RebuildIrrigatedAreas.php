<?php

namespace App\Console\Commands;

use App\Http\Controllers\MapController;
use Illuminate\Console\Command;

class RebuildIrrigatedAreas extends Command
{
    protected $signature = 'map:rebuild-irrigated-areas';

    protected $description = 'Import irrigated map uploads into the database-backed bbox index.';

    public function handle(MapController $mapController): int
    {
        $this->info('Rebuilding irrigated area database index...');

        $result = $mapController->rebuildIrrigatedAreasDatabase();

        $this->info("Imported {$result['features_imported']} feature(s) from {$result['files_imported']} file(s).");

        if (!empty($result['failed_files'])) {
            $this->warn(count($result['failed_files']) . ' file(s) failed to import:');

            foreach ($result['failed_files'] as $failedFile) {
                $this->line('- ' . ($failedFile['name'] ?? 'Unknown file') . ': ' . ($failedFile['message'] ?? 'Unknown error'));
            }
        }

        return self::SUCCESS;
    }
}
