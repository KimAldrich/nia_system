<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\RpwsisAccomplishment;
use App\Models\EventCategory;
use App\Models\RpwsisAccomplishmentSummary;
use App\Models\RpwsisNurseryEstablishment;
use App\Models\RpwsisSignage;
use App\Models\RpwsisInfrastructure; // ✅ NEW IMPORT
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
class RpwsisTeamController extends Controller
{
    use HandlesAsyncRequests;
    use BuildsResolutionAnalytics;

    private function validateAccomplishment(Request $request): array
    {
        return $request->validate([
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
        ] + collect(range(1, 12))->mapWithKeys(fn($index) => [
            'c' . $index => ['nullable', 'string', 'max:255'],
        ])->toArray());
    }

    private function validateSummary(Request $request): array
    {
        return $request->validate([
            'sum_region' => ['required', 'string', 'max:100'],
            'sum_province' => ['required', 'string', 'max:100'],
            'sum_municipality' => ['required', 'string', 'max:100'],
            'sum_barangay' => ['required', 'string', 'max:255'],
            'sum_type' => ['required', 'string', 'max:255'],
            'sum_year' => ['required', 'digits:4', 'integer', 'min:1900', 'max:2100'],
            'sum_target_1' => ['nullable', 'string', 'max:255'],
            'sum_area_planted' => ['nullable', 'string', 'max:255'],
            'sum_species' => ['nullable', 'string', 'max:5000'],
            'sum_spacing' => ['nullable', 'string', 'max:255'],
            'sum_maintenance' => ['nullable', 'string', 'max:255'],
            'sum_target_2' => ['nullable', 'string', 'max:255'],
            'sum_actual' => ['nullable', 'string', 'max:255'],
            'sum_mortality' => ['nullable', 'string', 'max:255'],
            'sum_replanted' => ['nullable', 'string', 'max:5000'],
            'sum_nis' => ['nullable', 'string', 'max:255'],
            'sum_remarks' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validateNursery(Request $request): array
    {
        return $request->validate([
            'nur_region' => ['required', 'string', 'max:100'],
            'nur_province' => ['required', 'string', 'max:100'],
            'nur_municipality' => ['required', 'string', 'max:100'],
            'nur_barangay' => ['required', 'string', 'max:255'],
            'nur_x_coord' => ['nullable', 'string', 'max:255'],
            'nur_y_coord' => ['nullable', 'string', 'max:255'],
            'nur_seedlings' => ['nullable', 'string', 'max:255'],
            'nur_type' => ['required', 'string', 'max:255'],
            'nur_nis' => ['nullable', 'string', 'max:255'],
            'nur_remarks' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validateSignages(Request $request): array
    {
        return $request->validate([
            'sig_region' => ['required', 'string', 'max:100'],
            'sig_province' => ['required', 'string', 'max:100'],
            'sig_municipality' => ['required', 'string', 'max:100'],
            'sig_barangay' => ['required', 'string', 'max:255'],
            'sig_x_coord' => ['nullable', 'string', 'max:255'],
            'sig_y_coord' => ['nullable', 'string', 'max:255'],
            'sig_type' => ['required', 'string', 'max:255'],
            'sig_nis' => ['nullable', 'string', 'max:255'],
            'sig_remarks' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validateInfrastructure(Request $request): array
    {
        return $request->validate([
            'inf_region' => ['required', 'string', 'max:100'],
            'inf_province' => ['required', 'string', 'max:100'],
            'inf_municipality' => ['required', 'string', 'max:100'],
            'inf_barangay' => ['required', 'string', 'max:5000'],
            'inf_x_coord' => ['nullable', 'string', 'max:5000'],
            'inf_y_coord' => ['nullable', 'string', 'max:5000'],
            'inf_type' => ['required', 'string', 'max:5000'],
            'inf_nis' => ['nullable', 'string', 'max:255'],
            'inf_remarks' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    // 1. Dashboard
    public function index()
    {
        $resolutions = IaResolution::where('team', 'rpwsis_team')
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

        // ✅ ADDED THIS: Fetch records to fix the "undefined $records" error
        $records = RpwsisAccomplishment::latest()->get();

        // ✅ ADDED THIS: Fetch records for the new Summary Table
        $summaryRecords = RpwsisAccomplishmentSummary::latest()->get();

        //nuresery
        $nurseryRecords = RpwsisNurseryEstablishment::latest()->get();

        // ✅ FETCH SIGNAGE RECORDS
        $signageRecords = RpwsisSignage::latest()->get();

        // ✅ FETCH INFRASTRUCTURE RECORDS
        $infrastructureRecords = RpwsisInfrastructure::latest()->get();

        $categories = EventCategory::all();
        $analytics = $this->buildResolutionAnalytics('rpwsis_team');
        return view('rpwsis_team.dashboard', compact('resolutions', 'events', 'paginatedEvents', 'categories', 'analytics', 'records', 'summaryRecords', 'nurseryRecords', 'signageRecords', 'infrastructureRecords'));
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
                'team' => 'rpwsis_team'
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
                'team' => 'rpwsis_team'
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
        $validated = $this->validateAccomplishment($request);

        $record = RpwsisAccomplishment::create($validated);

        return $this->successResponse($request, 'Added successfully.', [
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

    public function updateAccomplishment(Request $request, $id)
    {
        $validated = $this->validateAccomplishment($request);

        $record = RpwsisAccomplishment::findOrFail($id);
        $record->update($validated);

        return $this->successResponse($request, 'Updated successfully.', [
            'record' => $record->fresh(),
        ]);
    }

    // ----------------------------------------------------------------------
    // ✅ NEW METHODS FOR THE SUMMARY OF ACCOMPLISHMENT TABLE
    // ----------------------------------------------------------------------

    // 12. Store Summary Accomplishment
    public function storeSummary(Request $request)
    {
        $validated = $this->validateSummary($request);

        $record = RpwsisAccomplishmentSummary::create([
            'region' => $validated['sum_region'],
            'province' => $validated['sum_province'],
            'municipality' => $validated['sum_municipality'],
            'barangay' => $validated['sum_barangay'],
            'plantation_type' => $validated['sum_type'],
            'year_established' => $validated['sum_year'],
            'target_area_1' => $validated['sum_target_1'] ?? null,
            'area_planted' => $validated['sum_area_planted'] ?? null,
            'species_planted' => $validated['sum_species'] ?? null,
            'spacing' => $validated['sum_spacing'] ?? null,
            'maintenance' => $validated['sum_maintenance'] ?? null,
            'target_area_2' => $validated['sum_target_2'] ?? null,
            'actual_area' => $validated['sum_actual'] ?? null,
            'mortality_rate' => $validated['sum_mortality'] ?? null,
            'species_replanted' => $validated['sum_replanted'] ?? null,
            'nis_name' => $validated['sum_nis'] ?? null,
            'remarks' => $validated['sum_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Added successfully.', [
            'record' => $record,
        ]);
    }

    public function updateSummary(Request $request, $id)
    {
        $validated = $this->validateSummary($request);
        $record = RpwsisAccomplishmentSummary::findOrFail($id);
        $record->update([
            'region' => $validated['sum_region'],
            'province' => $validated['sum_province'],
            'municipality' => $validated['sum_municipality'],
            'barangay' => $validated['sum_barangay'],
            'plantation_type' => $validated['sum_type'],
            'year_established' => $validated['sum_year'],
            'target_area_1' => $validated['sum_target_1'] ?? null,
            'area_planted' => $validated['sum_area_planted'] ?? null,
            'species_planted' => $validated['sum_species'] ?? null,
            'spacing' => $validated['sum_spacing'] ?? null,
            'maintenance' => $validated['sum_maintenance'] ?? null,
            'target_area_2' => $validated['sum_target_2'] ?? null,
            'actual_area' => $validated['sum_actual'] ?? null,
            'mortality_rate' => $validated['sum_mortality'] ?? null,
            'species_replanted' => $validated['sum_replanted'] ?? null,
            'nis_name' => $validated['sum_nis'] ?? null,
            'remarks' => $validated['sum_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Updated successfully.', [
            'record' => $record->fresh(),
        ]);
    }

    // 13. Delete Summary Accomplishment
    public function deleteSummary($id)
    {
        $record = RpwsisAccomplishmentSummary::findOrFail($id);
        $record->delete();

        return response()->json(['success' => true]);
    }

    //14
    // ----------------------------------------------------------------------
    // ✅ NEW: NURSERY ESTABLISHMENT TABLE
    // ----------------------------------------------------------------------

    public function storeNursery(Request $request)
    {
        $validated = $this->validateNursery($request);
        $record = RpwsisNurseryEstablishment::create([
            'region'             => $validated['nur_region'],
            'province'           => $validated['nur_province'],
            'municipality'       => $validated['nur_municipality'],
            'barangay'           => $validated['nur_barangay'],
            'x_coordinates'      => $validated['nur_x_coord'] ?? null,
            'y_coordinates'      => $validated['nur_y_coord'] ?? null,
            'seedlings_produced' => $validated['nur_seedlings'] ?? null,
            'nursery_type'       => $validated['nur_type'],
            'nis_name'           => $validated['nur_nis'] ?? null,
            'remarks'            => $validated['nur_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Added successfully.', [
            'record' => $record,
        ]);
    }

    public function deleteNursery($id)
    {
        $record = RpwsisNurseryEstablishment::findOrFail($id);
        $record->delete();
        
        return response()->json(['success' => true]);
    }

    public function updateNursery(Request $request, $id)
    {
        $validated = $this->validateNursery($request);
        $record = RpwsisNurseryEstablishment::findOrFail($id);
        $record->update([
            'region'             => $validated['nur_region'],
            'province'           => $validated['nur_province'],
            'municipality'       => $validated['nur_municipality'],
            'barangay'           => $validated['nur_barangay'],
            'x_coordinates'      => $validated['nur_x_coord'] ?? null,
            'y_coordinates'      => $validated['nur_y_coord'] ?? null,
            'seedlings_produced' => $validated['nur_seedlings'] ?? null,
            'nursery_type'       => $validated['nur_type'],
            'nis_name'           => $validated['nur_nis'] ?? null,
            'remarks'            => $validated['nur_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Updated successfully.', [
            'record' => $record->fresh(),
        ]);
    }

    // ----------------------------------------------------------------------
    // ✅ NEW: INFORMATIVE SIGNAGES TABLE
    // ----------------------------------------------------------------------

    public function storeSignages(Request $request)
    {
        $validated = $this->validateSignages($request);
        $record = RpwsisSignage::create([
            'region'        => $validated['sig_region'],
            'province'      => $validated['sig_province'],
            'municipality'  => $validated['sig_municipality'],
            'barangay'      => $validated['sig_barangay'],
            'x_coordinates' => $validated['sig_x_coord'] ?? null,
            'y_coordinates' => $validated['sig_y_coord'] ?? null,
            'signage_type'  => $validated['sig_type'],
            'nis_name'      => $validated['sig_nis'] ?? null,
            'remarks'       => $validated['sig_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Added successfully.', [
            'record' => $record,
        ]);
    }

    public function deleteSignages($id)
    {
        $record = RpwsisSignage::findOrFail($id);
        $record->delete();
        
        return response()->json(['success' => true]);
    }

    public function updateSignages(Request $request, $id)
    {
        $validated = $this->validateSignages($request);
        $record = RpwsisSignage::findOrFail($id);
        $record->update([
            'region'        => $validated['sig_region'],
            'province'      => $validated['sig_province'],
            'municipality'  => $validated['sig_municipality'],
            'barangay'      => $validated['sig_barangay'],
            'x_coordinates' => $validated['sig_x_coord'] ?? null,
            'y_coordinates' => $validated['sig_y_coord'] ?? null,
            'signage_type'  => $validated['sig_type'],
            'nis_name'      => $validated['sig_nis'] ?? null,
            'remarks'       => $validated['sig_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Updated successfully.', [
            'record' => $record->fresh(),
        ]);
    }


    // ----------------------------------------------------------------------
    // ✅ NEW: OTHER INFRASTRUCTURES TABLE
    // ----------------------------------------------------------------------

    public function storeInfrastructure(Request $request)
    {
        $validated = $this->validateInfrastructure($request);
        $record = RpwsisInfrastructure::create([
            'region'              => $validated['inf_region'],
            'province'            => $validated['inf_province'],
            'municipality'        => $validated['inf_municipality'],
            'barangay'            => $validated['inf_barangay'],
            'x_coordinates'       => $validated['inf_x_coord'] ?? null,
            'y_coordinates'       => $validated['inf_y_coord'] ?? null,
            'infrastructure_type' => $validated['inf_type'],
            'nis_name'            => $validated['inf_nis'] ?? null,
            'remarks'             => $validated['inf_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Added successfully.', [
            'record' => $record,
        ]);
    }

    public function deleteInfrastructure($id)
    {
        $record = RpwsisInfrastructure::findOrFail($id);
        $record->delete();
        return response()->json(['success' => true]);
    }

    public function updateInfrastructure(Request $request, $id)
    {
        $validated = $this->validateInfrastructure($request);
        $record = RpwsisInfrastructure::findOrFail($id);
        $record->update([
            'region'              => $validated['inf_region'],
            'province'            => $validated['inf_province'],
            'municipality'        => $validated['inf_municipality'],
            'barangay'            => $validated['inf_barangay'],
            'x_coordinates'       => $validated['inf_x_coord'] ?? null,
            'y_coordinates'       => $validated['inf_y_coord'] ?? null,
            'infrastructure_type' => $validated['inf_type'],
            'nis_name'            => $validated['inf_nis'] ?? null,
            'remarks'             => $validated['inf_remarks'] ?? null,
        ]);

        return $this->successResponse($request, 'Updated successfully.', [
            'record' => $record->fresh(),
        ]);
    }

    // ----------------------------------------------------------------------

    public function exportAccomplishmentExcel(Request $request): StreamedResponse
    {
        $rows = RpwsisAccomplishment::orderBy('region')
            ->orderBy('batch')
            ->orderBy('allocation')
            ->orderBy('nis')
            ->get();
        $currentDate = now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Accomplishment');

        foreach ([
            'A' => 10,
            'B' => 12,
            'C' => 14,
            'D' => 18,
            'E' => 28,
            'F' => 24,
            'G' => 16,
            'H' => 18,
            'I' => 20,
            'J' => 18,
            'K' => 22,
            'L' => 20,
            'M' => 22,
            'N' => 22,
            'O' => 24,
            'P' => 24,
            'Q' => 22,
            'R' => 22,
            'S' => 24,
            'T' => 10,
            'U' => 10,
            'V' => 16,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        foreach (range(1, 8) as $rowNumber) {
            $sheet->getRowDimension($rowNumber)->setRowHeight(20);
        }
        $sheet->getRowDimension(9)->setRowHeight(26);
        $sheet->getRowDimension(10)->setRowHeight(24);
        $sheet->getRowDimension(11)->setRowHeight(42);

        foreach (range('A', 'V') as $column) {
            $sheet->getStyle("{$column}1:{$column}999")->getAlignment()->setWrapText(true);
        }

        $sheet->mergeCells('A1:V1');
        $sheet->mergeCells('A2:V2');
        $sheet->mergeCells('A3:V3');
        $sheet->mergeCells('A4:V4');
        $sheet->mergeCells('A5:V5');
        $sheet->mergeCells('A7:V7');

        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->setCellValue('A2', 'OFFICE OF THE PRESIDENT');
        $sheet->setCellValue('A3', 'NATIONAL IRRIGATION ADMINISTRATION');
        $sheet->setCellValue('A4', 'Regional Office I (Ilocos Region)');
        $sheet->setCellValue('A5', 'Pangasinan Irrigation Management Office');
        $sheet->setCellValue('A7', 'ACCOMPLISHMENT OF SOCIAL AND ENVIRONMENTAL As of ' . $currentDate->format('F j, Y'));

        $sheet->getStyle('A1:V5')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A4:A5')->getFont()->setSize(10);
        $sheet->getStyle('A7')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'T', 'U', 'V'] as $column) {
            $sheet->mergeCells("{$column}9:{$column}11");
        }
        $sheet->mergeCells('H9:S9');
        $sheet->mergeCells('H10:O10');
        $sheet->mergeCells('P10:R10');
        $sheet->mergeCells('S10:S11');

        $sheet->setCellValue('A9', 'REGION');
        $sheet->setCellValue('B9', 'BATCH');
        $sheet->setCellValue('C9', 'ALLOCATION');
        $sheet->setCellValue('D9', 'NIS');
        $sheet->setCellValue('E9', 'ACTIVITY TYPE');
        $sheet->setCellValue('F9', 'REMARKS');
        $sheet->setCellValue('G9', 'AMOUNT');
        $sheet->setCellValue('H9', 'B. Implementation Stage');
        $sheet->setCellValue('H10', '1. Preparation and Establishment');
        $sheet->setCellValue('P10', '2. Conduct of IEC');
        $sheet->setCellValue('S10', '3. Monitoring and Evaluation');
        $sheet->setCellValue('H11', 'POW Formulation');
        $sheet->setCellValue('I11', 'Nursery area/Bunk House/STW');
        $sheet->setCellValue('J11', 'Seedling Production');
        $sheet->setCellValue('K11', 'Procurement');
        $sheet->setCellValue('L11', 'Site Preparation');
        $sheet->setCellValue('M11', 'Vegetative enhancement');
        $sheet->setCellValue('N11', 'Establishment of Wattling');
        $sheet->setCellValue('O11', 'Right of Way/Rent/Wages of Caretaker/');
        $sheet->setCellValue('P11', 'Conduct of consultative meetings');
        $sheet->setCellValue('Q11', 'Distribution of reading materials');
        $sheet->setCellValue('R11', 'Installation of signboards/signages');
        $sheet->setCellValue('S11', 'Supervision and Monitoring of implementations');
        $sheet->setCellValue('T9', 'PHY %');
        $sheet->setCellValue('U9', 'FIN %');
        $sheet->setCellValue('V9', 'EXPENDITURES');

        $sheet->getStyle('A9:V11')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 9,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $currentRow = 12;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$currentRow}", $row->region);
            $sheet->setCellValue("B{$currentRow}", $row->batch);
            $sheet->setCellValue("C{$currentRow}", $row->allocation);
            $sheet->setCellValue("D{$currentRow}", $row->nis);
            $sheet->setCellValue("E{$currentRow}", $row->activity);
            $sheet->setCellValue("F{$currentRow}", $row->remarks);
            $sheet->setCellValue("G{$currentRow}", $row->amount === null ? '' : (float) $row->amount);
            $sheet->setCellValue("H{$currentRow}", $row->c1);
            $sheet->setCellValue("I{$currentRow}", $row->c2);
            $sheet->setCellValue("J{$currentRow}", $row->c3);
            $sheet->setCellValue("K{$currentRow}", $row->c4);
            $sheet->setCellValue("L{$currentRow}", $row->c5);
            $sheet->setCellValue("M{$currentRow}", $row->c6);
            $sheet->setCellValue("N{$currentRow}", $row->c7);
            $sheet->setCellValue("O{$currentRow}", $row->c8);
            $sheet->setCellValue("P{$currentRow}", $row->c9);
            $sheet->setCellValue("Q{$currentRow}", $row->c10);
            $sheet->setCellValue("R{$currentRow}", $row->c11);
            $sheet->setCellValue("S{$currentRow}", $row->c12);
            $sheet->setCellValue("T{$currentRow}", $row->phy === null ? '' : (float) $row->phy);
            $sheet->setCellValue("U{$currentRow}", $row->fin === null ? '' : (float) $row->fin);
            $sheet->setCellValue("V{$currentRow}", $row->exp === null ? '' : (float) $row->exp);
            $sheet->getRowDimension($currentRow)->setRowHeight(38);
            $currentRow++;
        }

        $dataEndRow = max($currentRow - 1, 12);
        $sheet->getStyle("A12:V{$dataEndRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        foreach (['A', 'B', 'C', 'G', 'T', 'U', 'V'] as $column) {
            $sheet->getStyle("{$column}12:{$column}{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['D', 'E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'] as $column) {
            $sheet->getStyle("{$column}12:{$column}{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (['G', 'V'] as $column) {
            $sheet->getStyle("{$column}12:{$column}{$dataEndRow}")
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }
        foreach (['T', 'U'] as $column) {
            $sheet->getStyle("{$column}12:{$column}{$dataEndRow}")
                ->getNumberFormat()->setFormatCode('0.00');
        }

        $signatureLabelRow = $dataEndRow + 3;
        $signatureNameRow = $signatureLabelRow + 2;
        $signatureTitleRow = $signatureLabelRow + 3;

        $sheet->setCellValue("B{$signatureLabelRow}", 'Prepared by:');
        $sheet->setCellValue("G{$signatureLabelRow}", 'Checked by:');
        $sheet->setCellValue("L{$signatureLabelRow}", 'Reviewed by:');
        $sheet->setCellValue("Q{$signatureLabelRow}", 'Submitted by:');

        $sheet->mergeCells("A{$signatureNameRow}:D{$signatureNameRow}");
        $sheet->mergeCells("F{$signatureNameRow}:I{$signatureNameRow}");
        $sheet->mergeCells("K{$signatureNameRow}:N{$signatureNameRow}");
        $sheet->mergeCells("P{$signatureNameRow}:V{$signatureNameRow}");
        $sheet->mergeCells("A{$signatureTitleRow}:D{$signatureTitleRow}");
        $sheet->mergeCells("F{$signatureTitleRow}:I{$signatureTitleRow}");
        $sheet->mergeCells("K{$signatureTitleRow}:N{$signatureTitleRow}");
        $sheet->mergeCells("P{$signatureTitleRow}:V{$signatureTitleRow}");

        $sheet->setCellValue("A{$signatureNameRow}", 'EILEEN N. PAROCHA');
        $sheet->setCellValue("F{$signatureNameRow}", 'ENGR. RENZ WILSON L. ETRATA');
        $sheet->setCellValue("K{$signatureNameRow}", 'ENGR. WEYNARD JOSEPH P. UNTALAN');
        $sheet->setCellValue("P{$signatureNameRow}", 'ENGR. JOHN N. MOLANO, MSME');
        $sheet->setCellValue("A{$signatureTitleRow}", 'Environmental Analyst');
        $sheet->setCellValue("F{$signatureTitleRow}", 'Senior Engineer A/ Head, Planning Unit');
        $sheet->setCellValue("K{$signatureTitleRow}", 'Principal Engineer C/ Chief, Engineering Section');
        $sheet->setCellValue("P{$signatureTitleRow}", 'Division Manager A, Pangasinan IMO');

        $sheet->getStyle("A{$signatureLabelRow}:V{$signatureTitleRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle("A{$signatureNameRow}:V{$signatureNameRow}")->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'ACCOMPLISHMENT OF SOCIAL AND ENVIRONMENTAL As of ' . $currentDate->format('F j, Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    public function exportSummaryExcel(Request $request): StreamedResponse
    {
        $rows = RpwsisAccomplishmentSummary::orderBy('region')
            ->orderBy('province')
            ->orderBy('municipality')
            ->orderBy('barangay')
            ->get();
        $currentDate = now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');

        foreach ([
            'A' => 12,
            'B' => 18,
            'C' => 18,
            'D' => 18,
            'E' => 24,
            'F' => 14,
            'G' => 14,
            'H' => 14,
            'I' => 34,
            'J' => 18,
            'K' => 24,
            'L' => 16,
            'M' => 16,
            'N' => 14,
            'O' => 28,
            'P' => 22,
            'Q' => 24,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        foreach (range(1, 8) as $rowNumber) {
            $sheet->getRowDimension($rowNumber)->setRowHeight(20);
        }
        $sheet->getRowDimension(7)->setRowHeight(24);
        $sheet->getRowDimension(8)->setRowHeight(24);
        $sheet->getRowDimension(10)->setRowHeight(44);

        foreach (range('A', 'Q') as $column) {
            $sheet->getStyle("{$column}1:{$column}999")->getAlignment()->setWrapText(true);
        }

        $sheet->mergeCells('A1:Q1');
        $sheet->mergeCells('A2:Q2');
        $sheet->mergeCells('A3:Q3');
        $sheet->mergeCells('A4:Q4');
        $sheet->mergeCells('A5:Q5');
        $sheet->mergeCells('A7:Q7');
        $sheet->mergeCells('A8:Q8');

        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->setCellValue('A2', 'OFFICE OF THE PRESIDENT');
        $sheet->setCellValue('A3', 'NATIONAL IRRIGATION ADMINISTRATION');
        $sheet->setCellValue('A4', 'Regional Office I (Ilocos Region)');
        $sheet->setCellValue('A5', 'Pangasinan Irrigation Management Office');
        $sheet->setCellValue('A7', 'REHABILITATION AND PROTECTION OF WATER RESOURCES SUPPORTING IRRIGATION SYSTEM');
        $sheet->setCellValue('A8', 'Summary of Accomplishment As of ' . $currentDate->format('F j, Y'));

        $sheet->getStyle('A1:Q5')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A4:A5')->getFont()->setSize(10);
        $sheet->getStyle('A7')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A8')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $headers = [
            'A10' => 'Region',
            'B10' => 'Province',
            'C10' => 'Municipality',
            'D10' => 'Barangay',
            'E10' => 'Type of Plantation',
            'F10' => 'Year Established',
            'G10' => 'Target Area',
            'H10' => 'Area Planted',
            'I10' => 'Species and Number of Seedlings Planted',
            'J10' => 'Spacing',
            'K10' => '1st Year Maintenance and Protection',
            'L10' => 'Replanting Target Area',
            'M10' => 'Replanting Actual Area',
            'N10' => 'Mortality Rate',
            'O10' => 'Species Replanted',
            'P10' => 'Name of NIS',
            'Q10' => 'Remarks',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A10:Q10')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 9,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $currentRow = 11;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$currentRow}", $row->region);
            $sheet->setCellValue("B{$currentRow}", $row->province);
            $sheet->setCellValue("C{$currentRow}", $row->municipality);
            $sheet->setCellValue("D{$currentRow}", $row->barangay);
            $sheet->setCellValue("E{$currentRow}", $row->plantation_type);
            $sheet->setCellValue("F{$currentRow}", $row->year_established);
            $sheet->setCellValue("G{$currentRow}", $row->target_area_1);
            $sheet->setCellValue("H{$currentRow}", $row->area_planted);
            $sheet->setCellValue("I{$currentRow}", $row->species_planted);
            $sheet->setCellValue("J{$currentRow}", $row->spacing);
            $sheet->setCellValue("K{$currentRow}", $row->maintenance);
            $sheet->setCellValue("L{$currentRow}", $row->target_area_2);
            $sheet->setCellValue("M{$currentRow}", $row->actual_area);
            $sheet->setCellValue("N{$currentRow}", $row->mortality_rate);
            $sheet->setCellValue("O{$currentRow}", $row->species_replanted);
            $sheet->setCellValue("P{$currentRow}", $row->nis_name);
            $sheet->setCellValue("Q{$currentRow}", $row->remarks);
            $sheet->getRowDimension($currentRow)->setRowHeight(42);
            $currentRow++;
        }

        $dataEndRow = max($currentRow - 1, 11);
        $sheet->getStyle("A11:Q{$dataEndRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        foreach (['A', 'F', 'G', 'H', 'L', 'M', 'N'] as $column) {
            $sheet->getStyle("{$column}11:{$column}{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['B', 'C', 'D', 'E', 'I', 'J', 'K', 'O', 'P', 'Q'] as $column) {
            $sheet->getStyle("{$column}11:{$column}{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'REHABILITATION AND PROTECTION OF WATER RESOURCES SUPPORTING IRRIGATION SYSTEM Summary of Accomplishment As of ' . $currentDate->format('F j, Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

}
