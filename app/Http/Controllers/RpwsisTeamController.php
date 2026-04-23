<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\RpwsisAccomplishment;
use App\Models\EventCategory;
use App\Models\RpwsisAccomplishmentSummary;

class RpwsisTeamController extends Controller
{
    use HandlesAsyncRequests;

    // 1. Dashboard
    public function index()
    {
        $resolutions = IaResolution::where('team', 'rpwsis_team')->latest()->get();
        $events = Event::with('category')
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        // ✅ ADDED THIS: Fetch records to fix the "undefined $records" error
        $records = RpwsisAccomplishment::latest()->get();

        // ✅ ADDED THIS: Fetch records for the new Summary Table
        $summaryRecords = RpwsisAccomplishmentSummary::latest()->get();

        $categories = EventCategory::all();
        return view('rpwsis_team.dashboard', compact('resolutions', 'events', 'categories','records', 'summaryRecords'));
    }

    // 2. View Downloadables Page
    public function downloadables()
    {
        $files = Downloadable::where('team', 'rpwsis_team')->get();
        return view('rpwsis_team.downloadables', compact('files'));
    }

    // 3. View IA Resolutions Page
    public function resolutions()
    {
        $resolutions = IaResolution::where('team', 'rpwsis_team')->latest()->get();
        return view('rpwsis_team.resolutions', compact('resolutions'));
    }

    // 4. Upload Downloadable
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
            'team' => 'rpwsis_team' // 🔥 IMPORTANT
        ]);
        return $this->successResponse($request, 'File uploaded successfully.');
    }

    // 5. Update Downloadable
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

    // 6. Delete Downloadable
    public function deleteForm(Request $request, $id)
    {
        $downloadable = Downloadable::findOrFail($id);

        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $downloadable->delete();

        return $this->successResponse($request, 'File deleted successfully.');
    }

    // 7. Upload Resolution
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
            'team' => 'rpwsis_team' // 🔥 IMPORTANT
        ]);
        return $this->successResponse($request, 'Resolution uploaded successfully.');
    }

    // 7. Update Resolution File
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

    // 8. Update Resolution Status
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
        // if ($resolution->team !== 'rpwsis_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }
    //10
    public function storeAccomplishment(Request $request)
    {
        $validated = $request->validate([
            'region' => ['required', 'string', 'max:100'],
            'batch' => ['nullable', 'string', 'max:100'],
            'allocation' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:255'],
            'activity' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'phy' => ['nullable', 'numeric', 'between:0,100'],
            'fin' => ['nullable', 'numeric', 'between:0,100'],
            'exp' => ['nullable', 'numeric', 'min:0'],
        ] + collect(range(1, 12))->mapWithKeys(fn ($index) => [
            'c' . $index => ['nullable', 'string', 'max:255'],
        ])->toArray());

        $record = RpwsisAccomplishment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Accomplishment record saved successfully.',
            'record' => $record,
        ]);
    }

    // 11. Delete Accomplishment
    public function deleteAccomplishment($id)
    {
        $record = RpwsisAccomplishment::findOrFail($id);
        $record->delete();

        return response()->json(['success' => true]);
    }

    // ----------------------------------------------------------------------
    // ✅ NEW METHODS FOR THE SUMMARY OF ACCOMPLISHMENT TABLE
    // ----------------------------------------------------------------------

    // 12. Store Summary Accomplishment
    public function storeSummary(Request $request)
    {
        // Map the inputs from your JS variables to the database columns
        $record = RpwsisAccomplishmentSummary::create([
            'region'            => $request->sum_region,
            'province'          => $request->sum_province,
            'municipality'      => $request->sum_municipality,
            'barangay'          => $request->sum_barangay,
            'plantation_type'   => $request->sum_type,
            'year_established'  => $request->sum_year,
            'target_area_1'     => $request->sum_target_1,
            'area_planted'      => $request->sum_area_planted,
            'species_planted'   => $request->sum_species,
            'spacing'           => $request->sum_spacing,
            'maintenance'       => $request->sum_maintenance,
            'target_area_2'     => $request->sum_target_2,
            'actual_area'       => $request->sum_actual,
            'mortality_rate'    => $request->sum_mortality,
            'species_replanted' => $request->sum_replanted,
            'nis_name'          => $request->sum_nis,
            'remarks'           => $request->sum_remarks,
        ]);

        return response()->json($record);
    }

    // 13. Delete Summary Accomplishment
    public function deleteSummary($id)
    {
        $record = RpwsisAccomplishmentSummary::findOrFail($id);
        $record->delete();

        return response()->json(['success' => true]);
    }

}
