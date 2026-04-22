<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\PcrStatusReport;

class PcrTeamController extends Controller
{
    use HandlesAsyncRequests;

    private function validatePcrStatus(Request $request, bool $requireId = false): array
    {
        $rules = [
            'fund_source' => ['required', 'string', 'max:50'],
            'no_of_contracts' => ['required', 'integer', 'min:0'],
            'allocation' => ['required', 'numeric', 'min:0'],
            'no_of_pcr_prepared' => ['required', 'integer', 'min:0'],
            'no_of_pcr_submitted_to_regional_office' => ['required', 'integer', 'min:0'],
            'accomplishment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'for_signing_of_ia_chief_dm_rm' => ['required', 'integer', 'min:0'],
            'for_submission_to_ro1' => ['required', 'integer', 'min:0'],
            'not_yet_prepared_pending_details' => ['required', 'integer', 'min:0'],
        ];

        if ($requireId) {
            $rules['id'] = ['required', 'integer', 'exists:pcr_status_reports,id'];
        }

        return $request->validate($rules);
    }

    public function index()
    {
        $resolutions = IaResolution::where('team', 'pcr_team')->latest()->get();
        $events = Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        $categories = EventCategory::all();
        $pcrStatusReports = PcrStatusReport::orderByDesc('fund_source')->paginate(8);
        return view('pcr_team.dashboard', compact('resolutions', 'events', 'categories', 'pcrStatusReports'));
    }

    public function downloadables()
    {
        $files = Downloadable::where('team', 'pcr_team')->get();
        return view('pcr_team.downloadables', compact('files'));
    }

    public function resolutions()
    {
        $resolutions = IaResolution::where('team', 'pcr_team')->latest()->get();
        return view('pcr_team.resolutions', compact('resolutions'));
    }

    public function uploadForm(Request $request)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $file = $request->file('document');
        $path = $file->store('forms', 'public');

        $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

        Downloadable::create([
            'title' => $cleanTitle,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'team' => 'pcr_team'
        ]);

        return $this->successResponse($request, 'File uploaded successfully.');
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
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $file = $request->file('document');
        $path = $file->store('resolutions', 'public');

        $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

        IaResolution::create([
            'title' => $cleanTitle,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'team' => 'pcr_team'
        ]);

        return $this->successResponse($request, 'Resolution uploaded successfully.');
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
        // if ($resolution->team !== 'pcr_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }

    public function storePcrStatus(Request $request)
    {
        PcrStatusReport::create($this->validatePcrStatus($request));

        return $this->successResponse($request, 'PCR status data added successfully.');
    }

    public function updatePcrStatus(Request $request)
    {
        $validated = $this->validatePcrStatus($request, true);
        $report = PcrStatusReport::findOrFail($validated['id']);
        $report->update(collect($validated)->except('id')->toArray());

        return $this->successResponse($request, 'PCR status data updated successfully.');
    }

    public function deletePcrStatus(Request $request, $id)
    {
        PcrStatusReport::findOrFail($id)->delete();

        return $this->successResponse($request, 'PCR status data deleted successfully.');
    }
}
