<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Downloadable extends Model
{
    protected $fillable = ['title', 'file_path', 'original_name', 'team'];
}