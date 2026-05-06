<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\IaResolutionFile;
use App\Models\Downloadable;
use App\Models\Event;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\PcrStatusReport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PcrTeamController extends Controller
{
    use HandlesAsyncRequests;
    use BuildsResolutionAnalytics;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

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

    public function index(Request $request)
    {
        $resolutions = IaResolution::where('team', 'pcr_team')
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

        $categories = EventCategory::all();
        $analytics = $this->buildResolutionAnalytics('pcr_team');
        $pcrQuery = PcrStatusReport::query();
        if ($request->filled('pcr_search')) {
            $search = trim((string) $request->input('pcr_search'));
            $pcrQuery->where(function ($query) use ($search) {
                $query->where('fund_source', 'like', "%{$search}%")
                    ->orWhere('allocation', 'like', "%{$search}%");
            });
        }
        if ($request->filled('pcr_fund_source')) {
            $pcrQuery->where('fund_source', $request->input('pcr_fund_source'));
        }

        $pcrStatusReports = $pcrQuery->orderByDesc('fund_source')->paginate(8, ['*'], 'pcr_page')->withQueryString();
        $pcrFundSources = PcrStatusReport::select('fund_source')->whereNotNull('fund_source')->distinct()->orderByDesc('fund_source')->pluck('fund_source');
        return view('pcr_team.dashboard', compact('resolutions', 'events', 'paginatedEvents', 'categories', 'analytics', 'pcrStatusReports', 'pcrFundSources'));
    }

    public function downloadables()
    {
        $files = Downloadable::where('team', 'pcr_team')->get();
        return view('pcr_team.downloadables', compact('files'));
    }

    public function resolutions()
    {
        $resolutions = IaResolution::with('files')->where('team', 'pcr_team')->latest()->get();
        return view('shared.team-resolutions', [
            'pageTitle' => 'Program Completion Report Files',
            'headerTitle' => 'Program Completion Report Files',
            'headerDesc' => 'Manage status entries and attached files for the Program Completion team.',
            'teamRole' => 'pcr_team',
            'uploadRouteName' => 'pcr.resolutions.upload',
            'deleteRouteName' => 'pcr.resolutions.delete',
            'resolutions' => $resolutions,
        ]);
    }

    public function uploadForm(Request $request)
    {
        $fileValidationMessages = [
            'documents.required' => 'Please select at least one file to upload.',
            'documents.array' => 'Please upload valid files only.',
            'documents.min' => 'Please select at least one file to upload.',
            'document.required' => 'Please select a file to upload.',
            'document.file' => 'Only document files are allowed.',
            'document.mimes' => 'Only document files are allowed. Please upload PDF, DOC, DOCX, XLS, or XLSX files only.',
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
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            ], $fileValidationMessages)->validate();

            $path = app(\App\Services\DocumentStorageService::class)->store($file, 'forms');
            $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

            Downloadable::create([
                'title' => $cleanTitle,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'team' => 'pcr_team'
            ]);
        }

        $message = $files->count() === 1
            ? 'File uploaded successfully.'
            : "{$files->count()} files uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('pcr_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $fileMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} downloadables."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} downloadables.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'pcr_team', 'Downloadables updated', $fileMessage, ['type' => 'downloadable', 'team' => 'pcr_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateForm(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx']);
        $downloadable = Downloadable::findOrFail($id);
        $file = $request->file('document');

        $previousName = $downloadable->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($downloadable->file_path);

        $path = app(\App\Services\DocumentStorageService::class)->store($file, 'forms');
        $downloadable->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $teamLabel = $this->notifications()->teamLabel('pcr_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'pcr_team', 'Downloadable updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'pcr_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'File updated successfully.');
    }

    public function deleteForm(Request $request, $id)
    {
        $downloadable = Downloadable::findOrFail($id);

        $deletedName = $downloadable->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($downloadable->file_path);

        $downloadable->delete();

        $teamLabel = $this->notifications()->teamLabel('pcr_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'pcr_team', 'Downloadable removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'pcr_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'File deleted successfully.');
    }

    public function uploadResolution(Request $request)
    {
        $fileValidationMessages = [
            'documents.required' => 'Please select at least one file to upload.',
            'documents.array' => 'Please upload valid files only.',
            'documents.min' => 'Please select at least one file to upload.',
            'document.required' => 'Please select a file to upload.',
            'document.file' => 'Only document files are allowed.',
            'document.mimes' => 'Only document files are allowed. Please upload PDF, DOC, DOCX, XLS, or XLSX files only.',
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
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            ], $fileValidationMessages)->validate();

            IaResolution::attachUploadedFile($file, 'pcr_team');
        }

        $message = $files->count() === 1
            ? 'Resolution uploaded successfully.'
            : "{$files->count()} resolutions uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('pcr_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $resolutionMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} IA resolutions."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} IA resolutions.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'pcr_team', 'IA resolutions updated', $resolutionMessage, ['type' => 'ia_resolution', 'team' => 'pcr_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateResolution(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx']);
        $resolution = IaResolution::findOrFail($id);
        $file = $request->file('document');

        $previousName = $resolution->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($resolution->file_path);

        $path = app(\App\Services\DocumentStorageService::class)->store($file, 'resolutions');
        $resolution->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $resolutionTeam = $resolution->team ?: 'pcr_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolutionTeam = $resolution->team ?: 'pcr_team';
        $previousStatus = $resolution->status ?: 'no status';
        $updatedStatus = IaResolution::normalizeStatusForTeam($request->status, $resolutionTeam);
        $resolution->update(['status' => $updatedStatus]);
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $previousStatusLabel = IaResolution::displayStatusLabel($previousStatus, $resolutionTeam);
        $updatedStatusLabel = IaResolution::displayStatusLabel($updatedStatus, $resolutionTeam);
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution status changed', "{$actorLabel} changed the status of {$resolution->title} in {$teamLabel} from {$previousStatusLabel} to {$updatedStatusLabel}.", ['type' => 'ia_resolution_status', 'team' => $resolutionTeam, 'team_label' => $teamLabel, 'status' => $updatedStatus]);

        return $this->successResponse($request, 'Resolution status updated successfully.');
    }

    // 9. Delete IA Resolution
    public function deleteResolution(Request $request, $id)
    {
        $resolutionFile = IaResolutionFile::with('resolution')->findOrFail($id);
        $resolution = $resolutionFile->resolution;
        $deletedName = $resolutionFile->original_name;
        app(\App\Services\DocumentStorageService::class)->delete($resolutionFile->file_path);

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'pcr_team') {
        //     abort(403);
        // }

        $resolutionFile->delete();

        if ($resolution) {
            if ($resolution->files()->exists()) {
                $resolution->refreshPrimaryAttachment();
            } else {
                $resolution->delete();
            }
        }

        $resolutionTeam = $resolution?->team ?: 'pcr_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }

    public function storePcrStatus(Request $request)
    {
        PcrStatusReport::create($this->validatePcrStatus($request));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Added successfully.',
            ]);
        }

        return back()->with('pcr_status_success', 'Added successfully.');
    }

    public function updatePcrStatus(Request $request)
    {
        $validated = $this->validatePcrStatus($request, true);
        $report = PcrStatusReport::findOrFail($validated['id']);
        $report->update(collect($validated)->except('id')->toArray());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Updated successfully.',
            ]);
        }

        return back()->with('pcr_status_success', 'Updated successfully.');
    }

    public function deletePcrStatus(Request $request, $id)
    {
        PcrStatusReport::findOrFail($id)->delete();

        return $this->successResponse($request, 'PCR status data deleted successfully.');
    }

    public function exportPcrStatusExcel(Request $request): StreamedResponse
    {
        $query = PcrStatusReport::query();
        if ($request->filled('pcr_search')) {
            $search = trim((string) $request->input('pcr_search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('fund_source', 'like', "%{$search}%")
                    ->orWhere('allocation', 'like', "%{$search}%");
            });
        }
        if ($request->filled('pcr_fund_source')) {
            $query->where('fund_source', $request->input('pcr_fund_source'));
        }

        $rows = $query->orderByDesc('fund_source')->get();
        $dateLabel = now()->format('F j, Y');
        $filenameParts = ['PCR STATUS as of', $dateLabel];
        if ($request->filled('pcr_search')) {
            $filenameParts[] = 'Search';
            $filenameParts[] = trim((string) $request->input('pcr_search'));
        }
        if ($request->filled('pcr_fund_source')) {
            $filenameParts[] = 'Fund Source';
            $filenameParts[] = $request->input('pcr_fund_source');
        }
        $filename = collect($filenameParts)->filter()->implode(' ') . '.xlsx';
        $filename = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $filename);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PCR Status');

        foreach ([
            'A' => 14,
            'B' => 16,
            'C' => 18,
            'D' => 18,
            'E' => 24,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 22,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');
        $sheet->mergeCells('F4:F5');
        $sheet->mergeCells('G4:I4');

        $sheet->setCellValue('A1', 'PROJECT COMPLETION REPORT STATUS MONITORING');
        $sheet->setCellValue('A2', 'AS OF ' . strtoupper($dateLabel));
        $sheet->setCellValue('A4', 'FUND SOURCE');
        $sheet->setCellValue('B4', 'NO. OF CONTRACTS');
        $sheet->setCellValue('C4', 'ALLOCATION');
        $sheet->setCellValue('D4', 'NO. OF PCR PREPARED');
        $sheet->setCellValue('E4', 'NO. OF PCR SUBMITTED TO REGIONAL OFFICE');
        $sheet->setCellValue('F4', 'ACCOMPLISHMENT (PREPARED/NO. OF CONTRACTS)');
        $sheet->setCellValue('G4', 'REMARKS');
        $sheet->setCellValue('G5', 'FOR SIGNING OF IA, CHIEF, DM, RM');
        $sheet->setCellValue('H5', 'FOR SUBMISSION TO RO1');
        $sheet->setCellValue('I5', 'NOT YET PREPARED / PENDING DETAILS');

        $sheet->getStyle('A1:I2')->applyFromArray([
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

        $sheet->getStyle('A4:I5')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 10,
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

        $rowIndex = 6;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$rowIndex}", $row->fund_source);
            $sheet->setCellValue("B{$rowIndex}", $row->no_of_contracts);
            $sheet->setCellValue("C{$rowIndex}", (float) $row->allocation);
            $sheet->setCellValue("D{$rowIndex}", $row->no_of_pcr_prepared);
            $sheet->setCellValue("E{$rowIndex}", $row->no_of_pcr_submitted_to_regional_office);
            $sheet->setCellValue("F{$rowIndex}", (float) $row->accomplishment_percentage / 100);
            $sheet->setCellValue("G{$rowIndex}", $row->for_signing_of_ia_chief_dm_rm);
            $sheet->setCellValue("H{$rowIndex}", $row->for_submission_to_ro1);
            $sheet->setCellValue("I{$rowIndex}", $row->not_yet_prepared_pending_details);
            $rowIndex++;
        }

        if ($rows->isNotEmpty()) {
            $sheet->setCellValue("A{$rowIndex}", 'TOTAL');
            $sheet->setCellValue("B{$rowIndex}", $rows->sum('no_of_contracts'));
            $sheet->setCellValue("C{$rowIndex}", (float) $rows->sum('allocation'));
            $sheet->setCellValue("D{$rowIndex}", $rows->sum('no_of_pcr_prepared'));
            $sheet->setCellValue("E{$rowIndex}", $rows->sum('no_of_pcr_submitted_to_regional_office'));
            $sheet->setCellValue("G{$rowIndex}", $rows->sum('for_signing_of_ia_chief_dm_rm'));
            $sheet->setCellValue("H{$rowIndex}", $rows->sum('for_submission_to_ro1'));
            $sheet->setCellValue("I{$rowIndex}", $rows->sum('not_yet_prepared_pending_details'));
            $sheet->getStyle("A{$rowIndex}:I{$rowIndex}")->applyFromArray([
                'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EAF4E2'],
                ],
            ]);
        }

        $lastRow = max($rowIndex, 6);
        $sheet->getStyle("A6:I{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C6:C{$lastRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');
        $sheet->getStyle("F6:F{$lastRow}")->getNumberFormat()->setFormatCode('0.00%');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
