<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
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
        $resolutions = IaResolution::where('team', 'row_team')->latest()->get();
        return view('row_team.resolutions', compact('resolutions'));
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
            'document.max' => 'Each file must not be larger than 5 MB.',
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
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ], $fileValidationMessages)->validate();

            $path = $file->store('forms', 'public');
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
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $downloadable = Downloadable::findOrFail($id);
        $file = $request->file('document');

        $previousName = $downloadable->original_name;
        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $path = $file->store('forms', 'public');
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
        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

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
            'document.max' => 'Each file must not be larger than 5 MB.',
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
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ], $fileValidationMessages)->validate();

            $path = $file->store('resolutions', 'public');
            $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

            IaResolution::create([
                'title' => $cleanTitle,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'team' => 'row_team'
            ]);
        }

        $message = $files->count() === 1
            ? 'Resolution uploaded successfully.'
            : "{$files->count()} resolutions uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $resolutionMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} IA resolutions."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} IA resolutions.";
        $this->notifications()->notifyByActorScope($request->user(), 'row_team', 'IA resolutions updated', $resolutionMessage, ['type' => 'ia_resolution', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateResolution(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $resolution = IaResolution::findOrFail($id);
        $file = $request->file('document');

        $previousName = $resolution->original_name;
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        $path = $file->store('resolutions', 'public');
        $resolution->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyByActorScope($request->user(), 'row_team', 'IA resolution updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $previousStatus = $resolution->status ?: 'no status';
        $resolution->update(['status' => $request->status]);

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyAgency($request->user(), 'IA resolution status changed', "{$actorLabel} changed the status of {$resolution->title} in {$teamLabel} from {$previousStatus} to {$request->status}.", ['type' => 'ia_resolution_status', 'team' => 'row_team', 'team_label' => $teamLabel, 'status' => $request->status]);

        return $this->successResponse($request, 'Resolution status updated successfully.');
    }

    // 9. Delete IA Resolution
    public function deleteResolution(Request $request, $id)
    {
        $resolution = IaResolution::findOrFail($id);

        // Delete file from storage
        $deletedName = $resolution->original_name;
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'row_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        $teamLabel = $this->notifications()->teamLabel('row_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyByActorScope($request->user(), 'row_team', 'IA resolution removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => 'row_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }
}
