<?php

namespace App\Models;

use App\Services\DocumentStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaResolutionFile extends Model
{
    protected $fillable = [
        'ia_resolution_id',
        'file_path',
        'original_name',
    ];

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(IaResolution::class, 'ia_resolution_id');
    }

    public function getFileUrlAttribute(): string
    {
        return app(DocumentStorageService::class)->url($this->file_path);
    }

    public function getPreviewUrlAttribute(): string
    {
        return app(DocumentStorageService::class)->previewUrl($this->file_path);
    }
}
