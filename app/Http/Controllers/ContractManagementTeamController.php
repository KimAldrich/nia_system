<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\ProcurementProject;

class ContractManagementTeamController extends Controller
{
    use HandlesAsyncRequests;

    public function index(Request $request)
    {
        $resolutions = IaResolution::where('team', 'cm_team')->latest()->get();
        $events = Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        $categories = EventCategory::all();
        $procCategories = ProcurementProject::select('category')->distinct()->pluck('category');

        // Filter logic
        $procQuery = ProcurementProject::query();
        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $procQuery->where('category', $request->proc_category);
        }

        // 🌟 THE FIX: Clone the query for the Excel Export BEFORE paginating! 🌟
        // This grabs 100% of the rows matching your filter.
        $procExportData = (clone $procQuery)->get();

        // Now we can safely paginate the original query for the HTML table
        $procurementProjects = $procQuery->paginate(10)->appends($request->query());

        return view('cm_team.dashboard', compact(
            'resolutions',
            'events',
            'categories',
            'procCategories',
            'procurementProjects',
            'procExportData'
        ));
    }

    public function downloadables()
    {
        $files = Downloadable::where('team', 'cm_team')->get();
        return view('cm_team.downloadables', compact('files'));
    }

    public function resolutions()
    {
        $resolutions = IaResolution::where('team', 'cm_team')->latest()->get();
        return view('cm_team.resolutions', compact('resolutions'));
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
            'team' => 'cm_team'
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
            'team' => 'cm_team'
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
        // if ($resolution->team !== 'cm_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }

    public function storeProcurement(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'proj_no' => ['required', 'string', 'max:50'],
            'name_of_project' => ['required', 'string', 'max:1000'],
            'municipality' => ['required', 'string', 'max:100'],
            'allocation' => ['nullable', 'numeric', 'min:0'],
            'abc' => ['nullable', 'numeric', 'min:0'],
            'bid_out' => ['nullable', 'integer', 'min:0'],
            'for_bidding' => ['nullable', 'integer', 'min:0'],
            'date_of_bidding' => ['nullable', 'date'],
            'awarded' => ['nullable', 'integer', 'min:0'],
            'date_of_award' => ['nullable', 'date', 'after_or_equal:date_of_bidding'],
            'contract_no' => ['nullable', 'string', 'max:100'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'name_of_contractor' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'project_description' => ['nullable', 'string', 'max:2000'],
        ]);

        ProcurementProject::create($validated);

        return $this->successResponse($request, 'New Procurement project successfully added!');
    }

    public function destroyProcurement(Request $request, $id)
    {
        ProcurementProject::findOrFail($id)->delete();
        return $this->successResponse($request, 'Project deleted!');
    }


}
