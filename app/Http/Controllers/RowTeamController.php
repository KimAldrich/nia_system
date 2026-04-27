<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;

class RowTeamController extends Controller
{
    use HandlesAsyncRequests;

    public function index()
    {
        $resolutions = IaResolution::where('team', 'row_team')
            ->latest()
            ->paginate(8, ['*'], 'active_projects_page')
            ->withQueryString();
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

        $events = (clone $upcomingEventsQuery)->get();
        $paginatedEvents = (clone $upcomingEventsQuery)
            ->paginate(5, ['*'], 'events_page')
            ->withQueryString();

        $categories = EventCategory::all();
        return view('row_team.dashboard', compact('resolutions', 'events', 'paginatedEvents', 'categories'));
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
        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']]);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ])->validate();

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

        return $this->successResponse($request, $message);
    }

    public function updateForm(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $downloadable = Downloadable::findOrFail($id);
        $file = $request->file('document');

        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $path = $file->store('forms', 'public');
        $downloadable->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        return $this->successResponse($request, 'File updated successfully.');
    }

    public function deleteForm(Request $request, $id)
    {
        $downloadable = Downloadable::findOrFail($id);

        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $downloadable->delete();

        return $this->successResponse($request, 'File deleted successfully.');
    }

    public function uploadResolution(Request $request)
    {
        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']]);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ])->validate();

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

        return $this->successResponse($request, $message);
    }

    public function updateResolution(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $resolution = IaResolution::findOrFail($id);
        $file = $request->file('document');

        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        $path = $file->store('resolutions', 'public');
        $resolution->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        return $this->successResponse($request, 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolution->update(['status' => $request->status]);

        return $this->successResponse($request, 'Resolution status updated successfully.');
    }

    // 9. Delete IA Resolution
    public function deleteResolution(Request $request, $id)
    {
        $resolution = IaResolution::findOrFail($id);

        // Delete file from storage
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'row_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }
}
