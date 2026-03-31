<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeDocument extends Model
{
    protected $fillable = [
        'title',
        'document_type',
        'file_path',
        'original_name',
        'user_id',
        'team_role'
    ];
}