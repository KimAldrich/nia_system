<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use ZipArchive;

class MapController extends Controller
{
    private const IRRIGATED_CHART_CACHE_KEY = 'map.irrigated_chart_data.v2';
    private const MUNICIPALITY_DETAILS_PATH = 'maps/details.json';
    private const IRRIGATED_DIRECTORY = 'maps/irrigated';
    private const POTENTIAL_DIRECTORY = 'maps/potential';
    private const IRRIGATED_AREA_FIELDS = ['area', 'area (ha)', 'calculated', 'declared_a', 'ia'];

    private const CATEGORY_DIRECTORY_MAP = [
        'Irrigated Area' => 'irrigated',
        'Pangasinan Land Boundary' => 'land_boundary',
        'Potential Irrigable Area' => 'potential',
    ];

    private const PRIMARY_FILE_EXTENSIONS = ['geojson', 'json', 'kml', 'kmz', 'zip', 'shp'];

    private const PRIMARY_FILE_PRIORITY = [
        'geojson' => 1,
        'json' => 2,
        'kmz' => 3,
        'kml' => 4,
        'zip' => 5,
        'shp' => 6,
    ];

    private const SHAPEFILE_COMPANION_EXTENSIONS = ['shp', 'shx', 'dbf', 'prj', 'cpg'];

    public function Showmap()
    {
        $overlayGroups = [
            'irrigated' => $this->buildOverlayGroup('Irrigated Area', 'irrigated'),
            'land_boundary' => $this->buildOverlayGroup('Pangasinan Land Boundary', 'land_boundary'),
            'potential' => $this->buildOverlayGroup('Potential Irrigable Area', 'potential'),
        ];
        $uploadTargets = $this->buildUploadTargets();

        return view('map.map', compact('overlayGroups', 'uploadTargets'));
    }

    private function buildOverlayGroup(string $label, string $directory): array
    {
        $disk = Storage::disk('public');
        $folder = $this->resolveOverlayFolder($label, $directory);

        if (!$disk->exists($folder)) {
            return [
                'label' => $label,
                'files' => [],
            ];
        }

        $files = collect($disk->allFiles($folder))
            ->filter(function ($path) use ($disk) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                if (!in_array($extension, self::PRIMARY_FILE_EXTENSIONS, true)) {
                    return false;
                }

                if ($extension !== 'shp') {
                    return true;
                }

                $basePath = substr($path, 0, -4);

                return $disk->exists($basePath . '.dbf') && $disk->exists($basePath . '.shx');
            })
            ->groupBy(function ($path) {
                return strtolower(dirname($path) . '|' . pathinfo($path, PATHINFO_FILENAME));
            })
            ->map(function ($paths) {
                return collect($paths)->sortBy(function ($path) {
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                    return self::PRIMARY_FILE_PRIORITY[$extension] ?? 999;
                })->first();
            })
            ->map(function ($path) use ($folder) {
                return [
                    'name' => basename($path),
                    'url' => Storage::url($path),
                    'folder' => $this->relativeOverlayFolder($path, $folder),
                ];
            })
            ->sortBy([
                ['folder', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();

        return [
            'label' => $label,
            'files' => $files,
        ];
    }

    private function resolveOverlayFolder(string $label, string $directory): string
    {
        $disk = Storage::disk('public');
        $canonicalFolder = "maps/{$directory}";
        $legacyFolder = "maps/{$label}";

        if ($disk->exists($canonicalFolder)) {
            return $canonicalFolder;
        }

        return $legacyFolder;
    }

    private function buildUploadTargets(): array
    {
        $targets = [];

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $root = $this->resolveOverlayFolder($category, $directory);
            $entries = [['value' => '', 'label' => 'Category root']];

            if (Storage::disk('public')->exists($root)) {
                $directories = collect(Storage::disk('public')->allDirectories($root))
                    ->map(fn ($path) => trim($this->relativeOverlayFolder($path, $root), '/'))
                    ->filter(fn ($path) => $path !== '')
                    ->unique()
                    ->sort()
                    ->values();

                foreach ($directories as $path) {
                    $entries[] = [
                        'value' => $path,
                        'label' => $path,
                    ];
                }
            }

            $targets[$category] = $entries;
        }

        return $targets;
    }

    private function relativeOverlayFolder(string $path, string $root): string
    {
        $prefix = rtrim($root, '/') . '/';
        $relativePath = str_starts_with($path, $prefix) ? substr($path, strlen($prefix)) : $path;
        $relativeFolder = dirname($relativePath);

        return $relativeFolder === '.' ? '' : $relativeFolder;
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|in:Irrigated Area,Pangasinan Land Boundary,Potential Irrigable Area',
                'files' => 'required',
                'files.*' => 'file|max:51200',
                'target_folder' => 'nullable|string|max:255',
            ]);

            $category = $request->category;
            $categoryDirectory = self::CATEGORY_DIRECTORY_MAP[$category] ?? $category;
            $paths = $request->input('paths', []);
            $targetFolder = $this->sanitizeRelativeFolder($request->input('target_folder', ''));
            $baseStoragePath = trim("maps/{$categoryDirectory}/{$targetFolder}", '/');
            $uploadedFiles = [];
            $shapefileBasenames = [];

            foreach ($request->file('files') as $index => $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $relativePath = $paths[$index] ?? $file->getClientOriginalName();
                $folderPath = $this->extractUploadSubfolder($relativePath);

                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = strtolower($file->getClientOriginalExtension());
                $safeBaseName = $this->sanitizeFileBaseName($baseName);
                $storagePath = trim($baseStoragePath . '/' . $folderPath, '/');
                $basenameKey = strtolower(trim($storagePath . '/' . $baseName, '/'));

                if (in_array($extension, self::SHAPEFILE_COMPANION_EXTENSIONS, true)) {
                    if (!isset($shapefileBasenames[$basenameKey])) {
                        $shapefileBasenames[$basenameKey] = $this->resolveAvailableBaseName(
                            $storagePath,
                            $safeBaseName,
                            self::SHAPEFILE_COMPANION_EXTENSIONS
                        );
                    }

                    $finalName = $shapefileBasenames[$basenameKey] . '.' . $extension;
                } else {
                    $finalName = $this->resolveAvailableFileName($storagePath, $safeBaseName, $extension);
                }

                Storage::disk('public')->makeDirectory($storagePath);

                $path = Storage::disk('public')->putFileAs($storagePath, $file, $finalName);

                if ($path) {
                    $uploadedFiles[] = [
                        'name' => $finalName,
                        'path' => $path,
                        'url' => Storage::url($path),
                    ];
                }
            }

            $this->clearMapDataCache();

            return response()->json([
                'message' => 'Upload successful',
                'files' => $uploadedFiles,
                'target_folder' => $targetFolder,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fileManager()
    {
        $filesData = [];

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $folder = $this->resolveOverlayFolder($category, $directory);

            if (!Storage::disk('public')->exists($folder)) {
                continue;
            }

            $files = collect(Storage::disk('public')->allFiles($folder));

            foreach ($files as $file) {
                $filesData[] = [
                    'name' => basename($file),
                    'category' => $category,
                    'url' => Storage::url($file),
                    'path' => $file,
                    'folder' => dirname($file),
                ];
            }
        }

        return view('map.files', compact('filesData'));
    }

    public function deleteFile(Request $request)
    {
        $path = $request->path;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            $this->clearMapDataCache();

            return response()->json([
                'message' => 'Deleted',
            ]);
        }

        return response()->json([
            'message' => 'File not found',
        ], 404);
    }

    private function readShapefileZip($zipPath)
    {
        $fullPath = storage_path('app/public/' . $zipPath);

        if (!file_exists($fullPath)) {
            return 0;
        }

        $extractPath = storage_path('app/temp/' . uniqid());
        mkdir($extractPath, 0777, true);

        $zip = new ZipArchive;

        if ($zip->open($fullPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return 0;
        }

        $shpFile = null;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'shp') {
                $shpFile = $file->getPathname();
                break;
            }
        }

        if (!$shpFile) {
            return 0;
        }

        try {
            $reader = new ShapefileReader($shpFile);
            $reader->setCharset('CP1252');

            $totalArea = 0;

            while ($record = $reader->fetchRecord()) {
                if ($record->isDeleted()) {
                    continue;
                }

                $data = $record->getDataArray();
                $area = 0;

                foreach ($data as $key => $value) {
                    $cleanKey = strtoupper(trim($key));

                    if ($cleanKey === 'AREA__HA_' || str_contains($cleanKey, 'AREA')) {
                        $area = (float) str_replace(',', '', $value);
                        break;
                    }
                }

                $totalArea += $area;
            }

            return round($totalArea, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getIrrigatedChartData()
    {
        return response()->json(Cache::store('file')->remember(self::IRRIGATED_CHART_CACHE_KEY, now()->addHour(), function () {
            $municipalityDetails = $this->getMunicipalityDetailIndex();

            if (empty($municipalityDetails)) {
                return [
                    'error' => 'details.json not found or invalid',
                ];
            }

            $irrigatedStats = $this->collectIrrigatedAreas($municipalityDetails);
            $piaStats = $this->collectPotentialAreas($municipalityDetails);
            $chartData = [];

            foreach ($municipalityDetails as $normalizedName => $detail) {
                $name = $detail['name'];
                $totalLand = (float) ($detail['total_land_area_ha'] ?? 0);
                $piaArea = round((float) ($piaStats[$normalizedName]['pia_area'] ?? 0), 2);
                $computedIrrigatedArea = round((float) ($irrigatedStats[$normalizedName]['irrigated_area'] ?? 0), 2);
                $fallbackIrrigatedArea = round((float) ($detail['area_developed_ha'] ?? 0), 2);
                $irrigatedArea = $computedIrrigatedArea;
                $irrigatedAreaSource = 'dbf';

                if (
                    ($irrigatedArea <= 0 && $fallbackIrrigatedArea > 0)
                    || ($totalLand > 0 && $irrigatedArea > $totalLand)
                ) {
                    $irrigatedArea = $fallbackIrrigatedArea;
                    $irrigatedAreaSource = 'details_json';
                }

                $remainingArea = round($piaArea - $irrigatedArea, 2);

                $chartData[$name] = [
                    'name' => $name,
                    'total_land_area_ha' => round($totalLand, 2),
                    'pia_area' => $piaArea,
                    'irrigated_area' => $irrigatedArea,
                    'irrigated_area_source' => $irrigatedAreaSource,
                    'remaining_area' => $remainingArea,
                    'dbf_file_count' => (int) ($irrigatedStats[$normalizedName]['dbf_file_count'] ?? 0),
                    'source_files' => array_values($irrigatedStats[$normalizedName]['source_files'] ?? []),
                    'ranges' => [
                        'PIA' => $piaArea,
                        'Irrigated Area' => $irrigatedArea,
                        'Remaining Area' => $remainingArea,
                    ],
                ];
            }

            return $chartData;
        }));
    }

    private function getMunicipalityDetailIndex(): array
    {
        $jsonPath = public_path(self::MUNICIPALITY_DETAILS_PATH);

        if (!file_exists($jsonPath)) {
            return [];
        }

        $rows = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($rows)) {
            return [];
        }

        $indexed = [];

        foreach ($rows as $row) {
            if (empty($row['name'])) {
                continue;
            }

            $indexed[$this->normalizeMunicipalityName($row['name'])] = $row;
        }

        return $indexed;
    }

    private function collectIrrigatedAreas(array $municipalityDetails): array
    {
        return $this->collectAreasFromDbfDirectory(
            public_path('storage/' . self::IRRIGATED_DIRECTORY),
            $municipalityDetails,
            function (string $path, array $record) use ($municipalityDetails): ?string {
                if (str_contains(strtolower($path), 'potential ia')) {
                    return null;
                }

                return $this->guessMunicipalityFromPath($path, $municipalityDetails);
            },
            function (array $record): float {
                return $this->extractIrrigatedAreaValue($record);
            }
        );
    }

    private function collectPotentialAreas(array $municipalityDetails): array
    {
        return $this->collectAreasFromDbfDirectory(
            public_path('storage/' . self::POTENTIAL_DIRECTORY),
            $municipalityDetails,
            function (string $path, array $record) use ($municipalityDetails): ?string {
                $layerName = (string) ($record['layer'] ?? $record['name'] ?? '');

                return $this->guessMunicipalityFromText($layerName . ' ' . $path, $municipalityDetails);
            },
            function (array $record): float {
                return $this->extractNumericValue($record['area (ha)'] ?? $record['area'] ?? 0);
            },
            'pia_area'
        );
    }

    private function collectAreasFromDbfDirectory(
        string $directory,
        array $municipalityDetails,
        callable $municipalityResolver,
        callable $areaResolver,
        string $areaKey = 'irrigated_area'
    ): array {
        if (!is_dir($directory)) {
            return [];
        }

        $this->registerXBaseAutoloader();
        $aggregated = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'dbf') {
                continue;
            }

            $filePath = $file->getPathname();
            $shapeReader = null;

            try {
                $reader = new \XBase\TableReader($filePath, ['encoding' => 'CP1252']);
            } catch (\Throwable $exception) {
                continue;
            }

            $columns = array_keys($reader->getColumns());
            $useGeometryFallback = !$this->hasAreaLikeColumns($columns);

            $shapePath = preg_replace('/\.dbf$/i', '.shp', $filePath);

            if ($useGeometryFallback && is_string($shapePath) && file_exists($shapePath)) {
                try {
                    $shapeReader = new ShapefileReader($shapePath, [
                        Shapefile::OPTION_POLYGON_CLOSED_RINGS_ACTION => Shapefile::ACTION_FORCE,
                    ]);
                    $shapeReader->setCharset('CP1252');
                } catch (\Throwable $exception) {
                    $shapeReader = null;
                }
            }

            $fileMunicipality = $municipalityResolver($filePath, []);
            $fileRegistered = false;

            while ($record = $reader->nextRecord()) {
                $recordData = [];

                foreach (array_keys($reader->getColumns()) as $column) {
                    $recordData[$column] = $record->get($column);
                }

                $shapeRecord = $this->fetchShapeRecordSafely($shapeReader);

                $municipality = $municipalityResolver($filePath, $recordData) ?? $fileMunicipality;

                if (!$municipality) {
                    continue;
                }

                $normalizedMunicipality = $this->normalizeMunicipalityName($municipality);

                if (!isset($municipalityDetails[$normalizedMunicipality])) {
                    continue;
                }

                if (!isset($aggregated[$normalizedMunicipality])) {
                    $aggregated[$normalizedMunicipality] = [
                        $areaKey => 0,
                        'dbf_file_count' => 0,
                        'source_files' => [],
                    ];
                }

                if (!$fileRegistered) {
                    $aggregated[$normalizedMunicipality]['dbf_file_count']++;
                    $aggregated[$normalizedMunicipality]['source_files'][] = $this->toRelativeStoragePath($filePath);
                    $fileRegistered = true;
                }

                $resolvedArea = $areaResolver($recordData);

                if ($useGeometryFallback && $resolvedArea <= 0) {
                    $resolvedArea = $this->extractAreaFromGeometryRecord($shapeRecord);
                }

                $aggregated[$normalizedMunicipality][$areaKey] += $resolvedArea;
            }

            $reader->close();
        }

        foreach ($aggregated as &$values) {
            $values[$areaKey] = round((float) $values[$areaKey], 2);
            $values['source_files'] = array_values(array_unique($values['source_files']));
        }

        return $aggregated;
    }

    private function extractIrrigatedAreaValue(array $record): float
    {
        foreach (self::IRRIGATED_AREA_FIELDS as $field) {
            $value = $this->extractNumericValue($record[$field] ?? null);

            if ($value > 0) {
                if ($field === 'calculated') {
                    return $value / 10000;
                }

                return $value;
            }
        }

        return $this->extractAreaFromText((string) ($record['name'] ?? ''));
    }

    private function extractNumericValue($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return 0;
        }

        $cleaned = trim(str_replace(',', '', $value));

        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }

    private function extractAreaFromText(string $text): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*hectare/i', $text, $matches)) {
            return (float) $matches[1];
        }

        return 0;
    }

    private function extractAreaFromGeometryRecord($shapeRecord): float
    {
        if (!$shapeRecord || $shapeRecord->isDeleted()) {
            return 0;
        }

        try {
            $geometry = json_decode($shapeRecord->getGeoJSON(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return 0;
        }

        if (!is_array($geometry) || !isset($geometry['type'], $geometry['coordinates'])) {
            return 0;
        }

        $geometryType = (string) $geometry['type'];

        if (str_starts_with($geometryType, 'MultiPolygon')) {
            $geometry['type'] = 'MultiPolygon';
        } elseif (str_starts_with($geometryType, 'Polygon')) {
            $geometry['type'] = 'Polygon';
        } else {
            return 0;
        }

        return round($this->calculateGeoJsonArea($geometry), 2);
    }

    private function fetchShapeRecordSafely(?ShapefileReader &$shapeReader)
    {
        if (!$shapeReader) {
            return null;
        }

        try {
            return $shapeReader->fetchRecord();
        } catch (\Throwable $exception) {
            $shapeReader = null;

            return null;
        }
    }

    private function hasAreaLikeColumns(array $columns): bool
    {
        foreach ($columns as $column) {
            $normalizedColumn = strtolower(trim((string) $column));

            if (
                str_contains($normalizedColumn, 'area')
                || in_array($normalizedColumn, self::IRRIGATED_AREA_FIELDS, true)
            ) {
                return true;
            }
        }

        return false;
    }

    private function guessMunicipalityFromText(string $text, array $municipalityDetails): ?string
    {
        $normalizedText = $this->normalizeMunicipalityName(str_replace(DIRECTORY_SEPARATOR, ' ', $text));
        $matches = [];

        foreach ($municipalityDetails as $normalizedName => $detail) {
            if (str_contains($normalizedText, $normalizedName)) {
                $matches[$normalizedName] = $detail['name'];
            }
        }

        if (empty($matches)) {
            return null;
        }

        uksort($matches, function ($left, $right) {
            return strlen($right) <=> strlen($left);
        });

        return array_values($matches)[0];
    }

    private function guessMunicipalityFromPath(string $path, array $municipalityDetails): ?string
    {
        $segments = preg_split('/[\/\\\\]+/', $path) ?: [];
        $matches = [];

        foreach ($segments as $segment) {
            $normalizedSegment = $this->normalizeMunicipalityName($segment);

            if ($normalizedSegment === '') {
                continue;
            }

            foreach ($municipalityDetails as $normalizedName => $detail) {
                if (
                    $normalizedSegment === $normalizedName
                    || str_starts_with($normalizedSegment, $normalizedName . ' ')
                ) {
                    $matches[$normalizedName] = $detail['name'];
                }
            }
        }

        if (empty($matches)) {
            return null;
        }

        uksort($matches, function ($left, $right) {
            return strlen($right) <=> strlen($left);
        });

        return array_values($matches)[0];
    }

    private function normalizeMunicipalityName(string $name): string
    {
        $normalized = strtolower($name);
        $normalized = preg_replace('/\bcity of\b/', '', $normalized);
        $normalized = preg_replace('/\bmunicipality of\b/', '', $normalized);
        $normalized = preg_replace('/\bcity\b/', '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized);

        return trim(preg_replace('/\s+/', ' ', $normalized));
    }

    private function registerXBaseAutoloader(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        spl_autoload_register(function (string $class): void {
            $prefix = 'XBase\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $path = app_path('Libraries/XBase/' . str_replace('\\', '/', $relativeClass) . '.php');

            if (file_exists($path)) {
                require_once $path;
            }
        });

        $registered = true;
    }

    private function toRelativeStoragePath(string $path): string
    {
        $storageRoot = str_replace('\\', '/', public_path('storage')) . '/';
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with($normalizedPath, $storageRoot)) {
            return substr($normalizedPath, strlen($storageRoot));
        }

        return $normalizedPath;
    }

    private function clearMapDataCache(): void
    {
        Cache::store('file')->forget(self::IRRIGATED_CHART_CACHE_KEY);
    }

    private function sanitizeRelativeFolder(?string $folder): string
    {
        $folder = str_replace('\\', '/', (string) $folder);
        $folder = preg_replace('#/+#', '/', $folder);
        $folder = trim($folder, '/');

        if ($folder === '' || $folder === '.') {
            return '';
        }

        $parts = array_filter(explode('/', $folder), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        return implode('/', $parts);
    }

    private function extractUploadSubfolder(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $folder = $this->sanitizeRelativeFolder(dirname($relativePath));

        return $folder === '.' ? '' : $folder;
    }

    private function sanitizeFileBaseName(string $name): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);

        return trim($safe, '_') ?: 'file';
    }

    private function resolveAvailableBaseName(string $storagePath, string $baseName, array $extensions): string
    {
        $disk = Storage::disk('public');
        $candidate = $baseName;
        $counter = 1;

        while ($this->baseNameExists($disk, $storagePath, $candidate, $extensions)) {
            $candidate = $baseName . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function baseNameExists($disk, string $storagePath, string $baseName, array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if ($disk->exists(trim($storagePath . '/' . $baseName . '.' . $extension, '/'))) {
                return true;
            }
        }

        return false;
    }

    private function resolveAvailableFileName(string $storagePath, string $baseName, string $extension): string
    {
        $disk = Storage::disk('public');
        $candidate = $baseName . '.' . $extension;
        $counter = 1;

        while ($disk->exists(trim($storagePath . '/' . $candidate, '/'))) {
            $candidate = $baseName . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $candidate;
    }

    private function getMunicipalityLandArea($municipality)
    {
        $jsonPath = public_path('maps/municipalities.json');

        if (!file_exists($jsonPath)) {
            return 0;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (!$data) {
            return 0;
        }

        foreach ($data as $item) {
            $jsonName = strtolower(trim($item['name']));
            $clickedName = strtolower(trim($municipality));

            $jsonName = str_replace(' city', '', $jsonName);
            $clickedName = str_replace(' city', '', $clickedName);

            if ($jsonName === $clickedName) {
                return (float) $item['total_land_area_ha'];
            }
        }

        return 0;
    }

    private function calculateGeoJsonArea($geometry)
    {
        $type = $geometry['type'];
        $coords = $geometry['coordinates'];
        $totalArea = 0;

        if ($type === 'Polygon') {
            $totalArea += $this->polygonArea($coords[0]);
        }

        if ($type === 'MultiPolygon') {
            foreach ($coords as $polygon) {
                $totalArea += $this->polygonArea($polygon[0]);
            }
        }

        return $totalArea / 10000;
    }

    private function polygonArea($ring)
    {
        $area = 0;
        $points = count($ring);

        for ($i = 0; $i < $points - 1; $i++) {
            $x1 = $ring[$i][0];
            $y1 = $ring[$i][1];
            $x2 = $ring[$i + 1][0];
            $y2 = $ring[$i + 1][1];

            $area += ($x1 * $y2) - ($x2 * $y1);
        }

        return abs($area) * 111319.9 * 111319.9 / 2;
    }
}
