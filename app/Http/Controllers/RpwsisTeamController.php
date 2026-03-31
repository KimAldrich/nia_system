<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;

class RpwsisTeamController extends Controller
{
    // 1. Dashboard
    public function index()
    {
        $resolutions = IaResolution::where('team', 'rpwsis_team')->latest()->get();
        $events = Event::whereDate('event_date', '>=', now())->orderBy('event_date', 'asc')->take(5)->get();

        $categories = EventCategory::all();
        return view('rpwsis_team.dashboard', compact('resolutions', 'events', 'categories'));
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
        return back()->with('success', 'File uploaded successfully.');
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

        return back()->with('success', 'File updated successfully.');
    }

    // 6. Delete Downloadable
    public function deleteForm($id)
    {
        $downloadable = Downloadable::findOrFail($id);

        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $downloadable->delete();

        return back()->with('success', 'File deleted successfully.');
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
        return back()->with('success', 'Resolution uploaded successfully.');
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

        return back()->with('success', 'Resolution updated successfully.');
    }

    // 8. Update Resolution Status
    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolution->update(['status' => $request->status]);

        return back()->with('success', 'Resolution status updated successfully.');
    }
}