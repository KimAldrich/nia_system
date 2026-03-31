<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Fields that are safe to save to the database
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'agreed_to_terms',
        'team_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'agreed_to_terms' => 'boolean', // Ensures this is treated as true/false
            'is_active' => 'boolean',
        ];
    }

    // A user belongs to one team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}