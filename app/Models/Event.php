<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'event_date', 'event_time', 'tag'];

    protected $casts = [
        'event_date' => 'date', // This lets us use ->format('M') in Blade
    ];
}