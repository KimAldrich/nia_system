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
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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

    public function updateProcurement(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:procurement_projects,id'],
            'category' => ['required', 'string', 'max:255'],
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

        $project = ProcurementProject::findOrFail($validated['id']);
        $project->update($validated);

        return $this->successResponse($request, 'Procurement project updated successfully!');
    }
    public function destroyProcurement(Request $request, $id)
    {
        ProcurementProject::findOrFail($id)->delete();
        return $this->successResponse($request, 'Project deleted!');
    }

    public function exportProcurementExcel(Request $request): StreamedResponse
    {
        $query = ProcurementProject::query();

        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $query->where('category', $request->proc_category);
        }

        $rows = $query->orderBy('category')->orderBy('proj_no')->get();

        $filename = 'Procurement Status as of ' . now()->format('F j, Y');
        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $filename .= '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $request->proc_category);
        }
        $filename .= '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        foreach ([
            'A' => 12,
            'B' => 38,
            'C' => 20,
            'D' => 18,
            'E' => 22,
            'F' => 10,
            'G' => 12,
            'H' => 18,
            'I' => 10,
            'J' => 18,
            'K' => 22,
            'L' => 18,
            'M' => 28,
            'N' => 22,
            'O' => 55,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');
        $sheet->mergeCells('D4:E4');
        $sheet->mergeCells('A4:A6');
        $sheet->mergeCells('B4:B6');
        $sheet->mergeCells('C4:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:E6');
        foreach (range('F', 'O') as $column) {
            $sheet->mergeCells("{$column}4:{$column}6");
        }
        $sheet->mergeCells('A7:O7');
        $sheet->mergeCells('A8:O8');

        $sheet->setCellValue('A1', 'STATUS OF PROCUREMENT AND CONTRACT - PANGASINAN IRRIGATION MANAGEMENT OFFICE');
        $sheet->setCellValue('A2', 'CY ' . now()->format('Y') . ' PROJECTS');
        $sheet->setCellValue('A3', 'as of ' . now()->format('F j, Y'));
        $sheet->setCellValue('A4', 'No. of Proj.');
        $sheet->setCellValue('B4', 'Name of Project');
        $sheet->setCellValue('C4', 'Municipality');
        $sheet->setCellValue('D4', 'Allocation and ABC');
        $sheet->setCellValue('D5', 'FY ' . now()->format('Y') . ' (Allocation)');
        $sheet->setCellValue('E5', 'Approved Budget of the Contract');
        $sheet->setCellValue('F4', 'BID-OUT');
        $sheet->setCellValue('G4', 'For Bidding');
        $sheet->setCellValue('H4', 'Date of Bidding');
        $sheet->setCellValue('I4', 'AWARDED');
        $sheet->setCellValue('J4', 'Date of Award');
        $sheet->setCellValue('K4', 'Contract No.');
        $sheet->setCellValue('L4', 'Contract Amount');
        $sheet->setCellValue('M4', 'Name of Contractor');
        $sheet->setCellValue('N4', 'Remarks');
        $sheet->setCellValue('O4', 'Project Description');
        $sheet->setCellValue('A7', 'PANGASINAN IMO');
        $sheet->setCellValue('A8', 'General Appropriations Act CY ' . now()->format('Y'));

        $sheet->getStyle('A1:O3')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '305496'],
            ],
        ]);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);

        $sheet->getStyle('A4:O6')->applyFromArray([
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

        $sheet->getStyle('A7:O8')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(22);
        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->getRowDimension(6)->setRowHeight(22);
        $sheet->getRowDimension(7)->setRowHeight(22);
        $sheet->getRowDimension(8)->setRowHeight(22);

        $currentRow = 9;
        $groupedRows = $rows->groupBy(fn ($row) => $row->category ?: 'Uncategorized');

        foreach ($groupedRows as $category => $projects) {
            $sheet->mergeCells("A{$currentRow}:O{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", $category);
            $sheet->getStyle("A{$currentRow}:O{$currentRow}")->applyFromArray([
                'font' => [
                    'name' => 'Arial',
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2F0D9'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $currentRow++;

            foreach ($projects as $project) {
                $sheet->setCellValue("A{$currentRow}", $project->proj_no);
                $sheet->setCellValue("B{$currentRow}", $project->name_of_project);
                $sheet->setCellValue("C{$currentRow}", $project->municipality);
                $sheet->setCellValue("D{$currentRow}", $project->allocation === null ? '' : (float) $project->allocation);
                $sheet->setCellValue("E{$currentRow}", $project->abc === null ? '' : (float) $project->abc);
                $sheet->setCellValue("F{$currentRow}", $project->bid_out ?? '');
                $sheet->setCellValue("G{$currentRow}", $project->for_bidding ?? '');
                $sheet->setCellValue("H{$currentRow}", $project->date_of_bidding ? \Carbon\Carbon::parse($project->date_of_bidding)->format('F j, Y') : '');
                $sheet->setCellValue("I{$currentRow}", $project->awarded ?? '');
                $sheet->setCellValue("J{$currentRow}", $project->date_of_award ? \Carbon\Carbon::parse($project->date_of_award)->format('F j, Y') : '');
                $sheet->setCellValue("K{$currentRow}", $project->contract_no);
                $sheet->setCellValue("L{$currentRow}", $project->contract_amount === null ? '' : (float) $project->contract_amount);
                $sheet->setCellValue("M{$currentRow}", $project->name_of_contractor);
                $sheet->setCellValue("N{$currentRow}", $project->remarks);
                $sheet->setCellValue("O{$currentRow}", $project->project_description);
                $sheet->getRowDimension($currentRow)->setRowHeight(34);
                $currentRow++;
            }
        }

        $lastRow = max($currentRow - 1, 9);
        $sheet->getStyle("A9:O{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
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

        foreach (['B', 'M', 'N', 'O'] as $column) {
            $sheet->getStyle("{$column}9:{$column}{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (['D', 'E', 'L'] as $column) {
            $sheet->getStyle("{$column}9:{$column}{$lastRow}")
                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

}
