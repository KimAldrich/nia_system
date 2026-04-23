<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RpwsisNurseryEstablishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'region', 'province', 'municipality', 'barangay', 
        'x_coordinates', 'y_coordinates', 'seedlings_produced', 
        'nursery_type', 'nis_name', 'remarks'
    ];
}
