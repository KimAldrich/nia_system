<?php

namespace App\Models;

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
}
