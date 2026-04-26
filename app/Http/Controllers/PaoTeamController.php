<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Models\EventCategory;
use App\Models\PaoPowData;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PaoTeamController extends Controller
{
    use HandlesAsyncRequests;

    private function validatePowData(Request $request, bool $requireId = false): array
    {
        $rules = [
            'district' => ['required', 'in:District 1,District 2,District 3,District 4,District 5,District 6'],
            'no_of_projects' => ['required', 'integer', 'min:0'],
            'total_allocation' => ['required', 'numeric', 'min:0'],
            'no_of_plans_received' => ['required', 'integer', 'min:0'],
            'no_of_project_estimate_received' => ['required', 'integer', 'min:0'],
            'pow_received' => ['required', 'integer', 'min:0'],
            'pow_approved' => ['required', 'integer', 'min:0'],
            'pow_submitted' => ['required', 'integer', 'min:0'],
            'ongoing_pow_preparation' => ['required', 'integer', 'min:0'],
            'pow_for_submission' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];

        if ($requireId) {
            $rules['id'] = ['required', 'integer', 'exists:pao_pow_data,id'];
        }

        return $request->validate($rules);
    }

    public function index()
    {
        $resolutions = IaResolution::where('team', 'pao_team')
            ->latest()
            ->paginate(8, ['*'], 'active_projects_page')
            ->withQueryString();
        $events = Event::with('category')
            ->where('event_date', '>', now()->format('Y-m-d'))
            ->orWhere(function ($query) {
                $today = now()->format('Y-m-d');
                $currentTime = now()->format('H:i:s');
                $query->where('event_date', $today)
                    ->whereRaw("TIME(STR_TO_DATE(SUBSTRING_INDEX(TRIM(`event_time`), ' - ', -1), '%h:%i %p')) > '{$currentTime}'");
            })
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        $categories = EventCategory::all();
        $powData = PaoPowData::paginate(8, ['*'], 'pow_page')->withQueryString();
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
        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']]);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ])->validate();

            $path = $file->store('forms', 'public');
            $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

            Downloadable::create([
                'title' => $cleanTitle,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'team' => 'pao_team'
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
        $singleFile = $request->file('document');
        $multipleFiles = $request->file('documents', []);
        $files = collect(is_array($multipleFiles) ? $multipleFiles : [])->filter()->values();

        if ($files->isEmpty() && $singleFile) {
            $files = collect([$singleFile]);
        }

        if ($files->isEmpty()) {
            $request->validate(['documents' => ['required', 'array', 'min:1']]);
        }

        foreach ($files as $file) {
            validator(['document' => $file], [
                'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            ])->validate();

            $path = $file->store('resolutions', 'public');
            $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

            IaResolution::create([
                'title' => $cleanTitle,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'team' => 'pao_team'
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
        // if ($resolution->team !== 'pao_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        return $this->successResponse($request, 'Resolution deleted successfully.');
    }

    public function storePow(Request $request)
    {
        PaoPowData::create($this->validatePowData($request));
        if ($this->respondsWithJson($request)) {
            return $this->successResponse($request, 'Added successfully.');
        }

        return back()->with('pow_status_success', 'Added successfully.');
    }

    public function updatePow(Request $request)
    {
        $validated = $this->validatePowData($request, true);
        $powData = PaoPowData::findOrFail($validated['id']);
        $powData->update(collect($validated)->except('id')->toArray());
        if ($this->respondsWithJson($request)) {
            return $this->successResponse($request, 'Updated successfully.');
        }

        return back()->with('pow_status_success', 'Updated successfully.');
    }

    public function deletePow(Request $request, $id)
    {
        $powData = PaoPowData::findOrFail($id);
        $powData->delete();
        return $this->successResponse($request, 'Program of Works data deleted successfully.');
    }

    public function exportPowExcel(Request $request): StreamedResponse
    {
        $isAuthorizedGuest = $request->session()->get('guest_terms_accepted') === true;
        $isAuthorizedUser = auth()->check();

        abort_unless($isAuthorizedGuest || $isAuthorizedUser, 403);

        $filename = 'STATUS OF POW CY ' . now()->format('Y') . ' AS OF ' . now()->format('F j, Y') . '.xlsx';
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $rows = PaoPowData::orderBy('district')->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('POW Status');

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.3)
            ->setRight(0.2)
            ->setLeft(0.2)
            ->setBottom(0.3);

        foreach ([
            'A' => 14,
            'B' => 14,
            'C' => 20,
            'D' => 18,
            'E' => 20,
            'F' => 14,
            'G' => 14,
            'H' => 14,
            'I' => 18,
            'J' => 18,
            'K' => 42,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getRowDimension(1)->setRowHeight(52);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(4)->setRowHeight(22);
        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->getRowDimension(6)->setRowHeight(28);
        $sheet->getRowDimension(7)->setRowHeight(24);
        $sheet->getRowDimension(8)->setRowHeight(22);
        $sheet->getRowDimension(10)->setRowHeight(26);
        $sheet->getRowDimension(11)->setRowHeight(34);

        foreach (range(1, 8) as $row) {
            $sheet->mergeCells("A{$row}:K{$row}");
        }

        $sheet->setCellValue('A1', 'Republic of the Philippines');
        $sheet->setCellValue('A2', 'OFFICE OF THE PRESIDENT');
        $sheet->setCellValue('A3', 'NATIONAL IRRIGATION ADMINISTRATION');
        $sheet->setCellValue('A4', 'Regional Office I (Ilocos Region)');
        $sheet->setCellValue('A5', 'Pangasinan Irrigation Management Office');
        $sheet->setCellValue('A6', 'STATUS OF PROGRAM OF WORKS');
        $sheet->setCellValue('A7', 'CY ' . now()->format('Y'));
        $sheet->setCellValue('A8', 'as of ' . now()->format('d F Y'));

        $sheet->getStyle('A1:K8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:K8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:K8')->getFont()->setName('Arial');
        $sheet->getStyle('A1')->getFont()->setSize(12);
        $sheet->getStyle('A2:A5')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A6')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A7')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A8')->getFont()->setSize(11)->setItalic(true);

        $sheet->getStyle('A10:K11')->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Arial',
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

        foreach (['A', 'B', 'C', 'D', 'E', 'I', 'J', 'K'] as $column) {
            $sheet->mergeCells($column . '10:' . $column . '11');
        }
        $sheet->mergeCells('F10:H10');

        $sheet->setCellValue('A10', 'DISTRICT');
        $sheet->setCellValue('B10', 'NO. OF PROJECTS');
        $sheet->setCellValue('C10', 'TOTAL ALLOCATION');
        $sheet->setCellValue('D10', 'NO. OF PLANS RECEIVED');
        $sheet->setCellValue('E10', 'NO. OF PROJECT ESTIMATE RECEIVED');
        $sheet->setCellValue('F10', 'STATUS OF PROGRAM OF WORK');
        $sheet->setCellValue('F11', 'NO. OF POW PREPARED');
        $sheet->setCellValue('G11', 'NO. OF POW APPROVED');
        $sheet->setCellValue('H11', 'NO. OF POW SUBMITTED');
        $sheet->setCellValue('I10', 'On Going POW Preparation');
        $sheet->setCellValue('J10', 'POW for Submission');
        $sheet->setCellValue('K10', 'Remarks');

        $currentRow = 12;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$currentRow}", $row->district);
            $sheet->setCellValue("B{$currentRow}", $row->no_of_projects);
            $sheet->setCellValue("C{$currentRow}", (float) $row->total_allocation);
            $sheet->setCellValue("D{$currentRow}", $row->no_of_plans_received);
            $sheet->setCellValue("E{$currentRow}", $row->no_of_project_estimate_received);
            $sheet->setCellValue("F{$currentRow}", $row->pow_received);
            $sheet->setCellValue("G{$currentRow}", $row->pow_approved);
            $sheet->setCellValue("H{$currentRow}", $row->pow_submitted);
            $sheet->setCellValue("I{$currentRow}", $row->ongoing_pow_preparation);
            $sheet->setCellValue("J{$currentRow}", $row->pow_for_submission);
            $sheet->setCellValue("K{$currentRow}", $row->remarks);
            $sheet->getRowDimension($currentRow)->setRowHeight(30);
            $currentRow++;
        }

        if ($rows->isNotEmpty()) {
            $sheet->setCellValue("A{$currentRow}", 'TOTAL');
            $sheet->setCellValue("B{$currentRow}", $rows->sum('no_of_projects'));
            $sheet->setCellValue("C{$currentRow}", (float) $rows->sum('total_allocation'));
            $sheet->setCellValue("D{$currentRow}", $rows->sum('no_of_plans_received'));
            $sheet->setCellValue("E{$currentRow}", $rows->sum('no_of_project_estimate_received'));
            $sheet->setCellValue("F{$currentRow}", $rows->sum('pow_received'));
            $sheet->setCellValue("G{$currentRow}", $rows->sum('pow_approved'));
            $sheet->setCellValue("H{$currentRow}", $rows->sum('pow_submitted'));
            $sheet->setCellValue("I{$currentRow}", $rows->sum('ongoing_pow_preparation'));
            $sheet->setCellValue("J{$currentRow}", $rows->sum('pow_for_submission'));
            $sheet->setCellValue("K{$currentRow}", '');
            $sheet->getStyle("A{$currentRow}:K{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}:K{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('EAF4E2');
        }

        $lastRow = max($currentRow, 12);
        $sheet->getStyle("A12:K{$lastRow}")->applyFromArray([
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
        $sheet->getStyle("K12:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C12:C{$lastRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');

        $noteRow = $lastRow + 2;
        $sheet->mergeCells("A{$noteRow}:K{$noteRow}");
        $sheet->setCellValue("A{$noteRow}", 'Note: All are status under Operations Monitored Projects');
        $sheet->getStyle("A{$noteRow}")->getFont()->setItalic(true)->setSize(10);

        $footerRow = $noteRow + 2;
        $sheet->mergeCells("A{$footerRow}:K{$footerRow}");
        $sheet->setCellValue("A{$footerRow}", 'Brgy. Bayaoas, Urdaneta City, Pangasinan, Ilocos Region, 2428 Philippines | Telephone Number: (075) 632 2775');
        $sheet->getStyle("A{$footerRow}")->getFont()->setSize(9);
        $sheet->getStyle("A{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $footerRow2 = $footerRow + 1;
        $sheet->mergeCells("A{$footerRow2}:K{$footerRow2}");
        $sheet->setCellValue("A{$footerRow2}", 'Email: r1.pangasinan-imo@nia.gov.ph | Website: www.nia.gov.ph | TIN: 000916415');
        $sheet->getStyle("A{$footerRow2}")->getFont()->setSize(9);
        $sheet->getStyle("A{$footerRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->addPowLogo($sheet, storage_path('app/public/excel_export_assets/page_1_image_6.png'), 'A1', 65, 45);
        $this->addPowLogo($sheet, storage_path('app/public/excel_export_assets/page_1_image_4.png'), 'B1', 55, 45);
        $this->addPowLogo($sheet, storage_path('app/public/excel_export_assets/page_1_image_5.png'), 'J1', 60, 45);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, $headers);
    }

    private function addPowLogo($sheet, string $path, string $coordinates, int $height, int $offsetX = 0): void
    {
        if (!file_exists($path)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($path);
        $drawing->setCoordinates($coordinates);
        $drawing->setHeight($height);
        $drawing->setOffsetX($offsetX);
        $drawing->setWorksheet($sheet);
    }
}
