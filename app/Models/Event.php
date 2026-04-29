<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'event_category_id',
        'team',
        'reminder_minutes',
        'recurrence_pattern',
        'recurrence_until',
        'recurrence_group',
    ];

    protected $casts = [
        'event_date' => 'date',
        'recurrence_until' => 'date',
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

        $rawEventTime = trim((string) $this->event_time);
        $normalizedRange = str_replace(['–', '—'], '-', $rawEventTime);
        $timeRange = preg_split('/\s*-\s*/', $normalizedRange);
        $endTime = trim((string) end($timeRange));
        $normalizedEndTime = preg_replace('/\s+/', ' ', str_replace([';', '.'], ':', $endTime));

        foreach (['g:i A', 'g:i a', 'h:i A', 'h:i a', 'g:iA', 'g:ia', 'h:iA', 'h:ia', 'H:i', 'G:i'] as $format) {
            try {
                $parsedTime = Carbon::createFromFormat($format, $normalizedEndTime);

                return $eventDate->copy()->setTime($parsedTime->hour, $parsedTime->minute, 0);
            } catch (\Throwable $e) {
                // Try the next supported time format.
            }
        }

        if (preg_match('/^(?<hour>\d{1,2}):(?<minute>\d{2})$/', $normalizedEndTime, $matches)) {
            $hour = (int) $matches['hour'];
            $minute = (int) $matches['minute'];

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return $eventDate->copy()->setTime($hour, $minute, 0);
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
