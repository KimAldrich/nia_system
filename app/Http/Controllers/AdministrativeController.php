<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\AdministrativeDocument;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdministrativeController extends Controller
{
    use HandlesAsyncRequests;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

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
        $fileValidationMessages = [
            'title.required' => 'Please enter a document title.',
            'title.max' => 'The document title must not exceed 255 characters.',
            'document_type.required' => 'Please select a document type.',
            'document_type.in' => 'Please select a valid document type.',
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'Only document files are allowed.',
            'file.mimes' => 'Only document files are allowed. Please upload PDF, DOC, DOCX, XLS, or XLSX files only.',
            'file.max' => 'Each file must not be larger than 10 MB.',
        ];

        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|in:memorandum,minutes',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ], $fileValidationMessages);

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

            $documentTypeLabel = $request->document_type === 'minutes' ? 'meeting minutes' : 'memorandum';
            $actorLabel = $this->notifications()->actorLabel($request->user());
            $this->notifications()->notifyByActorScope(
                $request->user(),
                $request->user()->role,
                ucfirst($documentTypeLabel) . ' uploaded',
                "{$actorLabel} uploaded {$request->title} to the {$documentTypeLabel} hub.",
                [
                    'type' => $request->document_type,
                ]
            );

            return $this->successResponse($request, 'Document uploaded successfully to ' . ucfirst($request->document_type) . '.');
        }

        return $this->errorResponse($request, 'File upload failed.');
    }

    // 3. Securely Delete a Document
    public function destroy(Request $request, $id)
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

        return $this->successResponse($request, 'Document removed successfully.');
    }
}
