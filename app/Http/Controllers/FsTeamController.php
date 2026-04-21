<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\HydroGeoProject;
use App\Models\FsdeProject;
use Illuminate\Validation\Rule;

class FsTeamController extends Controller
{
    use HandlesAsyncRequests;

    // 1. Dashboard
    public function index()
    {
        // Fetch resolutions for the project table
        $resolutions = IaResolution::where('team', 'fs_team')->latest()->get();

        // Fetch upcoming events set by the admin
        $events = Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        $categories = EventCategory::all();
        // 2. Calculate the dynamic KPI numbers for the top cards
        $totalProjects = HydroGeoProject::count();
        $conducted = HydroGeoProject::whereIn('status', ['For Interpretation', 'Interpreted', 'For Submission of Raw data'])->count();
        $remaining = HydroGeoProject::where('status', 'For Schedule')->count();
        $feasible = HydroGeoProject::where('result', 'Feasible')->count();
        $hydroExportData = HydroGeoProject::all();
        $fsdeExportData = FsdeProject::all();

        // 3. Fetch the table data with pagination (8 rows per page)
        $hydroProjects = HydroGeoProject::paginate(8, ['*'], 'hydro_page');
        $fsdeProjects = FsdeProject::paginate(8, ['*'], 'fsde_page');
        return view('fs-team.dashboard', compact(
            'totalProjects',
            'conducted',
            'remaining',
            'feasible',
            'resolutions',
            'events',
            'categories',
            'hydroProjects',
            'fsdeProjects',
            'hydroExportData',
            'fsdeExportData'
        ));

    }

    // 2. View Downloadables Page
    public function downloadables()
    {
        $files = Downloadable::where('team', 'fs_team')->get();
        return view('fs-team.downloadables', compact('files'));
    }

    // 3. View IA Resolutions Page
    public function resolutions()
    {
        $resolutions = IaResolution::where('team', 'fs_team')->latest()->get();
        return view('fs-team.resolutions', compact('resolutions'));
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
            'team' => 'fs_team' // 🔥 THIS IS THE FIX
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


        // if ($downloadable->team !== 'fs_team') {
//     abort(403);
// }
        $downloadable->delete();

        return $this->successResponse($request, 'File deleted successfully.');
    }

    // 7. Upload Resolution
    public function uploadResolution(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120'
        ]);

        $file = $request->file('document');
        $path = $file->store('resolutions', 'public');

        $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

        IaResolution::create([
            'title' => $cleanTitle,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'team' => 'fs_team'
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

    public function storeHydroGeo(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
            'district' => ['required', 'string', 'max:100'],
            'project_code' => ['required', 'string', 'max:100'],
            'system_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'municipality' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in([
                'For Schedule',
                'For Interpretation',
                'For Submission of Raw data',
                'Relocation',
                'Interpreted',
                'Not Applicable',
                'C/O Contractor',
            ])],
            'result' => ['nullable', 'string', 'max:100'],
        ]);

        HydroGeoProject::create($validated);

        return $this->successResponse($request, 'New Hydro-Geo data added successfully!');
    }

    public function storeFsde(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
            'type_of_study' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:1000'],
            'municipality' => ['required', 'string', 'max:100'],
            'consultant' => ['required', 'string', 'max:255'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'actual_obligation' => ['nullable', 'numeric', 'min:0'],
            'value_of_acc' => ['nullable', 'numeric', 'min:0'],
            'actual_expenditures' => ['nullable', 'numeric', 'min:0'],
            'acc_month' => ['required', Rule::in(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'])],
            'acc_year' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
            'acc_phy' => ['nullable', 'numeric', 'between:0,100'],
            'acc_fin' => ['nullable', 'numeric', 'between:0,100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = collect($validated)
            ->except(['acc_month', 'acc_year', 'acc_phy', 'acc_fin'])
            ->toArray();

        $month = $validated['acc_month'];
        $data[$month . '_phy'] = $validated['acc_phy'] ?? null;
        $data[$month . '_fin'] = $validated['acc_fin'] ?? null;
        $data['acc_year'] = $validated['acc_year'];

        FsdeProject::create($data);

        return $this->successResponse($request, 'FSDE data successfully added!');
    }

    public function destroyHydroGeo(Request $request, $id)
    {
        $project = HydroGeoProject::findOrFail($id);
        $project->delete();

        return $this->successResponse($request, 'Hydro-Geo data deleted successfully.');
    }

    public function destroyFsde(Request $request, $id)
    {
        $project = FsdeProject::findOrFail($id);
        $project->delete();

        return $this->successResponse($request, 'FSDE data deleted successfully.');
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
        // if ($resolution->team !== 'fs_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }
}
