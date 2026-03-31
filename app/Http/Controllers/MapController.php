<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class MapController extends Controller
{
    public function Showmap()
    {
        $overlayGroups = [
            'irrigated' => $this->buildOverlayGroup('Irrigated Area', 'Irrigated Area'),
            'land_boundary' => $this->buildOverlayGroup('Land Boundary', 'Pangasinan Land Boundary'),
            'potential' => $this->buildOverlayGroup('Potential Irrigable Area', 'Potential Irrigable Area'),
        ];

        return view('map.map', compact('overlayGroups'));
    }

    private function buildOverlayGroup(string $label, string $directory): array
    {
        $disk = Storage::disk('public');
        $folder = 'maps/' . $directory;
        $supportedExtensions = ['geojson', 'json', 'kml', 'kmz', 'shp'];
        $extensionPriority = array_flip($supportedExtensions);

        if (!$disk->exists($folder)) {
            return [
                'label' => $label,
                'files' => [],
            ];
        }

        $files = collect($disk->allFiles($folder))
            ->filter(function (string $path) use ($supportedExtensions) {
                return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $supportedExtensions, true);
            })
            ->groupBy(function (string $path) {
                return preg_replace('/\.[^.]+$/', '', $path);
            })
            ->map(function ($paths) use ($extensionPriority) {
                return $paths->sortBy(function (string $path) use ($extensionPriority) {
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    return $extensionPriority[$extension] ?? PHP_INT_MAX;
                })->first();
            })
            ->filter()
            ->sort()
            ->values()
            ->map(function (string $path) {
                return [
                    'name' => basename($path),
                    'url' => Storage::url($path),
                ];
            })
            ->all();

        return [
            'label' => $label,
            'files' => $files,
        ];
    }
}
