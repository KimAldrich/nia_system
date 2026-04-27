<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementProject extends Model
{
    use HasFactory;

    // 🌟 THIS IS REQUIRED! If this is missing, Laravel will quietly reject your form submission 🌟
    protected $guarded = [];
}
