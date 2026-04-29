<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $payload
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = (string) ($this->payload['type'] ?? 'general');

        return $this->payload + [
            'type' => $type,
            'category_label' => $this->categoryLabel($type),
            'category_color' => $this->categoryColor($type),
            'url' => $this->resolveUrl($notifiable, $type),
        ];
    }

    private function resolveUrl(object $notifiable, string $type): ?string
    {
        $team = (string) ($this->payload['team'] ?? '');

        return match ($type) {
            'downloadable' => $this->teamRoute($team, 'downloadables'),
            'ia_resolution' => $this->teamRoute($team, 'resolutions'),
            'ia_resolution_status' => $this->activeProjectsRoute($notifiable, $team),
            'event' => $this->eventRoute($notifiable, $team),
            'minutes', 'memorandum' => route('administrative.index'),
            default => null,
        };
    }

    private function eventRoute(object $notifiable, string $team): ?string
    {
        if ($team !== '' && $team !== 'all') {
            return $this->teamRoute($team, 'dashboard');
        }

        if ($notifiable instanceof User && $notifiable->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($notifiable instanceof User) {
            return $this->teamRoute($notifiable->role, 'dashboard');
        }

        return null;
    }

    private function activeProjectsRoute(object $notifiable, string $team): ?string
    {
        if ($notifiable instanceof User && $notifiable->isAdmin()) {
            return route('admin.dashboard') . '#activeProjectsContainer';
        }

        return ($this->teamRoute($team, 'dashboard') ?? $this->teamRoute($team, 'resolutions'))
            ? (($this->teamRoute($team, 'dashboard') ?? $this->teamRoute($team, 'resolutions')) . '#activeProjectsContainer')
            : null;
    }

    private function teamRoute(string $team, string $section): ?string
    {
        $routeMap = [
            'fs_team' => [
                'dashboard' => 'fs.dashboard',
                'downloadables' => 'fs.downloadables',
                'resolutions' => 'fs.resolutions',
            ],
            'rpwsis_team' => [
                'dashboard' => 'rpwsis.dashboard',
                'downloadables' => 'rpwsis.downloadables',
                'resolutions' => 'rpwsis.resolutions',
            ],
            'cm_team' => [
                'dashboard' => 'cm.dashboard',
                'downloadables' => 'cm.downloadables',
                'resolutions' => 'cm.resolutions',
            ],
            'row_team' => [
                'dashboard' => 'row.dashboard',
                'downloadables' => 'row.downloadables',
                'resolutions' => 'row.resolutions',
            ],
            'pcr_team' => [
                'dashboard' => 'pcr.dashboard',
                'downloadables' => 'pcr.downloadables',
                'resolutions' => 'pcr.resolutions',
            ],
            'pao_team' => [
                'dashboard' => 'pao.dashboard',
                'downloadables' => 'pao.downloadables',
                'resolutions' => 'pao.resolutions',
            ],
        ];

        $routeName = $routeMap[$team][$section] ?? null;

        return $routeName ? route($routeName) : null;
    }

    private function categoryLabel(string $type): string
    {
        return match ($type) {
            'event' => 'Event',
            'downloadable' => 'Downloadable',
            'ia_resolution' => 'IA Resolution',
            'ia_resolution_status' => 'Status Change',
            'minutes' => 'Minutes',
            'memorandum' => 'Memorandum',
            default => 'Update',
        };
    }

    private function categoryColor(string $type): string
    {
        return match ($type) {
            'event' => 'blue',
            'downloadable' => 'amber',
            'ia_resolution' => 'green',
            'ia_resolution_status' => 'rose',
            'minutes', 'memorandum' => 'slate',
            default => 'slate',
        };
    }
}
