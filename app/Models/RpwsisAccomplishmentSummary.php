<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RpwsisAccomplishmentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'region', 'province', 'municipality', 'barangay', 
        'plantation_type', 'year_established', 'target_area_1', 'area_planted', 
        'species_planted', 'spacing', 'maintenance', 'target_area_2', 
        'actual_area', 'mortality_rate', 'species_replanted', 'nis_name', 'remarks'
    ];
}
