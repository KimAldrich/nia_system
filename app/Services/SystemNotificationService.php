<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class SystemNotificationService
{
    private const TEAM_LABELS = [
        'admin' => 'Admin',
        'all' => 'All Teams',
        'fs_team' => 'FS Team',
        'rpwsis_team' => 'Social and Environmental Team',
        'cm_team' => 'Contract Management Team',
        'row_team' => 'Right of Way Team',
        'pcr_team' => 'Program Completion Report Team',
        'pao_team' => 'Programming Team',
    ];

    public function notifyAgency(User $actor, string $title, string $message, array $context = []): void
    {
        $this->send(
            $this->agencyRecipients(),
            $actor,
            $title,
            $message,
            $context + ['audience' => 'agency']
        );
    }

    public function notifyTeamAndAdmins(User $actor, string $team, string $title, string $message, array $context = []): void
    {
        $this->send(
            $this->teamRecipients($team),
            $actor,
            $title,
            $message,
            $context + ['audience' => 'team', 'team' => $team, 'team_label' => $this->teamLabel($team)]
        );
    }

    public function notifyByActorScope(User $actor, string $team, string $title, string $message, array $context = []): void
    {
        if ($actor->isAdmin()) {
            $this->notifyAgency($actor, $title, $message, $context + ['team' => $team, 'team_label' => $this->teamLabel($team)]);

            return;
        }

        $this->notifyTeamAndAdmins($actor, $team, $title, $message, $context);
    }

    public function teamLabel(?string $team): string
    {
        return self::TEAM_LABELS[$team ?? ''] ?? str((string) $team)->replace('_', ' ')->title()->value();
    }

    public function actorLabel(User $actor): string
    {
        return trim($actor->name . ' (' . $this->teamLabel($actor->role) . ')');
    }

    private function agencyRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->get();
    }

    private function teamRecipients(string $team): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($team) {
                $query->where('role', 'admin')
                    ->orWhere('role', $team);
            })
            ->get();
    }

    private function send(Collection $recipients, User $actor, string $title, string $message, array $context = []): void
    {
        $recipients = $recipients
            ->reject(fn (User $user) => (int) $user->id === (int) $actor->id)
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new SystemActivityNotification([
            'title' => $title,
            'message' => $message,
            'actor_name' => $actor->name,
            'actor_role' => $actor->role,
            'actor_role_label' => $this->teamLabel($actor->role),
            'created_at' => now()->toIso8601String(),
        ] + $context));
    }
}
