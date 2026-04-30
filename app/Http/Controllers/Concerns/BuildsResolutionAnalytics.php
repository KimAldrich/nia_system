<?php

namespace App\Http\Controllers\Concerns;

use App\Models\IaResolution;
use Illuminate\Support\Carbon;

trait BuildsResolutionAnalytics
{
    protected function buildResolutionAnalytics(?string $team = null): array
    {
        $weekStartDate = Carbon::now()->startOfDay()->subDays(6);
        $weekEndDate = Carbon::now()->endOfDay();
        $monthStartDate = Carbon::now()->startOfMonth()->subMonths(5);
        $monthEndDate = Carbon::now()->endOfMonth();

        $baseQuery = IaResolution::query();
        if ($team !== null) {
            $baseQuery->where('team', $team);
        }

        $weeklyResolutions = (clone $baseQuery)
            ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
            ->get(['id', 'created_at']);

        $weeklyUploadsByDate = $weeklyResolutions
            ->groupBy(fn (IaResolution $resolution) => optional($resolution->created_at)->format('Y-m-d'))
            ->map(fn ($items) => $items->count());

        $days = collect(range(0, 6))
            ->map(fn (int $offset) => $weekStartDate->copy()->addDays($offset));

        $weeklyLabels = $days
            ->map(fn (Carbon $date) => $date->format('D'))
            ->all();

        $weeklyUploads = $days
            ->map(fn (Carbon $date) => (int) ($weeklyUploadsByDate[$date->format('Y-m-d')] ?? 0))
            ->all();

        $monthlyResolutions = (clone $baseQuery)
            ->whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->get(['id', 'created_at']);

        $monthlyUploadsByDate = $monthlyResolutions
            ->groupBy(fn (IaResolution $resolution) => optional($resolution->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        $months = collect(range(0, 5))
            ->map(fn (int $offset) => $monthStartDate->copy()->addMonths($offset));

        $monthlyLabels = $months
            ->map(fn (Carbon $date) => $date->format('M Y'))
            ->all();

        $monthlyUploads = $months
            ->map(fn (Carbon $date) => (int) ($monthlyUploadsByDate[$date->format('Y-m')] ?? 0))
            ->all();

        $statusCounts = (clone $baseQuery)
            ->selectRaw("
                SUM(CASE WHEN status IN ('validated', 'accomplished') THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'on-going' THEN 1 ELSE 0 END) as ongoing_count,
                SUM(CASE WHEN status IS NULL OR status NOT IN ('validated', 'accomplished', 'on-going') THEN 1 ELSE 0 END) as pending_count,
                COUNT(*) as total_count
            ")
            ->first();

        $completedCount = (int) ($statusCounts->completed_count ?? 0);
        $ongoingCount = (int) ($statusCounts->ongoing_count ?? 0);
        $pendingCount = (int) ($statusCounts->pending_count ?? 0);
        $totalCount = (int) ($statusCounts->total_count ?? 0);
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0;
        $completedLabel = $team === 'fs_team' ? 'Validated' : ($team === null ? 'Completed' : 'Accomplished');
        $pendingLabel = $team === 'fs_team' ? 'Not Validated' : ($team === null ? 'Pending' : 'Not Accomplished');

        return [
            'monthlyLabels' => $monthlyLabels,
            'monthlyUploads' => $monthlyUploads,
            'monthlyUploadsTotal' => array_sum($monthlyUploads),
            'weeklyLabels' => $weeklyLabels,
            'weeklyUploads' => $weeklyUploads,
            'weeklyUploadsTotal' => array_sum($weeklyUploads),
            'statusLabels' => [$completedLabel, 'On-Going', 'Pending'],
            'statusValues' => [$completedCount, $ongoingCount, $pendingCount],
            'validatedCount' => $completedCount,
            'completedCount' => $completedCount,
            'completedLabel' => $completedLabel,
            'pendingLabel' => $pendingLabel,
            'ongoingCount' => $ongoingCount,
            'pendingCount' => $pendingCount,
            'totalActivities' => $totalCount,
            'completionRate' => $completionRate,
            'monthRangeLabel' => $monthStartDate->format('M Y') . ' - ' . $monthEndDate->format('M Y'),
            'weekRangeLabel' => $weekStartDate->format('M d') . ' - ' . $weekEndDate->format('M d, Y'),
        ];
    }
}
