<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'status',
        'team_id',
    ];

    // A project belongs to one team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}