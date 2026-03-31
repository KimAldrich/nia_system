<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdministrativeDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdministrativeController extends Controller
{
    // 1. Load the Shared Page
    public function index()
    {
        // Fetch all documents, categorized
        $memorandums = AdministrativeDocument::where('document_type', 'memorandum')->latest()->get();
        $minutes = AdministrativeDocument::where('document_type', 'minutes')->latest()->get();

        return view('shared.administrative', compact('memorandums', 'minutes'));
    }

    // 2. Upload a Document
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|in:memorandum,minutes',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            // Save inside an 'administrative' folder in storage
            $path = $file->store('administrative', 'public');

            AdministrativeDocument::create([
                'title' => $request->title,
                'document_type' => $request->document_type,
                'file_path' => $path,
                'original_name' => $originalName,
                'user_id' => Auth::id(), // Logs the exact user!
                'team_role' => Auth::user()->role,
            ]);

            return back()->with('success', 'Document uploaded successfully to ' . ucfirst($request->document_type) . '.');
        }

        return back()->withErrors(['file' => 'File upload failed.']);
    }

    // 3. Securely Delete a Document
    public function destroy($id)
    {
        $document = AdministrativeDocument::findOrFail($id);

        // SECURITY CHECK: Only members of the exact team OR the Admin can delete this!
        if (Auth::user()->role !== $document->team_role && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Your team did not upload this document.');
        }

        // Delete physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete database record
        $document->delete();

        return back()->with('success', 'Document removed successfully.');
    }
}