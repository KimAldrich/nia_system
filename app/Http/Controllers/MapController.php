<?php

namespace App\Http\Controllers;

use App\Services\SystemNotificationService;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use ZipArchive;

class MapController extends Controller
{
    private const IRRIGATED_CHART_CACHE_KEY_PREFIX = 'map.irrigated_chart_data.v7.';
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
    private const MAP_NOTIFICATION_FILE = 'map_notifications.json';
    private const MAP_NOTIFICATION_LIMIT = 200;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

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
                    'url' => $this->mapFileUrl($path),
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
                'files.*' => 'file|extensions:geojson,json,kml,kmz,zip,shp,shx,dbf,prj,cpg|max:51200',
                'target_folder' => 'nullable|string|max:255',
            ], [
                'files.required' => 'Please select at least one supported map file to upload.',
                'files.*.file' => 'Only supported map files may be uploaded.',
                'files.*.extensions' => 'Unsupported file detected. Please upload only: .geojson, .json, .kml, .kmz, .zip, .shp, .shx, .dbf, .prj, or .cpg.',
                'files.*.max' => 'Each uploaded file must be 50MB or smaller.',
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
                        'url' => $this->mapFileUrl($path),
                    ];
                }
            }

            $this->clearMapDataCache();
            $this->notifyUploadedMapFiles($request, $category, $uploadedFiles, $targetFolder);
            $notificationResult = $this->notifyMapFileChange(
                'upload',
                $category,
                $uploadedFiles
            );

            return response()->json([
                'message' => 'Upload successful. ' . $notificationResult['admin_message'],
                'files' => $uploadedFiles,
                'target_folder' => $targetFolder,
                'notified_users_count' => $notificationResult['notified_users_count'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first() ?: 'Upload failed. Please check the selected files and try again.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fileManager()
    {
        $filesData = [];
        $foldersData = [];

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $folder = $this->resolveOverlayFolder($category, $directory);

            if (!Storage::disk('public')->exists($folder)) {
                continue;
            }

            $files = collect(Storage::disk('public')->allFiles($folder));
            $folderCounts = [];

            foreach ($files as $file) {
                $fileFolder = dirname($file);
                $folderCounts[$fileFolder] = ($folderCounts[$fileFolder] ?? 0) + 1;

                $filesData[] = [
                    'name' => basename($file),
                    'category' => $category,
                    'url' => $this->mapFileUrl($file),
                    'path' => $file,
                    'folder' => $fileFolder,
                ];
            }

            foreach ($folderCounts as $fileFolder => $count) {
                if ($fileFolder === $folder) {
                    continue;
                }

                $foldersData[] = [
                    'category' => $category,
                    'path' => $fileFolder,
                    'name' => trim($this->relativeOverlayFolder($fileFolder, $folder), '/') ?: basename($fileFolder),
                    'file_count' => $count,
                ];
            }
        }

        return view('map.files', compact('filesData', 'foldersData'));
    }

    public function deleteFile(Request $request)
    {
        $path = $this->normalizePublicStoragePath((string) $request->path);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            $category = $this->resolveCategoryFromStoragePath((string) $path);
            $notificationResult = $this->notifyMapFileChange('delete', $category, [[
                'name' => basename((string) $path),
                'path' => (string) $path,
                'url' => $this->mapFileUrl((string) $path),
            ]]);

            $this->clearMapDataCache();

            return response()->json([
                'message' => 'Deleted. ' . $notificationResult['admin_message'],
                'notified_users_count' => $notificationResult['notified_users_count'],
            ]);
        }

        return response()->json([
            'message' => 'File not found',
        ], 404);
    }

    public function deleteFolder(Request $request)
    {
        $folder = $this->normalizePublicStoragePath((string) $request->input('folder'));
        $allowedRoots = array_map(fn ($directory) => "maps/{$directory}", array_values(self::CATEGORY_DIRECTORY_MAP));

        $isAllowed = collect($allowedRoots)->contains(function ($root) use ($folder) {
            return $folder !== $root && str_starts_with($folder . '/', $root . '/');
        });

        if (!$isAllowed) {
            return response()->json([
                'message' => 'Invalid folder path.',
            ], 422);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($folder)) {
            return response()->json([
                'message' => 'Folder not found.',
            ], 404);
        }

        $files = collect($disk->allFiles($folder))->map(function ($path) {
            return [
                'name' => basename($path),
                'path' => $path,
                'url' => $this->mapFileUrl($path),
            ];
        })->values()->all();

        $disk->deleteDirectory($folder);
        $category = $this->resolveCategoryFromStoragePath($folder);
        $this->clearMapDataCache();

        $notificationResult = $this->notifyMapFileChange('delete', $category, [[
            'name' => basename($folder),
            'path' => $folder,
        ]]);

        return response()->json([
            'message' => 'Folder deleted. ' . $notificationResult['admin_message'],
            'deleted_files_count' => count($files),
            'notified_users_count' => $notificationResult['notified_users_count'],
        ]);
    }

    public function serveMapFile(string $path)
    {
        $path = $this->normalizePublicStoragePath($path);
        $disk = Storage::disk('public');
        $fullPath = $disk->path($path);

        if (!$disk->exists($path) || !is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function mapNotifications(Request $request)
    {
        $user = $request->user();
        $isGuest = (bool) $request->session()->get('guest_terms_accepted');

        if (!$user && !$isGuest) {
            return response()->json([
                'notifications' => [],
            ]);
        }

        $notifications = $this->readMapNotifications();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function clearOldMapNotifications(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $days = max(1, min(365, $days));
        $cutoff = now()->subDays($days);

        $notifications = $this->readMapNotifications();
        $filtered = array_values(array_filter($notifications, function ($item) use ($cutoff) {
            $createdAt = $item['created_at'] ?? null;

            if (!$createdAt) {
                return false;
            }

            try {
                return \Carbon\Carbon::parse($createdAt)->greaterThanOrEqualTo($cutoff);
            } catch (\Throwable $exception) {
                return false;
            }
        }));

        $removed = count($notifications) - count($filtered);
        $this->writeMapNotifications($filtered);

        return response()->json([
            'message' => "Cleared {$removed} old notification(s).",
            'removed_count' => $removed,
            'remaining_count' => count($filtered),
        ]);
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
        return response()->json(Cache::store('file')->remember($this->irrigatedChartCacheKey(), now()->addHour(), function () {
            $municipalityDetails = $this->getMunicipalityDetailIndex();

            if (empty($municipalityDetails)) {
                return [
                    'error' => 'details.json not found or invalid',
                ];
            }

            $irrigatedStats = $this->collectIrrigatedAreas($municipalityDetails);
            $piaStats = $this->collectPotentialAreas($municipalityDetails);
            $chartData = [];
            $hasUploadedIrrigatedData = !empty($irrigatedStats);

            foreach ($municipalityDetails as $normalizedName => $detail) {
                $name = $detail['name'];
                $totalLand = max(0, (float) ($detail['total_land_area_ha'] ?? 0));
                $piaArea = round(max(0, (float) ($piaStats[$normalizedName]['pia_area'] ?? 0)), 2);
                $computedIrrigatedArea = round(max(0, (float) ($irrigatedStats[$normalizedName]['irrigated_area'] ?? 0)), 2);
                $fallbackIrrigatedArea = round(max(0, (float) ($detail['area_developed_ha'] ?? 0)), 2);
                $irrigatedArea = $computedIrrigatedArea;
                $irrigatedAreaSource = 'dbf';

                if (
                    (!$hasUploadedIrrigatedData && $irrigatedArea <= 0 && $fallbackIrrigatedArea > 0)
                    || ($totalLand > 0 && $irrigatedArea > $totalLand)
                ) {
                    $irrigatedArea = $fallbackIrrigatedArea;
                    $irrigatedAreaSource = 'details_json';
                }

                if ($piaArea > 0 && $irrigatedArea > $piaArea) {
                    if ($fallbackIrrigatedArea > 0 && ($totalLand <= 0 || $fallbackIrrigatedArea <= $totalLand)) {
                        $irrigatedArea = $fallbackIrrigatedArea;
                        $irrigatedAreaSource = 'details_json';
                    } else {
                        $irrigatedArea = $piaArea;
                    }
                }

                $remainingArea = round(max(0, $piaArea - $irrigatedArea), 2);

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
            $this->resolveMapDataDirectory(self::IRRIGATED_DIRECTORY),
            $municipalityDetails,
            function (string $path, array $record) use ($municipalityDetails): ?string {
                if ($this->shouldSkipIrrigatedPath($path)) {
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
            $this->resolveMapDataDirectory(self::POTENTIAL_DIRECTORY),
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
                if (method_exists($record, 'isDeleted') && $record->isDeleted()) {
                    continue;
                }

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

                $aggregated[$normalizedMunicipality][$areaKey] += max(0, (float) $resolvedArea);
            }

            $reader->close();
        }

        foreach ($aggregated as &$values) {
            $values[$areaKey] = round((float) $values[$areaKey], 2);
            $values['source_files'] = array_values(array_unique($values['source_files']));
        }

        return $aggregated;
    }

    private function resolveMapDataDirectory(string $relativeDirectory): string
    {
        $relativeDirectory = trim(str_replace('\\', '/', $relativeDirectory), '/');
        $primaryPath = storage_path('app/public/' . $relativeDirectory);

        if (is_dir($primaryPath)) {
            return $primaryPath;
        }

        return public_path('storage/' . $relativeDirectory);
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

    private function shouldSkipIrrigatedPath(string $path): bool
    {
        $normalizedPath = strtolower($path);

        return str_contains($normalizedPath, 'potential ia')
            || str_contains($normalizedPath, 'non-operational')
            || str_contains($normalizedPath, 'non operational');
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
        Cache::store('file')->forget($this->irrigatedChartCacheKey());
    }

    private function notifyUploadedMapFiles(Request $request, string $category, array $uploadedFiles, string $targetFolder): void
    {
        $actor = $request->user();

        if (!$actor || empty($uploadedFiles)) {
            return;
        }

        $fileNames = collect($uploadedFiles)
            ->pluck('name')
            ->filter()
            ->values();

        if ($fileNames->isEmpty()) {
            return;
        }

        $categoryDirectory = self::CATEGORY_DIRECTORY_MAP[$category] ?? $category;
        $directory = trim("maps/{$categoryDirectory}/{$targetFolder}", '/');
        $directoryLabel = $directory !== '' ? $directory : 'maps';
        $actorLabel = $this->notifications()->actorLabel($actor);
        $title = $fileNames->count() === 1 ? 'Map file uploaded' : 'Map files uploaded';
        $message = $fileNames->count() === 1
            ? "{$actorLabel} uploaded a new file into {$directoryLabel}/{$fileNames->first()}."
            : "{$actorLabel} uploaded new files into {$directoryLabel}: {$fileNames->implode(', ')}.";

        $this->notifications()->notifyAgency($actor, $title, $message, [
            'type' => 'map_file',
            'team' => 'all',
            'team_label' => 'All Teams',
            'map_category' => $category,
            'map_directory' => $directoryLabel,
            'map_files' => $fileNames->all(),
        ]);
    }

    private function notifyMapFileChange(string $action, string $category, array $files): array
    {
        $files = array_values(array_filter($files, function ($file) {
            return !empty($file['path']);
        }));

        if (empty($files)) {
            return [
                'notified_users_count' => 0,
                'admin_message' => 'No users were notified.',
            ];
        }

        $actor = Auth::check() ? (Auth::user()->name ?? 'Admin') : 'Admin';
        $locations = array_values(array_unique(array_map(function ($file) {
            return trim(dirname((string) ($file['path'] ?? '')), '.');
        }, $files)));

        $entry = [
            'id' => uniqid('map_', true),
            'action' => $action,
            'category' => $category,
            'actor' => $actor,
            'files' => array_map(function ($file) {
                return [
                    'name' => (string) ($file['name'] ?? basename((string) ($file['path'] ?? ''))),
                    'path' => (string) ($file['path'] ?? ''),
                ];
            }, $files),
            'locations' => $locations,
            'created_at' => now()->toIso8601String(),
        ];

        $existing = $this->readMapNotifications();
        array_unshift($existing, $entry);
        $existing = array_slice($existing, 0, self::MAP_NOTIFICATION_LIMIT);
        $this->writeMapNotifications($existing);

        $notifiedUsers = User::query()
            ->where('role', '!=', 'admin')
            ->count();

        $adminMessage = 'Other users have been notified.';

        return [
            'notified_users_count' => $notifiedUsers,
            'admin_message' => $adminMessage,
        ];
    }

    private function resolveCategoryFromStoragePath(string $path): string
    {
        $normalizedPath = strtolower(str_replace('\\', '/', $path));

        if (str_contains('/' . trim($normalizedPath, '/') . '/', '/maps/irrigated/')) {
            return 'Irrigated Area';
        }

        if (str_contains('/' . trim($normalizedPath, '/') . '/', '/maps/potential/')) {
            return 'Potential Irrigable Area';
        }

        if (str_contains('/' . trim($normalizedPath, '/') . '/', '/maps/land_boundary/')) {
            return 'Pangasinan Land Boundary';
        }

        return 'Map Files';
    }

    private function mapFileUrl(string $path): string
    {
        $segments = array_map('rawurlencode', explode('/', $this->normalizePublicStoragePath($path)));

        return url('/map/file/' . implode('/', $segments));
    }

    private function normalizePublicStoragePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = preg_replace('#^/?storage/#', '', $path);
        $path = trim($path, '/');

        $parts = array_filter(explode('/', $path), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        return implode('/', $parts);
    }

    private function irrigatedChartCacheKey(): string
    {
        return self::IRRIGATED_CHART_CACHE_KEY_PREFIX . $this->mapDataFingerprint([
            self::IRRIGATED_DIRECTORY,
            self::POTENTIAL_DIRECTORY,
            self::MUNICIPALITY_DETAILS_PATH,
        ]);
    }

    private function mapDataFingerprint(array $paths): string
    {
        $parts = [];

        foreach ($paths as $path) {
            $storagePath = storage_path('app/public/' . trim($path, '/'));
            $publicPath = public_path(trim($path, '/'));
            $target = is_dir($storagePath) || file_exists($storagePath) ? $storagePath : $publicPath;

            if (is_dir($target)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS));

                foreach ($iterator as $file) {
                    if (!$file->isFile()) {
                        continue;
                    }

                    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($target) + 1));
                    $parts[] = $path . '/' . $relativePath . ':' . $file->getMTime() . ':' . $file->getSize();
                }
            } elseif (is_file($target)) {
                $parts[] = $path . ':' . filemtime($target) . ':' . filesize($target);
            }
        }

        sort($parts);

        return sha1(implode('|', $parts));
    }

    private function readMapNotifications(): array
    {
        $filePath = storage_path('app/' . self::MAP_NOTIFICATION_FILE);

        if (!file_exists($filePath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($filePath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeMapNotifications(array $notifications): void
    {
        $filePath = storage_path('app/' . self::MAP_NOTIFICATION_FILE);
        file_put_contents($filePath, json_encode($notifications, JSON_PRETTY_PRINT));
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
