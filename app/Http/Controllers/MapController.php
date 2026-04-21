<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Shapefile\ShapefileReader;
use ZipArchive;

class MapController extends Controller
{
    public function Showmap()
    {
        $overlayGroups = [
            'irrigated' => $this->buildOverlayGroup('Irrigated Area', 'irrigated'),
            'land_boundary' => $this->buildOverlayGroup('Pangasinan Land Boundary', 'land_boundary'),
            'potential' => $this->buildOverlayGroup('Potential Irrigable Area', 'potential'),
        ];

        return view('map.map', compact('overlayGroups'));
    }

    // ================= MAP FILE LOADER =================
    private function buildOverlayGroup(string $label, string $directory): array
    {
        $disk = Storage::disk('public');
        $folder = "maps/{$directory}";

        if (!$disk->exists($folder)) {
            return [
                'label' => $label,
                'files' => [],
            ];
        }

         $files = collect(Storage::disk('public')->allFiles($folder)) // ✅ read all file formats
            ->map(function ($path) use ($directory) {
                return [
                    'name' => basename($path),
                    'url' => Storage::url($path),
                    'folder' => str_replace("maps/{$directory}/", '', dirname($path))
                ];
            })
            ->values()
            ->all();

        return [
            'label' => $label,
            'files' => $files,
        ];
    }

    // ================= UPLOAD =================
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|in:Irrigated Area,Pangasinan Land Boundary,Potential Irrigable Area',
                'files' => 'required',
                'files.*' => 'file|max:51200'
            ]);

            $category = $request->category;
            $paths = $request->input('paths', []); // optional (for folder upload)

            $uploadedFiles = [];

            foreach ($request->file('files') as $index => $file) {

                if (!$file->isValid()) continue;

                // 📂 handle folder structure
                $relativePath = $paths[$index] ?? $file->getClientOriginalName();

                $folderPath = dirname($relativePath);
                $folderPath = $folderPath === '.' ? '' : $folderPath;

                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();

                $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $baseName);
                $finalName = $safeName . '_' . uniqid() . '.' . $ext;

                $storagePath = "maps/{$category}/{$folderPath}";

                // ✅ ensure directory exists
                Storage::disk('public')->makeDirectory($storagePath);

                $path = Storage::disk('public')->putFileAs(
                    $storagePath,
                    $file,
                    $finalName
                );

                if ($path) {
                    $uploadedFiles[] = [
                        'name' => $finalName,
                        'path' => $path,
                        'url' => Storage::url($path)
                    ];
                }
            }

            return response()->json([
                'message' => 'Upload successful',
                'files' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

  public function fileManager()
{
    $categories = ['Irrigated Area', 'Pangasinan Land Boundary', 'Potential Irrigable Area'];
    $filesData = [];

    foreach ($categories as $category) {

        $folder = "maps/" . $category;

        if (!Storage::disk('public')->exists($folder)) {
            continue;
        }

        // ✅ GET ALL FILES (NO FILTER)
        $files = collect(Storage::disk('public')->allFiles($folder));

        foreach ($files as $file) {
            $filesData[] = [
                'name' => basename($file),
                'category' => $category,
                'url' => Storage::url($file),
                'path' => $file,
                'folder' => dirname($file)
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
                'message' => 'Deleted'
            ]);
        }

        return response()->json([
            'message' => 'File not found'
        ], 404);
    }

private function readShapefileZip($zipPath)
{
    $fullPath = storage_path('app/public/' . $zipPath);

    if (!file_exists($fullPath)) return 0;

    $extractPath = storage_path('app/temp/' . uniqid());
    mkdir($extractPath, 0777, true);

    $zip = new ZipArchive;

    if ($zip->open($fullPath) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
    } else {
        return 0;
    }

    // find shp
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

    if (!$shpFile) return 0;

    try {
        $reader = new ShapefileReader($shpFile);
        $reader->setCharset('CP1252');

        $totalArea = 0;

        while ($record = $reader->fetchRecord()) {

            if ($record->isDeleted()) continue;

            $data = $record->getDataArray();

            $area = 0;

            foreach ($data as $key => $value) {

                $cleanKey = strtoupper(trim($key));

                if (
                    $cleanKey === 'AREA__HA_' ||
                    str_contains($cleanKey, 'AREA')
                ) {
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
    //time out for large file processing
    set_time_limit(300);
    ini_set('max_execution_time', 300);

    $basePath = 'maps/irrigated';
    $disk = Storage::disk('public');

    $municipalities = collect($disk->directories($basePath));

    $chartData = [];

    foreach ($municipalities as $municipalityPath) {

        $municipality = basename($municipalityPath);

        $ranges = $disk->directories($municipalityPath);

        $rangeAreas = [];
        $slopesTotal = 0;

        foreach ($ranges as $rangePath) {

            $rangeName = basename($rangePath);
            $files = $disk->files($rangePath);

            $rangeTotal = 0;

            foreach ($files as $file) {
                if (str_ends_with($file, '.zip')) {
                    $rangeTotal += $this->readShapefileZip($file);
                }
            }

            $rangeAreas[$rangeName] = $rangeTotal;
            $slopesTotal += $rangeTotal;
        }

        // ✅ Get real total land area
        $totalLandArea = $this->getMunicipalityLandArea($municipality);

        $percentages = [];

        foreach ($rangeAreas as $range => $area) {
            $percentages[$range] = $totalLandArea > 0
                ? round(($area / $totalLandArea) * 100, 2)
                : 0;
        }

$totalIrrigated = array_sum($rangeAreas);

$chartData[$municipality] = [
    'Area (ha)'        => $totalLandArea,
    'irrigated_total'  => round($totalIrrigated, 2),
    'ranges'           => $rangeAreas,
    'percentages'      => $percentages
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

        // normalize city names
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

    // convert square meters to hectares
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
