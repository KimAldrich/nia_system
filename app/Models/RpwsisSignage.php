<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RpwsisSignage extends Model
{
    use HasFactory;

    protected $fillable = [
        'region', 'province', 'municipality', 'barangay', 
        'x_coordinates', 'y_coordinates', 'signage_type', 
        'nis_name', 'remarks'
    ];
}
