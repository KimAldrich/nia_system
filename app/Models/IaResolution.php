<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

class IaResolution extends Model
{
    protected $fillable = ['title', 'file_path', 'original_name', 'status', 'team'];

    public function files(): HasMany
    {
        return $this->hasMany(IaResolutionFile::class)->latest();
    }

    public static function cleanedTitleFromFileName(string $originalName): string
    {
        $rawName = pathinfo($originalName, PATHINFO_FILENAME);

        return ucwords(str_replace(['_', '-'], ' ', $rawName));
    }

    public static function attachUploadedFile(UploadedFile $file, string $team): self
    {
        $path = $file->store('resolutions', 'public');
        $title = self::cleanedTitleFromFileName($file->getClientOriginalName());

        $resolution = self::firstOrCreate(
            [
                'title' => $title,
                'team' => $team,
            ],
            [
                'status' => self::STATUS_PENDING,
            ]
        );

        $resolution->files()->create([
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        $resolution->refreshPrimaryAttachment();

        return $resolution->fresh('files');
    }

    public function refreshPrimaryAttachment(): void
    {
        $latestFile = $this->files()->latest()->first();

        $this->forceFill([
            'file_path' => $latestFile?->file_path,
            'original_name' => $latestFile?->original_name,
        ])->save();
    }

    public const STATUS_PENDING = 'not-validated';
    public const STATUS_ONGOING = 'on-going';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_ACCOMPLISHED = 'accomplished';

    public static function completedStatusValueForTeam(?string $team): string
    {
        return $team === 'fs_team'
            ? self::STATUS_VALIDATED
            : self::STATUS_ACCOMPLISHED;
    }

    public static function completedStatusLabelForTeam(?string $team): string
    {
        return $team === 'fs_team'
            ? 'Validated'
            : 'Accomplished';
    }

    public static function pendingStatusLabelForTeam(?string $team): string
    {
        return $team === 'fs_team'
            ? 'Not Validated'
            : 'Not Accomplished';
    }

    public static function normalizeStatusForTeam(?string $status, ?string $team): ?string
    {
        if ($status === null) {
            return null;
        }

        return match ($status) {
            self::STATUS_VALIDATED, self::STATUS_ACCOMPLISHED => self::completedStatusValueForTeam($team),
            default => $status,
        };
    }

    public static function isCompletedStatus(?string $status): bool
    {
        return in_array($status, [self::STATUS_VALIDATED, self::STATUS_ACCOMPLISHED], true);
    }

    public static function displayStatusLabel(?string $status, ?string $team): string
    {
        if (self::isCompletedStatus($status)) {
            return self::completedStatusLabelForTeam($team);
        }

        return match ($status) {
            self::STATUS_ONGOING => 'On-Going',
            default => self::pendingStatusLabelForTeam($team),
        };
    }

    public static function isPendingStatus(?string $status): bool
    {
        return !self::isCompletedStatus($status) && $status !== self::STATUS_ONGOING;
    }
}
