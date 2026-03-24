<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
    ];

    // A team has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // A team has many projects
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}