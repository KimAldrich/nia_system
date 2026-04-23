<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Shapefile\ShapefileReader;
use ZipArchive;

class MapController extends Controller
{
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

        return view('map.map', compact('overlayGroups'));
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
            ]);

            $category = $request->category;
            $categoryDirectory = self::CATEGORY_DIRECTORY_MAP[$category] ?? $category;
            $paths = $request->input('paths', []);
            $uploadedFiles = [];
            $shapefileBasenames = [];

            foreach ($request->file('files') as $index => $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $relativePath = $paths[$index] ?? $file->getClientOriginalName();
                $folderPath = dirname($relativePath);
                $folderPath = $folderPath === '.' ? '' : $folderPath;

                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = strtolower($file->getClientOriginalExtension());
                $safeBaseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName);
                $basenameKey = strtolower(trim($folderPath . '/' . $baseName, '/'));

                if (in_array($extension, self::SHAPEFILE_COMPANION_EXTENSIONS, true)) {
                    if (!isset($shapefileBasenames[$basenameKey])) {
                        $shapefileBasenames[$basenameKey] = $safeBaseName . '_' . uniqid();
                    }

                    $finalName = $shapefileBasenames[$basenameKey] . '.' . $extension;
                } else {
                    $finalName = $safeBaseName . '_' . uniqid() . '.' . $extension;
                }

                $storagePath = "maps/{$categoryDirectory}/{$folderPath}";
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

            return response()->json([
                'message' => 'Upload successful',
                'files' => $uploadedFiles,
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
        $jsonPath = public_path('maps/details.json');

        if (!file_exists($jsonPath)) {
            return response()->json([
                'error' => 'details.json not found',
            ], 404);
        }

        $rows = json_decode(file_get_contents($jsonPath), true);

        if (!$rows) {
            return response()->json([
                'error' => 'Invalid JSON',
            ], 500);
        }

        $chartData = [];

        foreach ($rows as $row) {
            $name = $row['name'];
            $totalLand = (float) $row['total_land_area_ha'];
            $irrigated = (float) $row['area_developed_ha'];
            $remaining = (float) $row['remaining_area_ha'];
            $program = (float) $row['program_area_ha'];
            $potential = (float) $row['potential_irrigation_area_ha'];

            $ranges = [
                'Irrigated Area' => $irrigated,
                'Remaining Area' => $remaining,
                'Program Area' => $program,
                'Potential Irrigation Area' => $potential,
            ];

            $percentages = [];

            foreach ($ranges as $rangeLabel => $value) {
                $percentages[$rangeLabel] = $totalLand > 0
                    ? round(($value / $totalLand) * 100, 2)
                    : 0;
            }

            $chartData[$name] = [
                'Area (ha)' => $totalLand,
                'irrigated_total' => $irrigated,
                'ranges' => $ranges,
                'percentages' => $percentages,
            ];
        }

        return response()->json($chartData);
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
