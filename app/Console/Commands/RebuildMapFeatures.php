<?php

namespace App\Console\Commands;

use App\Http\Controllers\MapController;
use Illuminate\Console\Command;

class RebuildMapFeatures extends Command
{
    protected $signature = 'map:rebuild-features {category? : irrigated, land_boundary, potential, or the display label}';

    protected $description = 'Import uploaded map layers into the shared database-backed map feature index.';

    public function handle(MapController $mapController): int
    {
        $category = $this->argument('category');
        $this->info('Rebuilding map feature database index...');

        $result = $mapController->rebuildMapFeaturesDatabase(is_string($category) ? $category : null);

        $this->info("Imported {$result['features_imported']} feature(s) from {$result['files_imported']} file(s).");

        if (!empty($result['failed_files'])) {
            $this->warn(count($result['failed_files']) . ' file(s) failed to import:');

            foreach ($result['failed_files'] as $failedFile) {
                $this->line('- ' . ($failedFile['name'] ?? 'Unknown file') . ': ' . ($failedFile['message'] ?? 'Unknown error'));
            }
        }

        return empty($result['failed_files']) ? self::SUCCESS : self::FAILURE;
    }
}
