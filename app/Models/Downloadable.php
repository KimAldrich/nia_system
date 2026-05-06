<?php

namespace App\Models;

use App\Services\DocumentStorageService;
use Illuminate\Database\Eloquent\Model;

class Downloadable extends Model
{
    protected $fillable = ['title', 'file_path', 'original_name', 'team'];

    public function getFileUrlAttribute(): string
    {
        return app(DocumentStorageService::class)->url($this->file_path);
    }

    public function getPreviewUrlAttribute(): string
    {
        return app(DocumentStorageService::class)->previewUrl($this->file_path);
    }
}
