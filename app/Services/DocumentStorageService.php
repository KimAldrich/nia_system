<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentStorageService
{
    public function diskName(): string
    {
        return (string) config('filesystems.documents_disk', 'public');
    }

    public function store(UploadedFile $file, string $directory): string
    {
        $diskName = $this->diskName();
        $options = ['disk' => $diskName];

        if (config("filesystems.disks.{$diskName}.driver") === 's3') {
            $options['visibility'] = 'public';
        }

        return $file->store(trim($directory, '/'), $options);
    }

    public function delete(?string $path): void
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return;
        }

        foreach (array_unique([$this->diskName(), 'public']) as $diskName) {
            try {
                $disk = Storage::disk($diskName);

                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    public function url(?string $path): string
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return '#';
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $diskNames = array_unique([$this->diskName(), 'public']);

        foreach ($diskNames as $diskName) {
            try {
                $disk = Storage::disk($diskName);

                if ($disk->exists($path)) {
                    return $disk->url($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        if (config("filesystems.disks.{$this->diskName()}.driver") === 's3') {
            return Storage::disk($this->diskName())->url($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function previewUrl(?string $path): string
    {
        $url = $this->url($path);

        return $url === '#' || str_contains($url, '#')
            ? $url
            : $url . '#page=1&view=Fit&toolbar=0&navpanes=0';
    }

    private function normalizePath(?string $path): string
    {
        return trim(str_replace('\\', '/', (string) $path), '/');
    }
}
