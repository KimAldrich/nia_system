<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'event_date', 'event_time', 'event_category_id'];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function getEndDateTime(): Carbon
    {
        $eventDate = $this->event_date instanceof Carbon
            ? $this->event_date->copy()
            : Carbon::parse($this->event_date);

        if (blank($this->event_time)) {
            return $eventDate->endOfDay();
        }

        $timeRange = preg_split('/\s*-\s*/', (string) $this->event_time);
        $endTime = trim((string) end($timeRange));

        foreach (['g:i A', 'g:i a', 'h:i A', 'h:i a', 'H:i'] as $format) {
            try {
                $parsedTime = Carbon::createFromFormat($format, $endTime);

                return $eventDate->copy()->setTime($parsedTime->hour, $parsedTime->minute, 0);
            } catch (\Throwable $e) {
                // Try the next supported time format.
            }
        }

        return $eventDate->endOfDay();
    }

    public function hasEnded(): bool
    {
        return $this->getEndDateTime()->lt(now());
    }

    public function isUpcoming(): bool
    {
        return ! $this->hasEnded();
    }
}
