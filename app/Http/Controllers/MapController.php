<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function Showmap()
    {
        $overlayGroups = [
            'Irrigated Area' => $this->buildOverlayGroup('Irrigated Area', 'Irrigated Area'),
            'Pangasinan Land Boundary' => $this->buildOverlayGroup('Pangasinan Land Boundary', 'Pangasinan Land Boundary'),
            'Potential Irrigable Area' => $this->buildOverlayGroup('Potential Irrigable Area', 'Potential Irrigable Area'),
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
}
