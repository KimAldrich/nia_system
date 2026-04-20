<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\PaoPowData;

class PaoTeamController extends Controller
{
    public function index()
    {
        $resolutions = IaResolution::where('team', 'pao_team')->latest()->get();
        $events = Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        $categories = EventCategory::all();
        $powData = PaoPowData::paginate(8);
        return view('pao_team.dashboard', compact('resolutions', 'events', 'categories', 'powData'));
    }

    public function downloadables()
    {
        $files = Downloadable::where('team', 'pao_team')->get();
        return view('pao_team.downloadables', compact('files'));
    }

    public function resolutions()
    {
        $resolutions = IaResolution::where('team', 'pao_team')->latest()->get();
        return view('pao_team.resolutions', compact('resolutions'));
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
            'team' => 'pao_team'
        ]);

        return back()->with('success', 'File uploaded successfully.');
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

        return back()->with('success', 'File updated successfully.');
    }

    public function deleteForm($id)
    {
        $downloadable = Downloadable::findOrFail($id);

        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $downloadable->delete();

        return back()->with('success', 'File deleted successfully.');
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
            'team' => 'pao_team'
        ]);

        return back()->with('success', 'Resolution uploaded successfully.');
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

        return back()->with('success', 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolution->update(['status' => $request->status]);

        return back()->with('success', 'Resolution status updated successfully.');
    }

    // 9. Delete IA Resolution
    public function deleteResolution($id)
    {
        $resolution = IaResolution::findOrFail($id);

        // Delete file from storage
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'pao_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return back()->with('success', 'Resolution deleted successfully.');
    }

    public function storePow(Request $request)
    {
        PaoPowData::create($request->all());
        return redirect()->back()->with('success', 'New Program of Works data added successfully!');
    }

    public function updatePow(Request $request)
    {
        $powData = PaoPowData::findOrFail($request->id);
        $powData->update($request->except('id'));
        return redirect()->back()->with('success', 'Program of Works data updated successfully!');
    }

    public function deletePow($id)
    {
        $powData = PaoPowData::findOrFail($id);
        $powData->delete();
        return redirect()->back()->with('success', 'Program of Works data deleted successfully!');
    }
}
