<?php

namespace App\Http\Controllers;

use App\Models\IrrigatedArea;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use ZipArchive;

class MapController extends Controller
{
    private const IRRIGATED_CHART_CACHE_KEY = 'map.irrigated_chart_data.v8';
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
    private const MAP_API_CACHE_KEY = 'map.api.snapshot.v4';
    private const MAP_RENDER_CACHE_KEY_PREFIX = 'map.rendered_geojson.v19.';
    private const MAP_API_VERSION_FILE = 'map_api_version.json';
    private const MAP_API_SIGNATURE_FILE = 'map_api_signature.json';
    private const RENDERED_FEATURE_LIMITS = [
        'irrigated' => 86500,
        'potential' => 20000,
        'land_boundary' => 5000,
    ];
    private const RENDERED_FEATURES_PER_FILE_LIMITS = [
        'irrigated' => 86500,
        'potential' => 500,
        'land_boundary' => 1000,
    ];
    private const MUNICIPALITY_RENDERED_FEATURE_LIMIT = 30000;
    private const MUNICIPALITY_RENDERED_FEATURES_PER_FILE_LIMIT = 5000;
    private const IRRIGATED_OVERVIEW_POLYGON_POINTS = 10;
    private const DEFAULT_RENDER_POLYGON_POINTS = 35;
    private const DEFAULT_RENDER_LINE_POINTS = 120;
    private const SYNC_IRRIGATED_IMPORT_FILE_LIMIT = 6;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

    private function denyMapAccessUnlessAllowed(Request $request)
    {
        if ($request->session()->get('guest_terms_accepted') === true) {
            return null;
        }

        if ($request->user() && $request->session()->get('agreed_to_terms') === true) {
            return null;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Map access requires login or accepted guest access.',
            ], 403);
        }

        if ($request->user()) {
            return redirect()->route('terms.show');
        }

        return redirect()->route('login');
    }

    private function mapDisk(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk((string) config('filesystems.maps_disk', 'public'));
    }

    private function mapDiskUsesDirectPublicUrls(): bool
    {
        if (!config('filesystems.maps_public_urls', true)) {
            return false;
        }

        $name = (string) config('filesystems.maps_disk', 'public');

        return config("filesystems.disks.{$name}.driver") === 's3';
    }

    private function overlayPrefixHasAssets(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $root): bool
    {
        $root = trim(str_replace('\\', '/', $root), '/');

        if ($root === '') {
            return false;
        }

        if (count($disk->files($root)) > 0) {
            return true;
        }

        foreach ($disk->directories($root) as $_) {
            return true;
        }

        return false;
    }

    private function mapStorageKeyFromUrl(string $fileUrl): string
    {
        $decodedUrl = rawurldecode($fileUrl);
        $pathPart = parse_url($decodedUrl, PHP_URL_PATH) ?: '';

        if (str_contains($pathPart, '/map/file/')) {
            $trimmed = substr($pathPart, strpos($pathPart, '/map/file/') + strlen('/map/file/'));

            return $this->normalizePublicStoragePath($trimmed);
        }

        $diskName = (string) config('filesystems.maps_disk', 'public');
        $baseUrl = rtrim((string) (config("filesystems.disks.{$diskName}.url") ?? ''), '/');

        if ($baseUrl !== '' && str_starts_with($decodedUrl, $baseUrl.'/')) {
            return $this->normalizePublicStoragePath(substr($decodedUrl, strlen($baseUrl) + 1));
        }

        $candidate = $pathPart !== '' ? ltrim($pathPart, '/') : $decodedUrl;

        return $this->normalizePublicStoragePath($candidate);
    }

    public function Showmap(Request $request)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        $overlayGroups = $this->defaultOverlayGroups();
        $uploadTargets = [];

        return view('map.map', compact('overlayGroups', 'uploadTargets'));
    }

    public function mapApiStatus(Request $request)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        $snapshot = $this->mapApiSnapshot();

        return response()->json([
            'version' => $snapshot['version'],
            'updated_at' => $snapshot['updated_at'],
            'overlay_groups' => collect($snapshot['overlay_groups'])->map(function ($group, $categoryKey) {
                return [
                    'label' => $group['label'],
                    'files' => [],
                    'file_count' => (int) ($group['file_count'] ?? 0),
                    'files_loaded' => false,
                    'has_files' => (bool) ($group['has_files'] ?? false),
                    'render_url' => url('/map/render/' . $categoryKey),
                ];
            })->all(),
            'upload_targets' => $snapshot['upload_targets'],
        ]);
    }

    public function overlayFiles(Request $request, string $category)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        $snapshot = $this->mapApiSnapshot();

        if (!isset($snapshot['overlay_groups'][$category])) {
            return response()->json([
                'message' => 'Invalid map category.',
            ], 404);
        }

        return response()->json([
            ...$snapshot['overlay_groups'][$category],
            'version' => $snapshot['version'],
            'updated_at' => $snapshot['updated_at'],
        ]);
    }

    public function renderedOverlay(Request $request, string $category)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        @ini_set('memory_limit', '2048M');
        @set_time_limit(180);

        if (!array_key_exists($category, $this->categoryRouteMap())) {
            return response()->json([
                'message' => 'Invalid map category.',
            ], 404);
        }

        $snapshot = $this->mapApiSnapshot();
        $version = $snapshot['version'];
        $cacheKey = self::MAP_RENDER_CACHE_KEY_PREFIX . $version . '.' . $category;

        $payload = Cache::store('file')->rememberForever($cacheKey, function () use ($category, $snapshot) {
            return $this->buildRenderedOverlayPayload($category, $snapshot);
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=86400');
    }

    public function renderedMunicipalityIrrigatedOverlay(Request $request, string $municipality)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        @ini_set('memory_limit', '2048M');
        @set_time_limit(180);

        $snapshot = $this->mapApiSnapshot();
        $version = $snapshot['version'];
        $normalizedMunicipality = $this->normalizeMunicipalityName(rawurldecode($municipality));

        if ($normalizedMunicipality === '') {
            return response()->json([
                'message' => 'Invalid municipality.',
            ], 422);
        }

        $cacheKey = self::MAP_RENDER_CACHE_KEY_PREFIX . $version . '.irrigated.municipality.' . sha1($normalizedMunicipality);

        $payload = Cache::store('file')->rememberForever($cacheKey, function () use ($snapshot, $normalizedMunicipality, $municipality) {
            return $this->buildMunicipalityIrrigatedOverlayPayload($snapshot, $normalizedMunicipality, rawurldecode($municipality));
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=86400');
    }

    public function irrigatedAreasInBounds(Request $request)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        @ini_set('memory_limit', '2048M');
        @set_time_limit(180);

        $bounds = $this->validatedMapBounds($request);

        if (!$bounds) {
            return response()->json([
                'message' => 'Valid sw_lat, sw_lng, ne_lat, and ne_lng query parameters are required.',
            ], 422);
        }

        $snapshot = $this->mapApiSnapshot();
        $version = $snapshot['version'];

        if (IrrigatedArea::query()->exists()) {
            $payload = $this->buildDatabaseIrrigatedPayload($snapshot, $bounds);

            return response()->json($payload)->header('Cache-Control', 'private, max-age=30');
        }

        $fullCacheKey = self::MAP_RENDER_CACHE_KEY_PREFIX . $version . '.irrigated';
        $fullPayload = Cache::store('file')->rememberForever($fullCacheKey, function () use ($snapshot) {
            return $this->buildRenderedOverlayPayload('irrigated', $snapshot);
        });
        $payload = $this->filterRenderedPayloadToBounds($fullPayload, $bounds);

        return response()->json($payload)->header('Cache-Control', 'private, max-age=30');
    }

    public function rebuildIrrigatedAreasDatabase(): array
    {
        @ini_set('memory_limit', '2048M');
        @set_time_limit(0);

        $snapshot = $this->mapApiSnapshot();
        $files = $snapshot['overlay_groups']['irrigated']['files'] ?? [];
        $result = [
            'files_imported' => 0,
            'features_imported' => 0,
            'failed_files' => [],
        ];

        IrrigatedArea::query()->truncate();

        foreach ($files as $file) {
            $importResult = $this->importIrrigatedAreaFile($file);
            $result['files_imported'] += $importResult['files_imported'];
            $result['features_imported'] += $importResult['features_imported'];
            $result['failed_files'] = array_merge($result['failed_files'], $importResult['failed_files']);
        }

        $this->clearMapDataCache();

        return $result;
    }

    private function defaultOverlayGroups(): array
    {
        return [
            'irrigated' => $this->emptyOverlayGroup('Irrigated Area'),
            'land_boundary' => $this->emptyOverlayGroup('Pangasinan Land Boundary'),
            'potential' => $this->emptyOverlayGroup('Potential Irrigable Area'),
        ];
    }

    private function emptyOverlayGroup(string $label): array
    {
        return [
            'label' => $label,
            'files' => [],
            'file_count' => 0,
            'files_loaded' => false,
            'has_files' => true,
        ];
    }

    private function categoryRouteMap(): array
    {
        return [
            'irrigated' => ['Irrigated Area', 'irrigated'],
            'land_boundary' => ['Pangasinan Land Boundary', 'land_boundary'],
            'potential' => ['Potential Irrigable Area', 'potential'],
        ];
    }

    private function buildOverlayGroup(string $label, string $directory): array
    {
        $disk = $this->mapDisk();
        $folder = $this->resolveOverlayFolder($label, $directory);

        if (!$this->overlayPrefixHasAssets($disk, $folder)) {
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

                if (str_starts_with(strtolower($path), 'maps/irrigated/') && $this->shouldSkipIrrigatedPath($path)) {
                    return false;
                }

                return true;
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
                    'path' => $path,
                    'name' => basename($path),
                    'url' => $this->mapFileUrl($path),
                    'folder' => $this->relativeOverlayFolder($path, $folder),
                    'size' => $this->overlayFilePayloadSize($path),
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
            'file_count' => count($files),
            'files_loaded' => true,
            'has_files' => count($files) > 0,
        ];
    }

    private function mapApiSnapshot(): array
    {
        return Cache::store('file')->rememberForever(self::MAP_API_CACHE_KEY, function () {
            $version = $this->readMapApiVersion();

            return [
                'version' => $version['version'],
                'updated_at' => $version['updated_at'],
                'overlay_groups' => [
                    'irrigated' => $this->buildOverlayGroup('Irrigated Area', 'irrigated'),
                    'land_boundary' => $this->buildOverlayGroup('Pangasinan Land Boundary', 'land_boundary'),
                    'potential' => $this->buildOverlayGroup('Potential Irrigable Area', 'potential'),
                ],
                'upload_targets' => $this->buildUploadTargets(),
            ];
        });
    }

    private function buildRenderedOverlayPayload(string $category, array $snapshot): array
    {
        if ($category === 'land_boundary') {
            $basePath = public_path('maps/PANGASINAN.geojson');

            if (is_file($basePath)) {
                $features = $this->featuresFromGeoJsonFile($basePath, 'PANGASINAN.geojson', $category);
                return $this->renderedOverlayFeatureCollection($category, $snapshot, $features, []);
            }
        }

        $group = $snapshot['overlay_groups'][$category] ?? null;
        $features = [];
        $failedFiles = [];

        if (!$group || empty($group['files'])) {
            return $this->renderedOverlayFeatureCollection($category, $snapshot, [], []);
        }

        $files = $group['files'];

        $featureLimit = self::RENDERED_FEATURE_LIMITS[$category] ?? 20000;
        $perFileFeatureLimit = self::RENDERED_FEATURES_PER_FILE_LIMITS[$category] ?? 500;

        foreach ($files as $file) {
            if (count($features) >= $featureLimit) {
                $failedFiles[] = [
                    'name' => 'Additional ' . ($group['label'] ?? $category) . ' files',
                    'message' => "Skipped from rendered overlay after {$featureLimit} features to keep the browser responsive. These files are still included in DBF/stat calculations.",
                ];
                break;
            }

            $fileUrl = (string) ($file['url'] ?? '');
            $fileName = (string) ($file['name'] ?? 'Unknown file');
            $fileFeatureLimit = min($perFileFeatureLimit, $featureLimit - count($features));

            try {
                foreach ($this->featuresFromMapFile($fileUrl, $fileName, $category, $fileFeatureLimit) as $feature) {
                    $features[] = $feature;

                    if (count($features) >= $featureLimit) {
                        break;
                    }
                }
            } catch (\Throwable $exception) {
                try {
                    $fallbackPath = $this->fallbackRenderablePath($fileUrl);
                    $fallbackName = basename($fallbackPath);

                    foreach ($this->featuresFromMapFile($this->mapFileUrl($fallbackPath), $fallbackName, $category, $fileFeatureLimit) as $feature) {
                        $features[] = $feature;

                        if (count($features) >= $featureLimit) {
                            break;
                        }
                    }
                } catch (\Throwable $fallbackException) {
                    $failedFiles[] = [
                        'name' => $fileName,
                        'message' => $exception->getMessage() . ' / fallback: ' . $fallbackException->getMessage(),
                    ];
                }
            }
        }

        return $this->renderedOverlayFeatureCollection($category, $snapshot, $features, $failedFiles);
    }

    private function buildMunicipalityIrrigatedOverlayPayload(array $snapshot, string $normalizedMunicipality, string $municipalityLabel): array
    {
        $group = $snapshot['overlay_groups']['irrigated'] ?? null;
        $files = $group['files'] ?? [];
        $features = [];
        $failedFiles = [];
        $matchedFiles = 0;

        if (!$group || empty($files)) {
            return $this->municipalityRenderedOverlayFeatureCollection($snapshot, $municipalityLabel, [], [], 0);
        }

        foreach ($files as $file) {
            if (count($features) >= self::MUNICIPALITY_RENDERED_FEATURE_LIMIT) {
                $failedFiles[] = [
                    'name' => 'Additional ' . $municipalityLabel . ' irrigated files',
                    'message' => 'Skipped after the municipality render limit to keep the browser responsive.',
                ];
                break;
            }

            $fileUrl = (string) ($file['url'] ?? '');
            $fileName = (string) ($file['name'] ?? 'Unknown file');

            if (!$this->renderFileBelongsToMunicipality($file, $fileUrl, $fileName, $normalizedMunicipality)) {
                continue;
            }

            $matchedFiles++;
            $fileFeatureLimit = min(
                self::MUNICIPALITY_RENDERED_FEATURES_PER_FILE_LIMIT,
                self::MUNICIPALITY_RENDERED_FEATURE_LIMIT - count($features)
            );

            try {
                foreach ($this->featuresFromMapFile($fileUrl, $fileName, 'irrigated', $fileFeatureLimit) as $feature) {
                    $features[] = $feature;

                    if (count($features) >= self::MUNICIPALITY_RENDERED_FEATURE_LIMIT) {
                        break;
                    }
                }
            } catch (\Throwable $exception) {
                try {
                    $fallbackPath = $this->fallbackRenderablePath($fileUrl);
                    $fallbackName = basename($fallbackPath);

                    foreach ($this->featuresFromMapFile($this->mapFileUrl($fallbackPath), $fallbackName, 'irrigated', $fileFeatureLimit) as $feature) {
                        $features[] = $feature;

                        if (count($features) >= self::MUNICIPALITY_RENDERED_FEATURE_LIMIT) {
                            break;
                        }
                    }
                } catch (\Throwable $fallbackException) {
                    $failedFiles[] = [
                        'name' => $fileName,
                        'message' => $exception->getMessage() . ' / fallback: ' . $fallbackException->getMessage(),
                    ];
                }
            }
        }

        return $this->municipalityRenderedOverlayFeatureCollection($snapshot, $municipalityLabel, $features, $failedFiles, $matchedFiles);
    }

    private function municipalityRenderedOverlayFeatureCollection(
        array $snapshot,
        string $municipalityLabel,
        array $features,
        array $failedFiles,
        int $matchedFiles
    ): array {
        $sourceFeatureCount = count($features);
        $renderedFeatures = $this->combinedIrrigatedRenderFeatures($features);

        return [
            'type' => 'FeatureCollection',
            'category' => 'irrigated',
            'label' => 'Irrigated Area - ' . $municipalityLabel,
            'municipality' => $municipalityLabel,
            'version' => $snapshot['version'],
            'updated_at' => $snapshot['updated_at'],
            'feature_count' => $sourceFeatureCount,
            'rendered_feature_count' => count($renderedFeatures),
            'matched_files_count' => $matchedFiles,
            'failed_files' => $failedFiles,
            'features' => $renderedFeatures,
        ];
    }

    private function renderFileBelongsToMunicipality(array $file, string $fileUrl, string $fileName, string $normalizedMunicipality): bool
    {
        $folder = $this->normalizeMunicipalityName((string) ($file['folder'] ?? ''));
        $source = $this->normalizeMunicipalityName(rawurldecode($fileUrl . ' ' . $fileName));

        return $this->municipalityTextMatches($folder, $normalizedMunicipality)
            || $this->municipalityTextMatches($source, $normalizedMunicipality);
    }

    private function municipalityTextMatches(string $text, string $normalizedMunicipality): bool
    {
        if ($text === '' || $normalizedMunicipality === '') {
            return false;
        }

        return $text === $normalizedMunicipality
            || str_starts_with($text, $normalizedMunicipality . ' ')
            || str_contains($text, ' ' . $normalizedMunicipality . ' ')
            || str_ends_with($text, ' ' . $normalizedMunicipality);
    }

    private function renderedOverlayFeatureCollection(string $category, array $snapshot, array $features, array $failedFiles): array
    {
        $group = $snapshot['overlay_groups'][$category] ?? [];
        $sourceFeatureCount = count($features);
        $renderedFeatures = $category === 'irrigated'
            ? $this->combinedIrrigatedRenderFeatures($features)
            : $features;

        return [
            'type' => 'FeatureCollection',
            'category' => $category,
            'label' => (string) ($group['label'] ?? $category),
            'version' => $snapshot['version'],
            'updated_at' => $snapshot['updated_at'],
            'feature_count' => $sourceFeatureCount,
            'rendered_feature_count' => count($renderedFeatures),
            'failed_files' => $failedFiles,
            'features' => $renderedFeatures,
        ];
    }

    private function validatedMapBounds(Request $request): ?array
    {
        $values = [];

        foreach (['sw_lat', 'sw_lng', 'ne_lat', 'ne_lng'] as $key) {
            if (!$request->has($key) || !is_numeric($request->query($key))) {
                return null;
            }

            $values[$key] = (float) $request->query($key);
        }

        $south = max(-90, min($values['sw_lat'], $values['ne_lat']));
        $north = min(90, max($values['sw_lat'], $values['ne_lat']));
        $west = max(-180, min($values['sw_lng'], $values['ne_lng']));
        $east = min(180, max($values['sw_lng'], $values['ne_lng']));

        if ($south >= $north || $west >= $east) {
            return null;
        }

        return compact('south', 'west', 'north', 'east');
    }

    private function buildDatabaseIrrigatedPayload(array $snapshot, array $bounds): array
    {
        $rows = IrrigatedArea::query()
            ->where('min_lng', '<=', $bounds['east'])
            ->where('max_lng', '>=', $bounds['west'])
            ->where('min_lat', '<=', $bounds['north'])
            ->where('max_lat', '>=', $bounds['south'])
            ->orderBy('id')
            ->limit(20000)
            ->get(['properties_json', 'geometry_json']);

        $features = [];

        foreach ($rows as $row) {
            $geometry = json_decode((string) $row->geometry_json, true);

            if (!is_array($geometry)) {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'properties' => json_decode((string) $row->properties_json, true) ?: [
                    '_category' => 'irrigated',
                ],
                'geometry' => $geometry,
            ];
        }

        $renderedFeatures = $this->combinedIrrigatedRenderFeatures($features);

        return [
            'type' => 'FeatureCollection',
            'category' => 'irrigated',
            'label' => 'Irrigated Area',
            'version' => $snapshot['version'],
            'updated_at' => $snapshot['updated_at'],
            'feature_count' => $rows->count(),
            'rendered_feature_count' => count($renderedFeatures),
            'source' => 'database',
            'failed_files' => [],
            'features' => $renderedFeatures,
        ];
    }

    private function filterRenderedPayloadToBounds(array $payload, array $bounds): array
    {
        $features = [];

        foreach (($payload['features'] ?? []) as $feature) {
            $filteredFeature = $this->filterFeatureToBounds($feature, $bounds);

            if ($filteredFeature) {
                $features[] = $filteredFeature;
            }
        }

        return [
            ...$payload,
            'type' => 'FeatureCollection',
            'category' => 'irrigated',
            'label' => (string) ($payload['label'] ?? 'Irrigated Area'),
            'source' => 'file_fallback',
            'features' => $features,
            'rendered_feature_count' => count($features),
        ];
    }

    private function filterFeatureToBounds(array $feature, array $bounds): ?array
    {
        $geometry = $feature['geometry'] ?? null;

        if (!is_array($geometry) || empty($geometry['type']) || !isset($geometry['coordinates'])) {
            return null;
        }

        $filteredGeometry = $this->filterGeometryToBounds($geometry, $bounds);

        if (!$filteredGeometry) {
            return null;
        }

        return [
            'type' => 'Feature',
            'properties' => is_array($feature['properties'] ?? null) ? $feature['properties'] : [],
            'geometry' => $filteredGeometry,
        ];
    }

    private function filterGeometryToBounds(array $geometry, array $bounds): ?array
    {
        $type = (string) ($geometry['type'] ?? '');
        $coordinates = $geometry['coordinates'] ?? null;

        if (!is_array($coordinates)) {
            return null;
        }

        if ($type === 'MultiPolygon') {
            $filtered = array_values(array_filter($coordinates, function ($polygon) use ($bounds) {
                return isset($polygon[0]) && $this->coordinateListIntersectsBounds($polygon[0], $bounds);
            }));

            return $filtered ? ['type' => 'MultiPolygon', 'coordinates' => $filtered] : null;
        }

        if ($type === 'Polygon') {
            return isset($coordinates[0]) && $this->coordinateListIntersectsBounds($coordinates[0], $bounds) ? $geometry : null;
        }

        if ($type === 'MultiLineString') {
            $filtered = array_values(array_filter($coordinates, fn ($line) => $this->coordinateListIntersectsBounds($line, $bounds)));

            return $filtered ? ['type' => 'MultiLineString', 'coordinates' => $filtered] : null;
        }

        if ($type === 'LineString') {
            return $this->coordinateListIntersectsBounds($coordinates, $bounds) ? $geometry : null;
        }

        if ($type === 'MultiPoint') {
            $filtered = array_values(array_filter($coordinates, fn ($point) => $this->pointIntersectsBounds($point, $bounds)));

            return $filtered ? ['type' => 'MultiPoint', 'coordinates' => $filtered] : null;
        }

        if ($type === 'Point') {
            return $this->pointIntersectsBounds($coordinates, $bounds) ? $geometry : null;
        }

        return null;
    }

    private function coordinateListIntersectsBounds($coordinates, array $bounds): bool
    {
        if (!is_array($coordinates) || empty($coordinates)) {
            return false;
        }

        $featureBounds = $this->geometryBoundsFromCoordinateList($coordinates);

        return $featureBounds
            && !($featureBounds['min_lng'] > $bounds['east']
                || $featureBounds['max_lng'] < $bounds['west']
                || $featureBounds['min_lat'] > $bounds['north']
                || $featureBounds['max_lat'] < $bounds['south']);
    }

    private function pointIntersectsBounds($point, array $bounds): bool
    {
        if (!is_array($point) || !isset($point[0], $point[1]) || !is_numeric($point[0]) || !is_numeric($point[1])) {
            return false;
        }

        $lng = (float) $point[0];
        $lat = (float) $point[1];

        return $lng >= $bounds['west']
            && $lng <= $bounds['east']
            && $lat >= $bounds['south']
            && $lat <= $bounds['north'];
    }

    private function geometryBounds(array $geometry): ?array
    {
        if (!isset($geometry['coordinates']) || !is_array($geometry['coordinates'])) {
            return null;
        }

        return $this->geometryBoundsFromCoordinateList($geometry['coordinates']);
    }

    private function geometryBoundsFromCoordinateList($coordinates): ?array
    {
        $bounds = [
            'min_lat' => 90.0,
            'max_lat' => -90.0,
            'min_lng' => 180.0,
            'max_lng' => -180.0,
        ];
        $hasPoint = false;
        $stack = [$coordinates];

        while ($stack) {
            $item = array_pop($stack);

            if (!is_array($item)) {
                continue;
            }

            if (isset($item[0], $item[1]) && is_numeric($item[0]) && is_numeric($item[1])) {
                $lng = (float) $item[0];
                $lat = (float) $item[1];
                $bounds['min_lat'] = min($bounds['min_lat'], $lat);
                $bounds['max_lat'] = max($bounds['max_lat'], $lat);
                $bounds['min_lng'] = min($bounds['min_lng'], $lng);
                $bounds['max_lng'] = max($bounds['max_lng'], $lng);
                $hasPoint = true;
                continue;
            }

            foreach ($item as $child) {
                if (is_array($child)) {
                    $stack[] = $child;
                }
            }
        }

        return $hasPoint ? $bounds : null;
    }

    private function boundsLookLikeWgs84(array $bounds): bool
    {
        return $bounds['min_lng'] >= -180
            && $bounds['max_lng'] <= 180
            && $bounds['min_lat'] >= -90
            && $bounds['max_lat'] <= 90;
    }

    private function combinedIrrigatedRenderFeatures(array $features): array
    {
        $polygons = [];
        $lines = [];
        $points = [];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? null;

            if (!is_array($geometry) || empty($geometry['type']) || !isset($geometry['coordinates'])) {
                continue;
            }

            $type = (string) $geometry['type'];
            $coordinates = $geometry['coordinates'];

            if ($type === 'Polygon') {
                $polygons[] = $coordinates;
            } elseif ($type === 'MultiPolygon') {
                foreach ($coordinates as $polygon) {
                    $polygons[] = $polygon;
                }
            } elseif ($type === 'LineString') {
                $lines[] = $coordinates;
            } elseif ($type === 'MultiLineString') {
                foreach ($coordinates as $line) {
                    $lines[] = $line;
                }
            } elseif ($type === 'Point') {
                $points[] = $coordinates;
            } elseif ($type === 'MultiPoint') {
                foreach ($coordinates as $point) {
                    $points[] = $point;
                }
            }
        }

        $combined = [];
        $properties = [
            '_source_file' => 'Combined irrigated render layer',
            '_category' => 'irrigated',
        ];

        if (!empty($polygons)) {
            $combined[] = [
                'type' => 'Feature',
                'properties' => $properties,
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => $polygons,
                ],
            ];
        }

        if (!empty($lines)) {
            $combined[] = [
                'type' => 'Feature',
                'properties' => $properties,
                'geometry' => [
                    'type' => 'MultiLineString',
                    'coordinates' => $lines,
                ],
            ];
        }

        if (!empty($points)) {
            $combined[] = [
                'type' => 'Feature',
                'properties' => $properties,
                'geometry' => [
                    'type' => 'MultiPoint',
                    'coordinates' => $points,
                ],
            ];
        }

        return $combined;
    }

    private function featuresFromMapFile(string $fileUrl, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        return $this->featuresFromMapLocalPath(
            $this->storagePathFromMapUrl($fileUrl),
            $fileName,
            $category,
            $maxFeatures
        );
    }

    private function featuresFromMapLocalPath(string $localPath, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'geojson', 'json' => $this->featuresFromGeoJsonFile($localPath, $fileName, $category, $maxFeatures),
            'kml' => $this->featuresFromKmlFile($localPath, $fileName, $category, $maxFeatures),
            'kmz' => $this->featuresFromKmzFile($localPath, $fileName, $category, $maxFeatures),
            'shp' => $this->featuresFromShapefile($localPath, $fileName, $category, $maxFeatures),
            'zip' => $this->featuresFromZippedShapefile($localPath, $fileName, $category, $maxFeatures),
            default => [],
        };
    }

    // THIS IS WORKING ON LOCAL LARAVEL ONLY
    // private function storagePathFromMapUrl(string $fileUrl): string
    // {
    //     $path = parse_url($fileUrl, PHP_URL_PATH) ?: $fileUrl;
    //     $prefix = '/map/file/';

    //     if (str_contains($path, $prefix)) {
    //         $path = substr($path, strpos($path, $prefix) + strlen($prefix));
    //     }

    //     $storagePath = $this->normalizePublicStoragePath(rawurldecode($path));
    //     $fullPath = Storage::disk('public')->path($storagePath);

    //     if (!is_file($fullPath)) {
    //         throw new \RuntimeException('Map source file was not found.');
    //     }

    //     return $fullPath;
    // }

    /**
     * Download or resolve the map file object key on the map disk into a readable local filesystem path.
     */
    private function localPathForNormalizedMapStorageKey(string $storageKey): string
    {
        $storagePath = $this->normalizePublicStoragePath($storageKey);
        $disk = $this->mapDisk();

        if ($disk->exists($storagePath)) {
            $tempPath = storage_path('app/temp_maps/'.ltrim($storagePath, '/'));
            $tempDir = dirname($tempPath);

            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            if (!file_exists($tempPath)) {
                file_put_contents($tempPath, $disk->get($storagePath));
            }

            $extension = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));
            if (in_array($extension, ['shp', 'dbf', 'shx'], true)) {
                $basePathRemote = substr($storagePath, 0, -4);
                $basePathLocal = substr($tempPath, 0, -4);

                foreach (self::SHAPEFILE_COMPANION_EXTENSIONS as $comp) {
                    $compPathRemote = $basePathRemote.'.'.$comp;
                    $compPathLocal = $basePathLocal.'.'.$comp;

                    if ($disk->exists($compPathRemote) && !file_exists($compPathLocal)) {
                        file_put_contents($compPathLocal, $disk->get($compPathRemote));
                    }
                }
            }

            return $tempPath;
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($storagePath)) {
            $fullPath = $publicDisk->path($storagePath);

            if (is_file($fullPath)) {
                return $fullPath;
            }
        }

        throw new \RuntimeException('Map source file was not found.');
    }

    private function storagePathFromMapUrl(string $fileUrl): string
    {
        return $this->localPathForNormalizedMapStorageKey($this->mapStorageKeyFromUrl($fileUrl));
    }

    private function fallbackRenderablePath(string $fileUrl): string
    {
        $fullPath = $this->storagePathFromMapUrl($fileUrl);
        $basePath = preg_replace('/\.[^.]+$/', '', $fullPath);

        foreach (['geojson', 'json', 'kml', 'kmz'] as $extension) {
            $candidate = $basePath . '.' . $extension;

            if (is_file($candidate)) {
                $publicRoot = str_replace('\\', '/', Storage::disk('public')->path(''));
                $normalizedCandidate = str_replace('\\', '/', $candidate);

                if (str_starts_with($normalizedCandidate, $publicRoot)) {
                    return substr($normalizedCandidate, strlen($publicRoot));
                }
            }
        }

        throw new \RuntimeException('No renderable fallback source was found.');
    }

    private function featuresFromGeoJsonFile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $decoded = json_decode((string) file_get_contents($path), true);

        if (!is_array($decoded)) {
            return [];
        }

        return $this->normalizeGeoJsonFeatures($decoded, $fileName, $category, $maxFeatures);
    }

    private function normalizeGeoJsonFeatures(array $geoJson, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        if (($geoJson['type'] ?? null) === 'FeatureCollection') {
            $features = $geoJson['features'] ?? [];
        } elseif (($geoJson['type'] ?? null) === 'Feature') {
            $features = [$geoJson];
        } elseif (!empty($geoJson['type']) && !empty($geoJson['coordinates'])) {
            $features = [[
                'type' => 'Feature',
                'properties' => [],
                'geometry' => $geoJson,
            ]];
        } else {
            $features = [];
        }

        if ($maxFeatures !== null) {
            $features = array_slice($features, 0, max(0, $maxFeatures));
        }

        return array_values(array_filter(array_map(function ($feature) use ($fileName, $category) {
            if (!is_array($feature) || empty($feature['geometry'])) {
                return null;
            }

            $feature['type'] = 'Feature';
            $feature['properties'] = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $feature['properties']['_source_file'] = $fileName;
            $feature['properties']['_category'] = $category;

            return $this->compactRenderedFeature($feature, $fileName, $category);
        }, $features)));
    }

    private function featuresFromKmlFile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $reader = new \XMLReader();

        if (!$reader->open($path)) {
            return [];
        }

        $features = [];

        while ($reader->read()) {
            if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->localName !== 'Placemark') {
                continue;
            }

            $xml = simplexml_load_string((string) $reader->readOuterXML());

            if (!$xml) {
                continue;
            }

            foreach ($this->featuresFromKmlPlacemark($xml, $fileName, $category) as $feature) {
                $features[] = $feature;

                if ($maxFeatures !== null && count($features) >= $maxFeatures) {
                    break 2;
                }
            }
        }

        $reader->close();

        return $features;
    }

    private function featuresFromKmzFile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return [];
        }

        $kmlText = null;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entryName = (string) $zip->getNameIndex($index);

            if (strtolower($entryName) === 'doc.kml' || str_ends_with(strtolower($entryName), '.kml')) {
                $kmlText = $zip->getFromIndex($index);
                break;
            }
        }

        $zip->close();

        return is_string($kmlText) ? $this->featuresFromKmlString($kmlText, $fileName, $category, $maxFeatures) : [];
    }

    private function featuresFromKmlString(string $kmlText, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $xml = simplexml_load_string($kmlText);

        if (!$xml) {
            return [];
        }

        $placemarks = $xml->xpath('//*[local-name()="Placemark"]') ?: [];
        $features = [];

        foreach ($placemarks as $placemark) {
            $name = trim((string) ($placemark->xpath('./*[local-name()="name"]')[0] ?? ''));
            $description = trim((string) ($placemark->xpath('./*[local-name()="description"]')[0] ?? ''));

            foreach ($this->featuresFromKmlPlacemark($placemark, $fileName, $category, $name, $description) as $feature) {
                $features[] = $feature;

                if ($maxFeatures !== null && count($features) >= $maxFeatures) {
                    break 2;
                }
            }
        }

        return $features;
    }

    private function featuresFromKmlPlacemark(
        \SimpleXMLElement $placemark,
        string $fileName,
        string $category,
        ?string $knownName = null,
        ?string $knownDescription = null
    ): array {
        $name = $knownName ?? trim((string) ($placemark->xpath('./*[local-name()="name"]')[0] ?? ''));
        $description = $knownDescription ?? trim((string) ($placemark->xpath('./*[local-name()="description"]')[0] ?? ''));
        $features = [];

        foreach ($this->kmlGeometries($placemark) as $geometry) {
            $features[] = $this->compactRenderedFeature([
                'type' => 'Feature',
                'properties' => [
                    'name' => $name ?: pathinfo($fileName, PATHINFO_FILENAME),
                    'description' => $description,
                    '_source_file' => $fileName,
                    '_category' => $category,
                ],
                'geometry' => $geometry,
            ], $fileName, $category);
        }

        return $features;
    }

    private function kmlGeometries(\SimpleXMLElement $node): array
    {
        $geometries = [];

        foreach ($node->xpath('.//*[local-name()="Point"]/*[local-name()="coordinates"]') ?: [] as $coordinates) {
            $point = $this->parseKmlCoordinateList((string) $coordinates)[0] ?? null;

            if ($point) {
                $geometries[] = [
                    'type' => 'Point',
                    'coordinates' => $point,
                ];
            }
        }

        foreach ($node->xpath('.//*[local-name()="LineString"]/*[local-name()="coordinates"]') ?: [] as $coordinates) {
            $line = $this->parseKmlCoordinateList((string) $coordinates);

            if (count($line) >= 2) {
                $geometries[] = [
                    'type' => 'LineString',
                    'coordinates' => $line,
                ];
            }
        }

        foreach ($node->xpath('.//*[local-name()="Polygon"]') ?: [] as $polygon) {
            $rings = [];

            foreach ($polygon->xpath('.//*[local-name()="LinearRing"]/*[local-name()="coordinates"]') ?: [] as $coordinates) {
                $ring = $this->parseKmlCoordinateList((string) $coordinates);

                if (count($ring) >= 4) {
                    $rings[] = $ring;
                }
            }

            if (!empty($rings)) {
                $geometries[] = [
                    'type' => 'Polygon',
                    'coordinates' => $rings,
                ];
            }
        }

        return $geometries;
    }

    private function parseKmlCoordinateList(string $coordinates): array
    {
        $points = preg_split('/\s+/', trim($coordinates)) ?: [];
        $parsed = [];

        foreach ($points as $point) {
            $parts = array_map('trim', explode(',', $point));

            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $parsed[] = [(float) $parts[0], (float) $parts[1]];
            }
        }

        return $parsed;
    }

    private function featuresFromShapefile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        return $this->featuresFromRawShapefile($path, $fileName, $category, $maxFeatures);
    }

    private function featuresFromRawShapefile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $handle = fopen($path, 'rb');

        if (!$handle) {
            return [];
        }

        try {
            fseek($handle, 100);
            $features = [];

            while (!feof($handle)) {
                $recordHeader = fread($handle, 8);

                if (strlen($recordHeader) < 8) {
                    break;
                }

                $contentLengthWords = unpack('N', substr($recordHeader, 4, 4))[1] ?? 0;
                $contentLength = (int) $contentLengthWords * 2;

                if ($contentLength <= 0) {
                    break;
                }

                $content = fread($handle, $contentLength);

                if (strlen($content) < 4) {
                    continue;
                }

                $geometry = $this->geometryFromRawShpRecord($content, $contentLength);

                if (!$geometry) {
                    continue;
                }

                $features[] = $this->compactRenderedFeature([
                    'type' => 'Feature',
                    'properties' => [
                        'name' => pathinfo($fileName, PATHINFO_FILENAME),
                        '_source_file' => $fileName,
                        '_category' => $category,
                    ],
                    'geometry' => $geometry,
                ], $fileName, $category);

                if ($maxFeatures !== null && count($features) >= $maxFeatures) {
                    break;
                }
            }

            return array_values(array_filter($features, fn ($feature) => !empty($feature['geometry'])));
        } finally {
            fclose($handle);
        }
    }

    private function geometryFromRawShpRecord(string $content, int $contentLength = 0): ?array
    {
        $shapeType = $this->readLittleEndianInt($content, 0);

        return match ($shapeType) {
            1, 11, 21 => $this->pointGeometryFromRawShpRecord($content),
            3, 13, 23 => $contentLength > 400000
                ? $this->bboxGeometryFromRawShpRecord($content)
                : $this->lineGeometryFromRawShpRecord($content),
            5, 15, 25 => $contentLength > 400000
                ? $this->bboxGeometryFromRawShpRecord($content)
                : $this->polygonGeometryFromRawShpRecord($content),
            default => null,
        };
    }

    private function bboxGeometryFromRawShpRecord(string $content): ?array
    {
        if (strlen($content) < 36) {
            return null;
        }

        $xmin = round($this->readLittleEndianDouble($content, 4), 4);
        $ymin = round($this->readLittleEndianDouble($content, 12), 4);
        $xmax = round($this->readLittleEndianDouble($content, 20), 4);
        $ymax = round($this->readLittleEndianDouble($content, 28), 4);

        if ($xmin === $xmax || $ymin === $ymax) {
            return null;
        }

        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [$xmin, $ymin],
                [$xmax, $ymin],
                [$xmax, $ymax],
                [$xmin, $ymax],
                [$xmin, $ymin],
            ]],
        ];
    }

    private function pointGeometryFromRawShpRecord(string $content): ?array
    {
        if (strlen($content) < 20) {
            return null;
        }

        return [
            'type' => 'Point',
            'coordinates' => [
                $this->readLittleEndianDouble($content, 4),
                $this->readLittleEndianDouble($content, 12),
            ],
        ];
    }

    private function lineGeometryFromRawShpRecord(string $content): ?array
    {
        $parts = $this->rawShpParts($content, 120);

        if (empty($parts)) {
            return null;
        }

        return count($parts) === 1
            ? ['type' => 'LineString', 'coordinates' => $parts[0]]
            : ['type' => 'MultiLineString', 'coordinates' => $parts];
    }

    private function polygonGeometryFromRawShpRecord(string $content): ?array
    {
        $rings = $this->rawShpParts($content, 35);

        if (empty($rings)) {
            return null;
        }

        return [
            'type' => count($rings) === 1 ? 'Polygon' : 'MultiPolygon',
            'coordinates' => count($rings) === 1
                ? [$rings[0]]
                : array_map(fn ($ring) => [$ring], $rings),
        ];
    }

    private function rawShpParts(string $content, int $maxPointsPerPart): array
    {
        if (strlen($content) < 44) {
            return [];
        }

        $numParts = $this->readLittleEndianInt($content, 36);
        $numPoints = $this->readLittleEndianInt($content, 40);

        if ($numParts <= 0 || $numPoints <= 0) {
            return [];
        }

        $partsOffset = 44;
        $pointsOffset = $partsOffset + ($numParts * 4);

        if (strlen($content) < $pointsOffset + ($numPoints * 16)) {
            return [];
        }

        $partStarts = [];

        for ($index = 0; $index < $numParts; $index++) {
            $partStarts[] = $this->readLittleEndianInt($content, $partsOffset + ($index * 4));
        }

        $partStarts[] = $numPoints;
        $parts = [];

        for ($partIndex = 0; $partIndex < $numParts; $partIndex++) {
            $start = $partStarts[$partIndex];
            $end = $partStarts[$partIndex + 1];
            $points = [];
            $pointCount = max(0, $end - $start);
            $step = max(1, (int) ceil($pointCount / max(1, $maxPointsPerPart)));

            for ($pointIndex = $start; $pointIndex < $end; $pointIndex += $step) {
                $pointOffset = $pointsOffset + ($pointIndex * 16);
                $points[] = [
                    round($this->readLittleEndianDouble($content, $pointOffset), 4),
                    round($this->readLittleEndianDouble($content, $pointOffset + 8), 4),
                ];
            }

            $lastPointIndex = $end - 1;

            if ($lastPointIndex >= $start) {
                $lastPointOffset = $pointsOffset + ($lastPointIndex * 16);
                $lastPoint = [
                    round($this->readLittleEndianDouble($content, $lastPointOffset), 4),
                    round($this->readLittleEndianDouble($content, $lastPointOffset + 8), 4),
                ];

                if (end($points) !== $lastPoint) {
                    $points[] = $lastPoint;
                }
            }

            if (!empty($points)) {
                $parts[] = $points;
            }
        }

        return $parts;
    }

    private function readLittleEndianInt(string $bytes, int $offset): int
    {
        return unpack('V', substr($bytes, $offset, 4))[1] ?? 0;
    }

    private function readLittleEndianDouble(string $bytes, int $offset): float
    {
        return unpack('d', substr($bytes, $offset, 8))[1] ?? 0.0;
    }

    private function compactRenderedFeature(array $feature, string $fileName, string $category): array
    {
        $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];

        $compactProperties = [
            '_source_file' => $fileName,
            '_category' => $category,
        ];

        foreach (['name', 'Name', 'NAMELSAD', 'ADM3_EN', 'MUNICIPALI', 'MUNICIPAL', 'layer', 'description'] as $key) {
            if (!empty($properties[$key]) && is_scalar($properties[$key])) {
                $compactProperties[$key] = (string) $properties[$key];
            }
        }

        return [
            'type' => 'Feature',
            'properties' => $compactProperties,
            'geometry' => $this->simplifyRenderedGeometry($feature['geometry'] ?? null, $category),
        ];
    }

    private function simplifyRenderedGeometry($geometry, string $category)
    {
        if (!is_array($geometry) || empty($geometry['type']) || !isset($geometry['coordinates'])) {
            return null;
        }

        return [
            'type' => $geometry['type'],
            'coordinates' => $this->simplifyCoordinateTree($geometry['coordinates'], (string) $geometry['type'], $category),
        ];
    }

    private function simplifyCoordinateTree($coordinates, string $geometryType, string $category = '')
    {
        if (!is_array($coordinates)) {
            return $coordinates;
        }

        if (count($coordinates) >= 2 && is_numeric($coordinates[0] ?? null) && is_numeric($coordinates[1] ?? null)) {
            return [
                round((float) $coordinates[0], 4),
                round((float) $coordinates[1], 4),
            ];
        }

        if ($this->isCoordinateRing($coordinates)) {
            $maxPoints = str_contains($geometryType, 'Polygon')
                ? ($category === 'irrigated' ? self::IRRIGATED_OVERVIEW_POLYGON_POINTS : self::DEFAULT_RENDER_POLYGON_POINTS)
                : self::DEFAULT_RENDER_LINE_POINTS;

            return $this->thinCoordinateRing($coordinates, $maxPoints);
        }

        return array_map(fn ($item) => $this->simplifyCoordinateTree($item, $geometryType, $category), $coordinates);
    }

    private function isCoordinateRing(array $coordinates): bool
    {
        return isset($coordinates[0], $coordinates[0][0], $coordinates[0][1])
            && is_numeric($coordinates[0][0])
            && is_numeric($coordinates[0][1]);
    }

    private function thinCoordinateRing(array $ring, int $maxPoints): array
    {
        $count = count($ring);

        if ($count <= $maxPoints) {
            return array_map(fn ($point) => $this->simplifyCoordinateTree($point, 'Point'), $ring);
        }

        $step = max(1, (int) ceil($count / $maxPoints));
        $thinned = [];

        for ($index = 0; $index < $count; $index += $step) {
            $thinned[] = $this->simplifyCoordinateTree($ring[$index], 'Point');
        }

        $lastPoint = $this->simplifyCoordinateTree($ring[$count - 1], 'Point');

        if (end($thinned) !== $lastPoint) {
            $thinned[] = $lastPoint;
        }

        return $thinned;
    }

    private function featuresFromZippedShapefile(string $path, string $fileName, string $category, ?int $maxFeatures = null): array
    {
        $extractPath = storage_path('app/temp/map_render_' . uniqid('', true));
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return [];
        }

        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'shp') {
                    return $this->featuresFromShapefile($file->getPathname(), $fileName, $category, $maxFeatures);
                }
            }

            return [];
        } finally {
            $this->deleteLocalDirectory($extractPath);
        }
    }

    private function deleteLocalDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($directory);
    }

    private function resolveOverlayFolder(string $label, string $directory): string
    {
        $disk = $this->mapDisk();
        $canonicalFolder = "maps/{$directory}";
        $legacyFolder = "maps/{$label}";

        if ($this->overlayPrefixHasAssets($disk, $canonicalFolder)) {
            return $canonicalFolder;
        }

        if ($this->overlayPrefixHasAssets($disk, $legacyFolder)) {
            return $legacyFolder;
        }

        return $canonicalFolder;
    }

    private function buildUploadTargets(): array
    {
        $targets = [];

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $root = $this->resolveOverlayFolder($category, $directory);
            $entries = [['value' => '', 'label' => 'Category root']];

            $disk = $this->mapDisk();

            if ($this->overlayPrefixHasAssets($disk, $root)) {
                $directories = collect($disk->allDirectories($root))
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

    private function overlayFilePayloadSize(string $path): int
    {
        $disk = $this->mapDisk();
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension !== 'shp') {
            return $disk->exists($path) ? (int) $disk->size($path) : 0;
        }

        $basePath = substr($path, 0, -4);
        $totalSize = 0;

        foreach (self::SHAPEFILE_COMPANION_EXTENSIONS as $companionExtension) {
            $companionPath = $basePath . '.' . $companionExtension;

            if ($disk->exists($companionPath)) {
                $totalSize += (int) $disk->size($companionPath);
            }
        }

        return $totalSize;
    }

    public function upload(Request $request)
    {
        try {
            @ini_set('memory_limit', '2048M');
            @set_time_limit(0);

            $request->validate([
                'category' => 'required|in:Irrigated Area,Pangasinan Land Boundary,Potential Irrigable Area',
                'files' => 'required',
                'files.*' => 'file|max:204800',
                'target_folder' => 'nullable|string|max:255',
            ]);

            $category = $request->category;
            $categoryDirectory = self::CATEGORY_DIRECTORY_MAP[$category] ?? $category;
            $paths = $request->input('paths', []);
            $targetFolder = $this->sanitizeRelativeFolder($request->input('target_folder', ''));
            $baseStoragePath = trim("maps/{$categoryDirectory}/{$targetFolder}", '/');
            $displayUploadDirectory = $this->displayMapDirectory($baseStoragePath, $category);
            $uploadedFiles = [];
            $shapefileBasenames = [];
            $skippedFiles = [];

            foreach ($request->file('files') as $index => $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $relativePath = $paths[$index] ?? $file->getClientOriginalName();
                $folderPath = $this->extractUploadSubfolder($relativePath);

                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = strtolower($file->getClientOriginalExtension());

                if (!in_array($extension, array_merge(self::PRIMARY_FILE_EXTENSIONS, self::SHAPEFILE_COMPANION_EXTENSIONS), true)) {
                    $skippedFiles[] = $file->getClientOriginalName();
                    continue;
                }

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

                $mapDisk = $this->mapDisk();
                $mapDisk->makeDirectory($storagePath);

                $diskName = (string) config('filesystems.maps_disk', 'public');
                $writeOptions = (config("filesystems.disks.{$diskName}.driver") === 's3')
                    ? ['visibility' => 'public']
                    : [];

                $path = $mapDisk->putFileAs($storagePath, $file, $finalName, $writeOptions);
                //$path = $file->store('forms');
                if ($path) {
                    $uploadedFiles[] = [
                        'name' => $finalName,
                        'path' => $path,
                        'url' => $this->mapFileUrl($path),
                    ];
                }
            }

            $primaryUploadedFiles = array_values(array_filter($uploadedFiles, function ($file) {
                $extension = strtolower(pathinfo((string) ($file['path'] ?? ''), PATHINFO_EXTENSION));

                return in_array($extension, self::PRIMARY_FILE_EXTENSIONS, true);
            }));
            $dbImportDeferred = false;

            if ($categoryDirectory === 'irrigated' && count($primaryUploadedFiles) <= self::SYNC_IRRIGATED_IMPORT_FILE_LIMIT) {
                $this->importUploadedIrrigatedAreaFiles($uploadedFiles);
            } elseif ($categoryDirectory === 'irrigated' && count($primaryUploadedFiles) > self::SYNC_IRRIGATED_IMPORT_FILE_LIMIT) {
                $dbImportDeferred = true;
            }

            $this->clearMapDataCache();
            $notificationResult = $this->notifyMapFileChange(
                'upload',
                $category,
                $uploadedFiles
            );

            return response()->json([
                'message' => $this->uploadSuccessMessage($displayUploadDirectory, $notificationResult['admin_message'], $skippedFiles, $dbImportDeferred),
                'files' => $uploadedFiles,
                'skipped_files' => $skippedFiles,
                'db_import_deferred' => $dbImportDeferred,
                'target_folder' => $targetFolder,
                'target_directory' => $displayUploadDirectory,
                'notified_users_count' => $notificationResult['notified_users_count'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function uploadSuccessMessage(string $displayUploadDirectory, string $adminMessage, array $skippedFiles, bool $dbImportDeferred): string
    {
        $message = "Upload successful to {$displayUploadDirectory}. {$adminMessage}";

        if (!empty($skippedFiles)) {
            $message .= ' Skipped ' . count($skippedFiles) . ' unsupported file(s).';
        }

        if ($dbImportDeferred) {
            $message .= ' Large irrigated upload saved. Run php artisan map:rebuild-irrigated-areas to index it for map display.';
        }

        return $message;
    }

    private function importUploadedIrrigatedAreaFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $file) {
            $path = (string) ($file['path'] ?? '');
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (!in_array($extension, self::PRIMARY_FILE_EXTENSIONS, true)) {
                continue;
            }

            if ($this->shouldSkipIrrigatedPath($path)) {
                continue;
            }

            $this->importIrrigatedAreaFile([
                'path' => $path,
                'name' => $file['name'] ?? basename($path),
                'url' => $file['url'] ?? $this->mapFileUrl($path),
            ]);
        }
    }

    private function importIrrigatedAreaFile(array $file): array
    {
        $fileUrl = (string) ($file['url'] ?? '');
        $fileName = (string) ($file['name'] ?? 'Unknown file');
        $result = [
            'files_imported' => 0,
            'features_imported' => 0,
            'failed_files' => [],
        ];

        try {
            $explicitKey = $this->normalizePublicStoragePath((string) ($file['path'] ?? ''));
            $canonicalKey = $explicitKey !== ''
                ? $explicitKey
                : $this->mapStorageKeyFromUrl($fileUrl);

            if ($canonicalKey === '') {
                throw new \RuntimeException('Unable to resolve map storage key for database import.');
            }

            IrrigatedArea::query()->where('source_path', $canonicalKey)->delete();

            $localPath = $this->localPathForNormalizedMapStorageKey($canonicalKey);
            $features = $this->featuresFromMapLocalPath($localPath, $fileName, 'irrigated');
            $rows = [];

            foreach ($features as $featureIndex => $feature) {
                $compactFeature = $this->compactRenderedFeature($feature, $fileName, 'irrigated');
                $geometry = $compactFeature['geometry'] ?? null;

                if (!is_array($geometry)) {
                    continue;
                }

                $bounds = $this->geometryBounds($geometry);

                if (!$bounds || !$this->boundsLookLikeWgs84($bounds)) {
                    continue;
                }

                $rows[] = [
                    'source_path' => $canonicalKey,
                    'source_file' => $fileName,
                    'source_hash' => hash('sha256', $canonicalKey.'|'.$featureIndex),
                    'feature_index' => $featureIndex,
                    'min_lat' => $bounds['min_lat'],
                    'max_lat' => $bounds['max_lat'],
                    'min_lng' => $bounds['min_lng'],
                    'max_lng' => $bounds['max_lng'],
                    'properties_json' => json_encode($compactFeature['properties'] ?? ['_category' => 'irrigated']),
                    'geometry_json' => json_encode($geometry),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($rows) >= 500) {
                    DB::table('irrigated_areas')->insert($rows);
                    $result['features_imported'] += count($rows);
                    $rows = [];
                }
            }

            if ($rows) {
                DB::table('irrigated_areas')->insert($rows);
                $result['features_imported'] += count($rows);
            }

            $result['files_imported'] = 1;
        } catch (\Throwable $exception) {
            $result['failed_files'][] = [
                'name' => $fileName,
                'message' => $exception->getMessage(),
            ];
        }

        return $result;
    }

    private function deleteIrrigatedAreaRowsForPath(string $path): void
    {
        $path = $this->normalizePublicStoragePath($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, self::SHAPEFILE_COMPANION_EXTENSIONS, true)) {
            $path = substr($path, 0, -strlen($extension)) . 'shp';
        }

        IrrigatedArea::query()->where('source_path', $path)->delete();
    }

    private function deleteIrrigatedAreaRowsForFolder(string $folder): void
    {
        $folder = trim($this->normalizePublicStoragePath($folder), '/');

        if ($folder === '') {
            return;
        }

        IrrigatedArea::query()
            ->where('source_path', 'like', $folder . '/%')
            ->delete();
    }

    public function fileManager()
    {
        $filesData = [];
        $foldersData = [];

        $disk = $this->mapDisk();

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $folder = $this->resolveOverlayFolder($category, $directory);

            if (!$this->overlayPrefixHasAssets($disk, $folder)) {
                continue;
            }

            $files = collect($disk->allFiles($folder));
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
        $mapDisk = $this->mapDisk();
        $legacyDisk = Storage::disk('public');

        if ($mapDisk->exists($path)) {
            $mapDisk->delete($path);
        } elseif ($legacyDisk->exists($path)) {
            $legacyDisk->delete($path);
        } else {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        $this->deleteIrrigatedAreaRowsForPath($path);
        $category = $this->resolveCategoryFromStoragePath((string) $path);
        $notificationResult = $this->notifyMapFileChange('delete', $category, [[
            'name' => basename((string) $path),
            'path' => (string) $path,
            'url' => $this->mapFileUrl((string) $path),
        ]]);

        $this->clearMapDataCache();

        return response()->json([
            'message' => 'Deleted. '.$notificationResult['admin_message'],
            'notified_users_count' => $notificationResult['notified_users_count'],
        ]);
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

        $disk = $this->mapDisk();

        if (count($disk->allFiles($folder)) === 0) {
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
        $this->deleteIrrigatedAreaRowsForFolder($folder);
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

    public function serveMapFile(Request $request, string $path)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        $path = $this->normalizePublicStoragePath($path);
        $disk = $this->mapDisk();

        if ($disk->exists($path)) {
            return $disk->response($path, basename($path), [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $legacy = Storage::disk('public');
        if ($legacy->exists($path)) {
            return response()->file($legacy->path($path), [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        abort(404);
    }

    public function mapNotifications(Request $request)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
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

     public function getIrrigatedChartData(Request $request)
    {
        if ($denied = $this->denyMapAccessUnlessAllowed($request)) {
            return $denied;
        }

        $payload = Cache::store('file')->rememberForever($this->irrigatedChartCacheKey(), function () {
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
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=86400');
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
                $municipalityFromFolder = $this->guessMunicipalityFromPath($path, $municipalityDetails);

                if ($municipalityFromFolder) {
                    return $municipalityFromFolder;
                }

                if ($this->shouldSkipIrrigatedPath($path)) {
                    return null;
                }

                $recordName = (string) ($record['layer'] ?? $record['name'] ?? '');

                return $this->guessMunicipalityFromText($recordName . ' ' . $path, $municipalityDetails);
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

        return str_contains($normalizedPath, '/!pia')
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
        $storageAppRoot = str_replace('\\', '/', storage_path('app/public')) . '/';
        $storageRoot = str_replace('\\', '/', public_path('storage')) . '/';
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with($normalizedPath, $storageAppRoot)) {
            return substr($normalizedPath, strlen($storageAppRoot));
        }

        if (str_starts_with($normalizedPath, $storageRoot)) {
            return substr($normalizedPath, strlen($storageRoot));
        }

        return $normalizedPath;
    }

    private function clearMapDataCache(): void
    {
        Cache::store('file')->forget($this->irrigatedChartCacheKey());
        Cache::store('file')->forget(self::MAP_API_CACHE_KEY);
        $version = $this->bumpMapApiVersion();
        $this->writeMapApiSignature('manual:' . ($version['version'] ?? uniqid('', true)), $version);
    }

    private function refreshMapDataCacheIfFilesystemChanged(): void
    {
        $signature = $this->currentMapDataSignature();
        $stored = $this->readMapApiSignature();

        if (($stored['signature'] ?? null) === $signature) {
            return;
        }

        Cache::store('file')->forget($this->irrigatedChartCacheKey());
        Cache::store('file')->forget(self::MAP_API_CACHE_KEY);
        $version = $this->bumpMapApiVersion();
        $this->writeMapApiSignature($signature, $version);
    }

    private function currentMapDataSignature(): string
    {
        $disk = $this->mapDisk();
        $entries = [];

        foreach (self::CATEGORY_DIRECTORY_MAP as $category => $directory) {
            $root = $this->resolveOverlayFolder($category, $directory);

            if (!$this->overlayPrefixHasAssets($disk, $root)) {
                $entries[] = "{$root}|missing";
                continue;
            }

            foreach ($disk->allFiles($root) as $path) {
                $entries[] = implode('|', [
                    $path,
                    $disk->size($path),
                    $disk->lastModified($path),
                ]);
            }
        }

        $detailsPath = public_path(self::MUNICIPALITY_DETAILS_PATH);

        if (is_file($detailsPath)) {
            $entries[] = implode('|', [
                self::MUNICIPALITY_DETAILS_PATH,
                filesize($detailsPath) ?: 0,
                filemtime($detailsPath) ?: 0,
            ]);
        }

        sort($entries, SORT_STRING);

        return sha1(implode("\n", $entries));
    }

    private function readMapApiSignature(): array
    {
        $filePath = storage_path('app/' . self::MAP_API_SIGNATURE_FILE);

        if (!file_exists($filePath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($filePath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeMapApiSignature(string $signature, array $version): void
    {
        file_put_contents(
            storage_path('app/' . self::MAP_API_SIGNATURE_FILE),
            json_encode([
                'signature' => $signature,
                'version' => $version['version'] ?? null,
                'updated_at' => $version['updated_at'] ?? now()->toIso8601String(),
            ], JSON_PRETTY_PRINT)
        );
    }

    private function readMapApiVersion(): array
    {
        $filePath = storage_path('app/' . self::MAP_API_VERSION_FILE);

        if (file_exists($filePath)) {
            $decoded = json_decode((string) file_get_contents($filePath), true);

            if (is_array($decoded) && !empty($decoded['version']) && !empty($decoded['updated_at'])) {
                return [
                    'version' => (string) $decoded['version'],
                    'updated_at' => (string) $decoded['updated_at'],
                ];
            }
        }

        return $this->bumpMapApiVersion();
    }

    private function bumpMapApiVersion(): array
    {
        $version = [
            'version' => uniqid('map_api_', true),
            'updated_at' => now()->toIso8601String(),
        ];

        file_put_contents(
            storage_path('app/' . self::MAP_API_VERSION_FILE),
            json_encode($version, JSON_PRETTY_PRINT)
        );

        return $version;
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
        $locations = array_values(array_unique(array_map(function ($file) use ($category) {
            $path = (string) ($file['path'] ?? '');
            return $this->displayMapDirectory(dirname($path), $category);
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

        $notifiedUsers = 0;
        $adminMessage = 'Teams and admins have been notified.';

        if (Auth::check() && Auth::user() instanceof User) {
            $actorUser = Auth::user();
            $actorLabel = $this->notifications()->actorLabel($actorUser);
            $locationSummary = implode(', ', array_slice($locations, 0, 3));

            if (count($locations) > 3) {
                $locationSummary .= ', and more';
            }

            if ($locationSummary === '') {
                $locationSummary = $this->displayMapDirectory('maps', $category);
            }

            $title = $action === 'delete' ? 'Map file removed' : 'Map file uploaded';
            $fileCount = count($files);
            $fileLabel = $fileCount === 1 ? 'file' : 'files';
            $actionText = $action === 'delete' ? 'removed' : 'uploaded';
            $message = "{$actorLabel} {$actionText} {$fileCount} map {$fileLabel} in {$locationSummary}.";

            $this->notifications()->notifyAgency($actorUser, $title, $message, [
                'type' => 'map_file',
                'team' => 'all',
                'team_label' => $this->notifications()->teamLabel('all'),
                'map_category' => $category,
                'map_locations' => $locations,
                'map_files' => array_map(function ($file) {
                    return [
                        'name' => (string) ($file['name'] ?? ''),
                        'path' => (string) ($file['path'] ?? ''),
                    ];
                }, $files),
            ]);

            $notifiedUsers = User::query()
                ->where('is_active', true)
                ->where('id', '!=', $actorUser->getKey())
                ->count();
        } else {
            $notifiedUsers = User::query()
                ->where('is_active', true)
                ->count();
        }

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
        $path = $this->normalizePublicStoragePath($path);

        if ($this->mapDiskUsesDirectPublicUrls()) {
            return $this->mapDisk()->url($path);
        }

        $segments = array_map('rawurlencode', explode('/', $path));

        return url('/map/file/'.implode('/', $segments));
    }

    private function normalizePublicStoragePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);

        foreach (['/storage/app/public/', '/public/storage/'] as $storageMarker) {
            $markerPosition = stripos($path, $storageMarker);

            if ($markerPosition !== false) {
                $path = substr($path, $markerPosition + strlen($storageMarker));
                break;
            }
        }

        $path = preg_replace('#^/?storage/#', '', $path);
        $path = trim($path, '/');

        $parts = array_filter(explode('/', $path), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        return implode('/', $parts);
    }

    private function irrigatedChartCacheKey(): string
    {
        return self::IRRIGATED_CHART_CACHE_KEY;
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

    private function displayMapDirectory(string $path, string $category): string
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        if ($normalized === '' || $normalized === '.') {
            return "maps/{$category}";
        }

        $segments = explode('/', $normalized);

        if (($segments[0] ?? '') !== 'maps') {
            array_unshift($segments, 'maps');
        }

        if (isset($segments[1])) {
            $segments[1] = $category;
        } else {
            $segments[] = $category;
        }

        return implode('/', $segments);
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
        $disk = $this->mapDisk();
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
        $disk = $this->mapDisk();
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
