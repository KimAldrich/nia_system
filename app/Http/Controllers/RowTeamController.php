<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\IaResolutionFile;
use App\Models\Downloadable;
use App\Models\Event;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;

class RowTeamController extends Controller
{
    use HandlesAsyncRequests;
    use BuildsResolutionAnalytics;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

    public function index()
    {
        $resolutions = IaResolution::where('team', 'row_team')
            ->latest()
            ->paginate(8, ['*'], 'active_projects_page')
            ->withQueryString();
        $events = Event::with('category')
            ->orderBy('event_date', 'asc')
            ->get();

        $upcomingEventsQuery = Event::with('category')
            ->where(function ($query) {
                $today = now()->toDateString();
                $currentTime = now()->format('H:i:s');
                $query->where('event_date', '>', $today)
                    ->orWhere(function ($q) use ($today, $currentTime) {
                        $q->where('event_date', $today)
                            ->whereRaw(
                                "TIME(STR_TO_DATE(SUBSTRING_INDEX(TRIM(event_time), ' - ', -1), '%h:%i %p')) > ?",
                                [$currentTime]
                            );
                    });
            })
            ->orderBy('event_date', 'asc');

        $paginatedEvents = (clone $upcomingEventsQuery)
            ->paginate(5, ['*'], 'events_page')
            ->withQueryString();

        $categories = EventCategory::all();
        $analytics = $this->buildResolutionAnalytics('row_team');
        return view('row_team.dashboard', compact('resolutions', 'events', 'paginatedEvents', 'categories', 'analytics'));
    }

    public function downloadables()
    {
        $files = Downloadable::where('team', 'row_team')->get();
        return view('row_team.downloadables', compact('files'));
    }

    public function resolutions()
    {
        $resolutions = IaResolution::with('files')->where('team', 'row_team')->latest()->get();
        return view('shared.team-resolutions', [
            'pageTitle' => 'Right of Way Files',
            'headerTitle' => 'Right of Way Files',
            'headerDesc' => 'Manage status entries and attached files for Right of Way.',
            'teamRole' => 'row_team',
            'uploadRouteName' => 'row.resolutions.upload',
            'deleteRouteName' => 'row.resolutions.delete',
            'resolutions' => $resolutions,
        ]);
    }

    public function uploadForm(Request $request)
    {
        $fileValidationMessages = [
            'documents.required' => 'Please select at least one file to upload.',
            'documents.array' => 'Please upload valid files only.',
            'documents.min' => 'Please select at least one file to upload.',
            'document.required' => 'Please select a file to upload.',
            'document.file' => 'Only document files are allowed.',
            'document.mimes' => 'Only document files are allowed. Please upload PDF, DOC, DOCX, XLS, or XLSX files only.',
        ];

        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']], $fileValidationMessages);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            ], $fileValidationMessages)->validate();

            $path = app(\App\Services\DocumentStorageService::class)->store($file, 'forms');
            $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

            Downloadable::create([
                'title' => $cleanTitle,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'team' => 'row_team'
            ]);
        }

        $message = $files->count() === 1
            ? 'File uploaded successfully.'
            : "{$files->count()} files uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $fileMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} downloadables."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} downloadables.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'row_team', 'Downloadables updated', $fileMessage, ['type' => 'downloadable', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateForm(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx']);
        $downloadable = Downloadable::findOrFail($id);
        $file = $request->file('document');

        $previousName = $downloadable->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($downloadable->file_path);

        $path = app(\App\Services\DocumentStorageService::class)->store($file, 'forms');
        $downloadable->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'row_team', 'Downloadable updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'File updated successfully.');
    }

    public function deleteForm(Request $request, $id)
    {
        $downloadable = Downloadable::findOrFail($id);

        $deletedName = $downloadable->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($downloadable->file_path);

        $downloadable->delete();

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'row_team', 'Downloadable removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'File deleted successfully.');
    }

    public function uploadResolution(Request $request)
    {
        $fileValidationMessages = [
            'documents.required' => 'Please select at least one file to upload.',
            'documents.array' => 'Please upload valid files only.',
            'documents.min' => 'Please select at least one file to upload.',
            'document.required' => 'Please select a file to upload.',
            'document.file' => 'Only document files are allowed.',
            'document.mimes' => 'Only document files are allowed. Please upload PDF, DOC, DOCX, XLS, or XLSX files only.',
        ];

        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']], $fileValidationMessages);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            ], $fileValidationMessages)->validate();

            IaResolution::attachUploadedFile($file, 'row_team');
        }

        $message = $files->count() === 1
            ? 'Resolution uploaded successfully.'
            : "{$files->count()} resolutions uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $resolutionMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} IA resolutions."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} IA resolutions.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'row_team', 'IA resolutions updated', $resolutionMessage, ['type' => 'ia_resolution', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateResolution(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx']);
        $resolution = IaResolution::findOrFail($id);
        $file = $request->file('document');

        $previousName = $resolution->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($resolution->file_path);

        $path = app(\App\Services\DocumentStorageService::class)->store($file, 'resolutions');
        $resolution->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $resolutionTeam = $resolution->team ?: 'row_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolutionTeam = $resolution->team ?: 'row_team';
        $previousStatus = $resolution->status ?: 'no status';
        $updatedStatus = IaResolution::normalizeStatusForTeam($request->status, $resolutionTeam);
        $resolution->update(['status' => $updatedStatus]);
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $previousStatusLabel = IaResolution::displayStatusLabel($previousStatus, $resolutionTeam);
        $updatedStatusLabel = IaResolution::displayStatusLabel($updatedStatus, $resolutionTeam);
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution status changed', "{$actorLabel} changed the status of {$resolution->title} in {$teamLabel} from {$previousStatusLabel} to {$updatedStatusLabel}.", ['type' => 'ia_resolution_status', 'team' => $resolutionTeam, 'team_label' => $teamLabel, 'status' => $updatedStatus]);

        return $this->successResponse($request, 'Resolution status updated successfully.');
    }

    // 9. Delete IA Resolution
    public function deleteResolution(Request $request, $id)
    {
        $resolutionFile = IaResolutionFile::with('resolution')->findOrFail($id);
        $resolution = $resolutionFile->resolution;
        $deletedName = $resolutionFile->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($resolutionFile->file_path);

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'row_team') {
        //     abort(403);
        // }

        $resolutionFile->delete();

        if ($resolution) {
            if ($resolution->files()->exists()) {
                $resolution->refreshPrimaryAttachment();
            } else {
                $resolution->delete();
            }
        }

        $resolutionTeam = $resolution?->team ?: 'row_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }
}
