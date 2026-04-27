<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    use MassPrunable;

    private const MAX_ENTRIES = 100;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'route_name',
        'method',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::created(function (): void {
            static::pruneToLatestLimit();
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(2));
    }

    public static function pruneToLatestLimit(): void
    {
        $staleIds = static::query()
            ->latest('id')
            ->skip(self::MAX_ENTRIES)
            ->pluck('id');

        if ($staleIds->isEmpty()) {
            return;
        }

        static::query()->whereIn('id', $staleIds)->delete();
    }
}
