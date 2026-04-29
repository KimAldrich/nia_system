<?php

namespace App\Http\Middleware;

use App\Models\AdministrativeDocument;
use App\Models\AuditLog;
use App\Models\Downloadable;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\FsdeProject;
use App\Models\HydroGeoProject;
use App\Models\IaResolution;
use App\Models\PaoPowData;
use App\Models\PcrStatusReport;
use App\Models\ProcurementProject;
use App\Models\RpwsisAccomplishment;
use App\Models\RpwsisAccomplishmentSummary;
use App\Models\RpwsisInfrastructure;
use App\Models\RpwsisNurseryEstablishment;
use App\Models\RpwsisSignage;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $snapshot = $this->captureSnapshot($request, $routeName);

        $response = $next($request);

        if ($this->shouldLog($request, $response, $routeName)) {
            $this->storeLog($request, $routeName, $snapshot);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response, ?string $routeName): bool
    {
        if (!auth()->check() || blank($routeName)) {
            return false;
        }

        if (!in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return $this->resolveActionConfig($routeName) !== null;
    }

    private function captureSnapshot(Request $request, ?string $routeName): array
    {
        if (blank($routeName)) {
            return [];
        }

        $context = $this->resolveSubjectContext($request, $routeName);
        if ($context === null) {
            return [];
        }

        [$modelClass, $id] = $context;
        if (!class_exists($modelClass) || blank($id)) {
            return [];
        }

        $record = $modelClass::find($id);
        if (!$record) {
            return [];
        }

        return [
            'subject_type' => class_basename($modelClass),
            'subject_id' => $record->getKey(),
            'subject_label' => $this->extractLabelFromRecord($record),
            'metadata' => $this->extractMetadataFromRecord($record),
        ];
    }

    private function storeLog(Request $request, string $routeName, array $snapshot): void
    {
        $config = $this->resolveActionConfig($routeName);
        if ($config === null) {
            return;
        }

        $user = $request->user();
        $resolvedTeam = $this->resolveMetadataTeam($request, $snapshot, $user?->role);
        $metadata = array_filter([
            'team' => $resolvedTeam,
            'status' => $request->input('status'),
            'event_date' => $request->input('event_date'),
            'event_time' => $request->input('event_time'),
            'files' => $this->extractFileNames($request),
            'target_user_email' => $request->input('email'),
            'target_user_role' => $request->input('role'),
            'document_type' => $request->input('document_type'),
            'record_snapshot' => Arr::get($snapshot, 'metadata'),
        ], fn ($value) => !blank($value));

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role,
            'action' => $config['action'],
            'subject_type' => Arr::get($snapshot, 'subject_type', $config['subject_type'] ?? null),
            'subject_id' => Arr::get($snapshot, 'subject_id'),
            'subject_label' => Arr::get($snapshot, 'subject_label', $this->resolveSubjectLabelFromRequest($request, $routeName, $config)),
            'description' => $this->buildDescription($request, $config, $snapshot),
            'route_name' => $routeName,
            'method' => strtoupper($request->method()),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'metadata' => $metadata,
        ]);
    }

    private function resolveActionConfig(string $routeName): ?array
    {
        $exact = [
            'administrative.store' => ['action' => 'administrative.uploaded', 'label' => 'uploaded an administrative document', 'subject_type' => 'AdministrativeDocument'],
            'administrative.destroy' => ['action' => 'administrative.deleted', 'label' => 'deleted an administrative document', 'subject_type' => 'AdministrativeDocument'],
            'map.upload' => ['action' => 'map.uploaded', 'label' => 'uploaded map files', 'subject_type' => 'MapFile'],
            'admin.users.store' => ['action' => 'user.created', 'label' => 'created a user account', 'subject_type' => 'User'],
            'admin.users.status' => ['action' => 'user.status_changed', 'label' => 'changed a user account status', 'subject_type' => 'User'],
            'admin.users.password' => ['action' => 'user.password_reset', 'label' => 'reset a user password', 'subject_type' => 'User'],
            'admin.users.destroy' => ['action' => 'user.deleted', 'label' => 'deleted a user account', 'subject_type' => 'User'],
            'admin.events.store' => ['action' => 'calendar.event_added', 'label' => 'added a calendar event', 'subject_type' => 'Event'],
            'admin.events.update' => ['action' => 'calendar.event_updated', 'label' => 'updated a calendar event', 'subject_type' => 'Event'],
            'admin.events.destroy' => ['action' => 'calendar.event_deleted', 'label' => 'deleted a calendar event', 'subject_type' => 'Event'],
            'admin.categories.store' => ['action' => 'calendar.tag_added', 'label' => 'added a calendar tag', 'subject_type' => 'EventCategory'],
            'admin.categories.destroy' => ['action' => 'calendar.tag_deleted', 'label' => 'deleted a calendar tag', 'subject_type' => 'EventCategory'],
            'admin.downloadables.upload' => ['action' => 'downloadable.uploaded', 'label' => 'uploaded a file to a team', 'subject_type' => 'Downloadable'],
            'admin.resolutions.upload' => ['action' => 'resolution.uploaded', 'label' => 'uploaded a resolution to a team', 'subject_type' => 'IaResolution'],
            'fs.hydro.store' => ['action' => 'hydro_geo.created', 'label' => 'added a Hydro-Geo record', 'subject_type' => 'HydroGeoProject'],
            'fs.hydro.update' => ['action' => 'hydro_geo.updated', 'label' => 'updated a Hydro-Geo record', 'subject_type' => 'HydroGeoProject'],
            'fs.hydro.destroy' => ['action' => 'hydro_geo.deleted', 'label' => 'deleted a Hydro-Geo record', 'subject_type' => 'HydroGeoProject'],
            'fs.fsde.store' => ['action' => 'fsde.created', 'label' => 'added an FSDE record', 'subject_type' => 'FsdeProject'],
            'fs.fsde.update' => ['action' => 'fsde.updated', 'label' => 'updated an FSDE record', 'subject_type' => 'FsdeProject'],
            'fs.fsde.destroy' => ['action' => 'fsde.deleted', 'label' => 'deleted an FSDE record', 'subject_type' => 'FsdeProject'],
            'rpwsis.accomplishments.store' => ['action' => 'rpwsis.accomplishment_created', 'label' => 'added an RP-WSIS accomplishment record', 'subject_type' => 'RpwsisAccomplishment'],
            'rpwsis.accomplishments.update' => ['action' => 'rpwsis.accomplishment_updated', 'label' => 'updated an RP-WSIS accomplishment record', 'subject_type' => 'RpwsisAccomplishment'],
            'rpwsis.accomplishments.delete' => ['action' => 'rpwsis.accomplishment_deleted', 'label' => 'deleted an RP-WSIS accomplishment record', 'subject_type' => 'RpwsisAccomplishment'],
            'rpwsis.summary.store' => ['action' => 'rpwsis.summary_created', 'label' => 'added an RP-WSIS summary record', 'subject_type' => 'RpwsisAccomplishmentSummary'],
            'rpwsis.summary.update' => ['action' => 'rpwsis.summary_updated', 'label' => 'updated an RP-WSIS summary record', 'subject_type' => 'RpwsisAccomplishmentSummary'],
            'rpwsis.summary.delete' => ['action' => 'rpwsis.summary_deleted', 'label' => 'deleted an RP-WSIS summary record', 'subject_type' => 'RpwsisAccomplishmentSummary'],
            'rpwsis.nursery.store' => ['action' => 'rpwsis.nursery_created', 'label' => 'added an RP-WSIS nursery record', 'subject_type' => 'RpwsisNurseryEstablishment'],
            'rpwsis.nursery.update' => ['action' => 'rpwsis.nursery_updated', 'label' => 'updated an RP-WSIS nursery record', 'subject_type' => 'RpwsisNurseryEstablishment'],
            'rpwsis.nursery.delete' => ['action' => 'rpwsis.nursery_deleted', 'label' => 'deleted an RP-WSIS nursery record', 'subject_type' => 'RpwsisNurseryEstablishment'],
            'rpwsis.signages.store' => ['action' => 'rpwsis.signage_created', 'label' => 'added an RP-WSIS signage record', 'subject_type' => 'RpwsisSignage'],
            'rpwsis.signages.update' => ['action' => 'rpwsis.signage_updated', 'label' => 'updated an RP-WSIS signage record', 'subject_type' => 'RpwsisSignage'],
            'rpwsis.signages.delete' => ['action' => 'rpwsis.signage_deleted', 'label' => 'deleted an RP-WSIS signage record', 'subject_type' => 'RpwsisSignage'],
            'rpwsis.infrastructure.store' => ['action' => 'rpwsis.infrastructure_created', 'label' => 'added an RP-WSIS infrastructure record', 'subject_type' => 'RpwsisInfrastructure'],
            'rpwsis.infrastructure.update' => ['action' => 'rpwsis.infrastructure_updated', 'label' => 'updated an RP-WSIS infrastructure record', 'subject_type' => 'RpwsisInfrastructure'],
            'rpwsis.infrastructure.delete' => ['action' => 'rpwsis.infrastructure_deleted', 'label' => 'deleted an RP-WSIS infrastructure record', 'subject_type' => 'RpwsisInfrastructure'],
            'cm.procurement.store' => ['action' => 'procurement.created', 'label' => 'added a procurement record', 'subject_type' => 'ProcurementProject'],
            'cm.procurement.update' => ['action' => 'procurement.updated', 'label' => 'updated a procurement record', 'subject_type' => 'ProcurementProject'],
            'cm.procurement.destroy' => ['action' => 'procurement.deleted', 'label' => 'deleted a procurement record', 'subject_type' => 'ProcurementProject'],
            'pcr.status.store' => ['action' => 'pcr.status_created', 'label' => 'added a PCR status record', 'subject_type' => 'PcrStatusReport'],
            'pcr.status.update' => ['action' => 'pcr.status_updated', 'label' => 'updated a PCR status record', 'subject_type' => 'PcrStatusReport'],
            'pcr.status.delete' => ['action' => 'pcr.status_deleted', 'label' => 'deleted a PCR status record', 'subject_type' => 'PcrStatusReport'],
            'pao.pow.store' => ['action' => 'pow.created', 'label' => 'added a POW record', 'subject_type' => 'PaoPowData'],
            'pao.pow.update' => ['action' => 'pow.updated', 'label' => 'updated a POW record', 'subject_type' => 'PaoPowData'],
            'pao.pow.delete' => ['action' => 'pow.deleted', 'label' => 'deleted a POW record', 'subject_type' => 'PaoPowData'],
        ];

        if (isset($exact[$routeName])) {
            return $exact[$routeName];
        }

        foreach ([
            '*.downloadables.upload' => ['action' => 'downloadable.uploaded', 'label' => 'uploaded a downloadable file', 'subject_type' => 'Downloadable'],
            '*.downloadables.update' => ['action' => 'downloadable.updated', 'label' => 'updated a downloadable file', 'subject_type' => 'Downloadable'],
            '*.downloadables.delete' => ['action' => 'downloadable.deleted', 'label' => 'deleted a downloadable file', 'subject_type' => 'Downloadable'],
            '*.resolutions.upload' => ['action' => 'resolution.uploaded', 'label' => 'uploaded a resolution', 'subject_type' => 'IaResolution'],
            '*.resolutions.update' => ['action' => 'resolution.updated', 'label' => 'updated a resolution', 'subject_type' => 'IaResolution'],
            '*.resolutions.delete' => ['action' => 'resolution.deleted', 'label' => 'deleted a resolution', 'subject_type' => 'IaResolution'],
            '*.resolutions.update_status' => ['action' => 'resolution.status_changed', 'label' => 'changed a resolution status', 'subject_type' => 'IaResolution'],
        ] as $pattern => $config) {
            if (Str::is($pattern, $routeName)) {
                return $config;
            }
        }

        return null;
    }

    private function resolveSubjectContext(Request $request, string $routeName): ?array
    {
        $route = $request->route();

        return match (true) {
            Str::is('*.downloadables.update', $routeName), Str::is('*.downloadables.delete', $routeName) => [Downloadable::class, $route?->parameter('id')],
            Str::is('*.resolutions.update', $routeName), Str::is('*.resolutions.delete', $routeName), Str::is('*.resolutions.update_status', $routeName) => [IaResolution::class, $route?->parameter('id')],
            $routeName === 'admin.users.status', $routeName === 'admin.users.password', $routeName === 'admin.users.destroy' => [User::class, $this->extractRouteModelId($route?->parameter('user'))],
            $routeName === 'admin.events.update', $routeName === 'admin.events.destroy' => [Event::class, $route?->parameter('id')],
            $routeName === 'admin.categories.destroy' => [EventCategory::class, $route?->parameter('id')],
            $routeName === 'administrative.destroy' => [AdministrativeDocument::class, $route?->parameter('id')],
            $routeName === 'fs.hydro.update', $routeName === 'fs.hydro.destroy' => [HydroGeoProject::class, $route?->parameter('id')],
            $routeName === 'fs.fsde.update', $routeName === 'fs.fsde.destroy' => [FsdeProject::class, $route?->parameter('id')],
            $routeName === 'rpwsis.accomplishments.update', $routeName === 'rpwsis.accomplishments.delete' => [RpwsisAccomplishment::class, $route?->parameter('id')],
            $routeName === 'rpwsis.summary.update', $routeName === 'rpwsis.summary.delete' => [RpwsisAccomplishmentSummary::class, $route?->parameter('id')],
            $routeName === 'rpwsis.nursery.update', $routeName === 'rpwsis.nursery.delete' => [RpwsisNurseryEstablishment::class, $route?->parameter('id')],
            $routeName === 'rpwsis.signages.update', $routeName === 'rpwsis.signages.delete' => [RpwsisSignage::class, $route?->parameter('id')],
            $routeName === 'rpwsis.infrastructure.update', $routeName === 'rpwsis.infrastructure.delete' => [RpwsisInfrastructure::class, $route?->parameter('id')],
            $routeName === 'cm.procurement.update' => [ProcurementProject::class, $request->input('id')],
            $routeName === 'cm.procurement.destroy' => [ProcurementProject::class, $route?->parameter('id')],
            $routeName === 'pcr.status.update' => [PcrStatusReport::class, $request->input('id')],
            $routeName === 'pcr.status.delete' => [PcrStatusReport::class, $route?->parameter('id')],
            $routeName === 'pao.pow.update' => [PaoPowData::class, $request->input('id')],
            $routeName === 'pao.pow.delete' => [PaoPowData::class, $route?->parameter('id')],
            default => null,
        };
    }

    private function resolveSubjectLabelFromRequest(Request $request, string $routeName, array $config): ?string
    {
        if ($routeName === 'admin.users.store') {
            return trim((string) $request->input('name')) ?: $request->input('email');
        }

        if (in_array($routeName, ['admin.events.store', 'admin.categories.store'], true)) {
            return trim((string) $request->input('title')) ?: trim((string) $request->input('name'));
        }

        if (in_array($routeName, ['admin.downloadables.upload', 'admin.resolutions.upload'], true)) {
            return implode(', ', $this->extractFileNames($request)) ?: $this->resolveTeamLabel((string) $request->input('team'));
        }

        if ($routeName === 'map.upload') {
            return $request->input('category');
        }

        return $config['subject_type'] ?? null;
    }

    private function buildDescription(Request $request, array $config, array $snapshot = []): string
    {
        $userName = $request->user()?->name ?? 'Unknown user';
        $parts = [$userName . ' ' . $config['label']];

        $status = trim((string) $request->input('status'));
        if ($status !== '') {
            $parts[] = 'to "' . $status . '"';
        }

        $team = $this->resolveTeamLabel((string) $request->input('team', ''));
        if ($team) {
            $parts[] = 'for ' . $team;
        }

        $subject = Arr::get($snapshot, 'subject_label')
            ?: $this->resolveSubjectLabelFromRequest($request, $request->route()?->getName() ?? '', $config);
        if ($subject) {
            $parts[] = '(' . $subject . ')';
        }

        return implode(' ', $parts) . '.';
    }

    private function extractFileNames(Request $request): array
    {
        $single = $request->file('document');
        $multiple = $request->file('documents', []);
        $files = collect(is_array($multiple) ? $multiple : [])->filter();

        if ($files->isEmpty() && $single) {
            $files = collect([$single]);
        }

        return $files
            ->map(fn ($file) => $file?->getClientOriginalName())
            ->filter()
            ->values()
            ->all();
    }

    private function extractRouteModelId(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'getKey')) {
            return $value->getKey();
        }

        return $value;
    }

    private function extractLabelFromRecord(object $record): string
    {
        foreach (['title', 'name', 'original_name', 'project_name', 'name_of_project', 'system_name', 'email', 'contract_no'] as $field) {
            $value = data_get($record, $field);
            if (!blank($value)) {
                return (string) $value;
            }
        }

        return class_basename($record) . ' #' . data_get($record, 'id');
    }

    private function extractMetadataFromRecord(object $record): array
    {
        return array_filter([
            'team' => data_get($record, 'team'),
            'status' => data_get($record, 'status'),
            'event_date' => data_get($record, 'event_date'),
            'event_time' => data_get($record, 'event_time'),
            'original_name' => data_get($record, 'original_name'),
        ], fn ($value) => !blank($value));
    }

    private function resolveMetadataTeam(Request $request, array $snapshot, ?string $userRole): ?string
    {
        $requestTeam = $this->resolveTeamLabel((string) $request->input('team', ''));
        if ($requestTeam) {
            return $requestTeam;
        }

        $snapshotTeam = $this->resolveTeamLabel((string) Arr::get($snapshot, 'metadata.team', ''));
        if ($snapshotTeam) {
            return $snapshotTeam;
        }

        $userTeam = $this->resolveTeamLabel((string) $userRole);
        if ($userTeam && $userTeam !== 'Admin') {
            return $userTeam;
        }

        return null;
    }

    private function resolveTeamLabel(string $team): ?string
    {
        $labels = [
            'admin' => 'Admin',
            'fs_team' => 'Feasibility Study Team',
            'rpwsis_team' => 'Social and Environmental Team',
            'cm_team' => 'Contract Management Team',
            'row_team' => 'Right Of Way Team',
            'pcr_team' => 'Program Completion Report Team',
            'pao_team' => 'Programming Team',
        ];

        return $labels[$team] ?? null;
    }
}
