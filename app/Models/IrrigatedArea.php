<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrrigatedArea extends Model
{
    protected $fillable = [
        'source_path',
        'source_file',
        'source_hash',
        'feature_index',
        'min_lat',
        'max_lat',
        'min_lng',
        'max_lng',
        'properties_json',
        'geometry_json',
    ];
}
