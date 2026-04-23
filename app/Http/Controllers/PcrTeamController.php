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
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
        $events = Event::with('category')
            ->whereDate('event_date', '>=', now())
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
        $rows = PcrStatusReport::orderByDesc('fund_source')->get();
        $dateLabel = now()->format('F j, Y');
        $filename = 'PCR STATUS AS OF ' . now()->format('Fj Y') . '.xlsx';

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
