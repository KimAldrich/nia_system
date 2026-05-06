<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckMapReprojection extends Command
{
    protected $signature = 'map:check-reprojection';

    protected $description = 'Check whether ogr2ogr/GDAL is available for cloud-side map reprojection.';

    public function handle(): int
    {
        $configuredPath = trim((string) config('services.ogr2ogr.path', ''));
        $binary = $this->resolveOgr2ogrBinary();

        $this->line('Configured OGR2OGR_PATH: ' . ($configuredPath !== '' ? $configuredPath : '(not set)'));

        if ($binary === null) {
            $this->error('ogr2ogr was not found.');
            $this->line('Laravel can still upload map files, but non-WGS84 shapefiles cannot be reprojected automatically on this server.');
            $this->line('Set OGR2OGR_PATH to an executable ogr2ogr binary if your hosting environment provides one.');

            return self::FAILURE;
        }

        $this->info("ogr2ogr found: {$binary}");

        $output = [];
        $exitCode = 0;
        @exec(escapeshellarg($binary) . ' --version 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->warn('ogr2ogr exists, but running --version failed.');
            $this->line(implode(PHP_EOL, $output));

            return self::FAILURE;
        }

        $this->line(implode(PHP_EOL, $output));

        return self::SUCCESS;
    }

    private function resolveOgr2ogrBinary(): ?string
    {
        $configuredPath = trim((string) config('services.ogr2ogr.path', ''));

        if ($configuredPath !== '') {
            return is_file($configuredPath) || is_executable($configuredPath) ? $configuredPath : null;
        }

        if (!function_exists('shell_exec')) {
            return null;
        }

        $command = PHP_OS_FAMILY === 'Windows'
            ? 'where ogr2ogr 2>NUL'
            : 'command -v ogr2ogr 2>/dev/null';

        $output = trim((string) @shell_exec($command));

        if ($output === '' && PHP_OS_FAMILY !== 'Windows') {
            $output = trim((string) @shell_exec('which ogr2ogr 2>/dev/null'));
        }

        $candidate = trim(strtok($output, "\r\n") ?: '');

        return $candidate !== '' ? $candidate : null;
    }
}
