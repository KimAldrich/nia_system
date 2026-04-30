<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\Event;
use App\Services\SystemNotificationService;
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
    use BuildsResolutionAnalytics;

    private function notifications(): SystemNotificationService
    {
        return app(SystemNotificationService::class);
    }

    public function index(Request $request)
    {
        $resolutions = IaResolution::where('team', 'cm_team')
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
        $analytics = $this->buildResolutionAnalytics('cm_team');
        $procCategories = ProcurementProject::select('category')->distinct()->pluck('category');
        $procMunicipalities = ProcurementProject::select('municipality')->whereNotNull('municipality')->distinct()->orderBy('municipality')->pluck('municipality');

        // Filter logic
        $procQuery = ProcurementProject::query();
        if ($request->filled('proc_search')) {
            $search = trim((string) $request->input('proc_search'));
            $procQuery->where(function ($query) use ($search) {
                $query->where('category', 'like', "%{$search}%")
                    ->orWhere('name_of_project', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('contract_no', 'like', "%{$search}%")
                    ->orWhere('name_of_contractor', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('project_description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $procQuery->where('category', $request->proc_category);
        }
        if ($request->filled('proc_municipality')) {
            $procQuery->where('municipality', $request->input('proc_municipality'));
        }

        // 🌟 THE FIX: Clone the query for the Excel Export BEFORE paginating! 🌟
        // This grabs 100% of the rows matching your filter.
        $procExportData = (clone $procQuery)->get();

        // Now we can safely paginate the original query for the HTML table
        $procurementProjects = $procQuery->paginate(10)->appends($request->query());

        return view('cm_team.dashboard', compact(
            'resolutions',
            'events',
            'paginatedEvents',
            'categories',
            'analytics',
            'procCategories',
            'procMunicipalities',
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
                'team' => 'cm_team'
            ]);
        }

        $message = $files->count() === 1
            ? 'File uploaded successfully.'
            : "{$files->count()} files uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('cm_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $fileMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} downloadables."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} downloadables.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'cm_team', 'Downloadables updated', $fileMessage, ['type' => 'downloadable', 'team' => 'cm_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateForm(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $downloadable = Downloadable::findOrFail($id);
        $file = $request->file('document');

        $previousName = $downloadable->original_name;
        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $path = $file->store('forms', 'public');
        $downloadable->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $teamLabel = $this->notifications()->teamLabel('cm_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'cm_team', 'Downloadable updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'cm_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'File updated successfully.');
    }

    public function deleteForm(Request $request, $id)
    {
        $downloadable = Downloadable::findOrFail($id);

        $deletedName = $downloadable->original_name;
        if (Storage::disk('public')->exists($downloadable->file_path)) {
            Storage::disk('public')->delete($downloadable->file_path);
        }

        $downloadable->delete();

        $teamLabel = $this->notifications()->teamLabel('cm_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'cm_team', 'Downloadable removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} downloadables.", ['type' => 'downloadable', 'team' => 'cm_team', 'team_label' => $teamLabel]);

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
                'team' => 'cm_team'
            ]);
        }

        $message = $files->count() === 1
            ? 'Resolution uploaded successfully.'
            : "{$files->count()} resolutions uploaded successfully.";

        $teamLabel = $this->notifications()->teamLabel('cm_team');
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $resolutionMessage = $files->count() === 1
            ? "{$actorLabel} uploaded {$files->first()->getClientOriginalName()} to {$teamLabel} IA resolutions."
            : "{$actorLabel} uploaded {$files->count()} files to {$teamLabel} IA resolutions.";
        $this->notifications()->notifyTeamAndAdmins($request->user(), 'cm_team', 'IA resolutions updated', $resolutionMessage, ['type' => 'ia_resolution', 'team' => 'cm_team', 'team_label' => $teamLabel]);

        return $this->successResponse($request, $message);
    }

    public function updateResolution(Request $request, $id)
    {
        $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120']);
        $resolution = IaResolution::findOrFail($id);
        $file = $request->file('document');

        $previousName = $resolution->original_name;
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        $path = $file->store('resolutions', 'public');
        $resolution->update(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);

        $resolutionTeam = $resolution->team ?: 'cm_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution updated', "{$actorLabel} replaced {$previousName} with {$file->getClientOriginalName()} in {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

        return $this->successResponse($request, 'Resolution updated successfully.');
    }

    public function updateResolutionStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $resolution = IaResolution::findOrFail($id);
        $resolutionTeam = $resolution->team ?: 'cm_team';
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
        $resolution = IaResolution::findOrFail($id);

        // Delete file from storage
        $deletedName = $resolution->original_name;
        if (Storage::disk('public')->exists($resolution->file_path)) {
            Storage::disk('public')->delete($resolution->file_path);
        }

        // Optional: role/team check (same as your comment)
        // if ($resolution->team !== 'cm_team') {
        //     abort(403);
        // }

        // Delete record from database
        $resolution->delete();

        $resolutionTeam = $resolution->team ?: 'cm_team';
        $teamLabel = $this->notifications()->teamLabel($resolutionTeam);
        $actorLabel = $this->notifications()->actorLabel($request->user());
        $this->notifications()->notifyTeamAndAdmins($request->user(), $resolutionTeam, 'IA resolution removed', "{$actorLabel} removed {$deletedName} from {$teamLabel} IA resolutions.", ['type' => 'ia_resolution', 'team' => $resolutionTeam, 'team_label' => $teamLabel]);

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

        return $this->successResponse($request, 'Added successfully.');
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

        return $this->successResponse($request, 'Updated successfully.');
    }
    public function destroyProcurement(Request $request, $id)
    {
        ProcurementProject::findOrFail($id)->delete();
        return $this->successResponse($request, 'Project deleted!');
    }

    public function exportProcurementExcel(Request $request): StreamedResponse
    {
        $query = ProcurementProject::query();

        if ($request->filled('proc_search')) {
            $search = trim((string) $request->input('proc_search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('category', 'like', "%{$search}%")
                    ->orWhere('name_of_project', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('contract_no', 'like', "%{$search}%")
                    ->orWhere('name_of_contractor', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('project_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $query->where('category', $request->proc_category);
        }

        if ($request->filled('proc_municipality')) {
            $query->where('municipality', $request->input('proc_municipality'));
        }

        $rows = $query->orderBy('category')->orderBy('proj_no')->get();

        $filenameParts = ['Procurement Status as of', now()->format('F j, Y')];
        if ($request->filled('proc_search')) {
            $filenameParts[] = 'Search';
            $filenameParts[] = trim((string) $request->input('proc_search'));
        }
        if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
            $filenameParts[] = 'Category';
            $filenameParts[] = $request->proc_category;
        }
        if ($request->filled('proc_municipality')) {
            $filenameParts[] = 'Municipality';
            $filenameParts[] = $request->input('proc_municipality');
        }
        $filename = collect($filenameParts)->filter()->implode(' ') . '.xlsx';
        $filename = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $filename);

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

        $sheet->getStyle('A7:O7')->applyFromArray([
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

        $currentRow = 8;
        $groupedRows = $rows->groupBy(fn($row) => $row->category ?: 'Uncategorized');

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

        $lastRow = max($currentRow - 1, 8);
        $sheet->getStyle("A8:O{$lastRow}")->applyFromArray([
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
            $sheet->getStyle("{$column}8:{$column}{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (['D', 'E', 'L'] as $column) {
            $sheet->getStyle("{$column}8:{$column}{$lastRow}")
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
