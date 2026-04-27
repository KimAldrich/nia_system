<?php

namespace App\Http\Controllers;

use App\Models\Downloadable;
use App\Models\AuditLog;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IaResolution; // <-- Added this
use App\Models\Event;        // <-- Added this
use Illuminate\Support\Carbon;
use App\Models\EventCategory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    use HandlesAsyncRequests;

    // Security check
    private function checkAdmin()
    {
        if (strtolower(trim(auth()->user()->role)) !== 'admin') {
            abort(403, 'Unauthorized Access. Admins only.');
        }
    }

    // 1. Admin Master Dashboard (FIXED)
    public function index(Request $request)
    {
        $users = User::all();
        $this->checkAdmin();
        $validatedResolutions = IaResolution::where('status', 'validated')->count();
        $pendingResolutions = IaResolution::whereIn('status', ['on-going', 'not-validated'])->count();
        $resolutions = IaResolution::latest()->paginate(5, ['*'], 'active_projects_page')->withQueryString();

        $upcomingEventsQuery = Event::with('category')
            ->where('event_date', '>', now()->format('Y-m-d'))
            ->orWhere(function ($query) {
                $today = now()->format('Y-m-d');
                $currentTime = now()->format('H:i:s');
                $query->where('event_date', $today)
                    ->whereRaw("TIME(STR_TO_DATE(SUBSTRING_INDEX(TRIM(`event_time`), ' - ', -1), '%h:%i %p')) > '{$currentTime}'");
            })
            ->orderBy('event_date', 'asc');

        $events = (clone $upcomingEventsQuery)->get();
        $paginatedEvents = (clone $upcomingEventsQuery)
            ->paginate(5, ['*'], 'events_page')
            ->withQueryString();

        // Fetch custom tags for the legend
        $categories = EventCategory::all();
        $downloadables = Downloadable::all();
        $recentAuditLogs = AuditLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'resolutions',
            'events',
            'paginatedEvents',
            'categories',
            'downloadables',
            'validatedResolutions',
            'pendingResolutions',
            'recentAuditLogs'
        ));
    }

    public function auditTrail(Request $request)
    {
        $this->checkAdmin();

        $search = trim((string) $request->query('search', ''));
        $action = trim((string) $request->query('action', ''));
        $team = trim((string) $request->query('team', ''));
        $user = trim((string) $request->query('user', ''));
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $logs = $this->buildAuditTrailQuery($request)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $users = AuditLog::query()->whereNotNull('user_name')->select('user_name')->distinct()->orderBy('user_name')->pluck('user_name');
        $statuses = AuditLog::query()
            ->whereNotNull('metadata->status')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) as status")
            ->distinct()
            ->orderBy('status')
            ->pluck('status');
        $teams = AuditLog::query()
            ->whereNotNull('metadata->team')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.team')) as team")
            ->distinct()
            ->orderBy('team')
            ->pluck('team');

        return view('admin.audit-trail', compact(
            'logs',
            'search',
            'action',
            'actions',
            'team',
            'user',
            'status',
            'dateFrom',
            'dateTo',
            'users',
            'statuses',
            'teams'
        ));
    }

    public function exportAuditTrail(Request $request): StreamedResponse
    {
        $this->checkAdmin();

        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $logs = $this->buildAuditTrailQuery($request)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $reportTitle = 'Activity Log as of ' . now()->format('F j, Y');

        if ($dateFrom !== '' && $dateTo !== '') {
            $reportTitle .= ' From ' . Carbon::parse($dateFrom)->format('F j, Y') . ' to ' . Carbon::parse($dateTo)->format('F j, Y');
        } elseif ($dateFrom !== '') {
            $reportTitle .= ' From ' . Carbon::parse($dateFrom)->format('F j, Y');
        } elseif ($dateTo !== '') {
            $reportTitle .= ' Until ' . Carbon::parse($dateTo)->format('F j, Y');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Activity Log');
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', $reportTitle);

        $headers = [
            'A2' => 'Date',
            'B2' => 'Time',
            'C2' => 'User',
            'D2' => 'Role',
            'E2' => 'Action',
            'F2' => 'Subject Type',
            'G2' => 'Subject',
            'H2' => 'Status',
            'I2' => 'Description',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        foreach ([
            'A' => 14,
            'B' => 14,
            'C' => 24,
            'D' => 18,
            'E' => 24,
            'F' => 18,
            'G' => 28,
            'H' => 18,
            'I' => 54,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
        ]);

        $sheet->getStyle('A1:I' . max(3, $logs->count() + 2))->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $row = 3;
        foreach ($logs as $log) {
            $sheet->setCellValue("A{$row}", optional($log->created_at)->format('Y-m-d'));
            $sheet->setCellValue("B{$row}", optional($log->created_at)->format('h:i:s A'));
            $sheet->setCellValue("C{$row}", $log->user_name);
            $sheet->setCellValue("D{$row}", $log->user_role);
            $sheet->setCellValue("E{$row}", $log->action);
            $sheet->setCellValue("F{$row}", $log->subject_type);
            $sheet->setCellValue("G{$row}", $log->subject_label);
            $sheet->setCellValue("H{$row}", data_get($log->metadata, 'status'));
            $sheet->setCellValue("I{$row}", $log->description);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = $reportTitle . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildAuditTrailQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $action = trim((string) $request->query('action', ''));
        $team = trim((string) $request->query('team', ''));
        $user = trim((string) $request->query('user', ''));
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        return AuditLog::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('user_role', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('metadata->status', 'like', "%{$search}%")
                        ->orWhere('metadata->team', 'like', "%{$search}%");
                });
            })
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($team !== '', fn ($query) => $query->where('metadata->team', $team))
            ->when($user !== '', fn ($query) => $query->where('user_name', $user))
            ->when($status !== '', fn ($query) => $query->where('metadata->status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo));
    }

    //UploadDownloadables
    public function uploadDownloadable(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'team' => 'required|in:fs_team,rpwsis_team,cm_team,row_team,pcr_team,pao_team'
        ]);

        $file = $request->file('document');
        $path = $file->store('forms', 'public');

        $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

        Downloadable::create([
            'title' => $cleanTitle,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'team' => $request->team // 🔥 ADMIN CHOOSES TEAM
        ]);

        return $this->successResponse($request, 'File uploaded to selected team.');
    }

    //Upload IA Resolutions
    public function uploadResolution(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'team' => 'required|in:fs_team,rpwsis_team,cm_team,row_team,pcr_team,pao_team'
        ]);

        $file = $request->file('document');
        $path = $file->store('resolutions', 'public');

        $rawName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanTitle = ucwords(str_replace(['_', '-'], ' ', $rawName));

        \App\Models\IaResolution::create([
            'title' => $cleanTitle,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'team' => $request->team // 🔥 SAME LOGIC
        ]);

        return $this->successResponse($request, 'Resolution uploaded to selected team.');
    }

    // 2. Manage Users Page
    public function manageUsers(Request $request)
    {
        $this->checkAdmin();

        $allowedRoles = ['admin', 'fs_team', 'rpwsis_team', 'cm_team', 'row_team', 'pcr_team', 'pao_team'];
        $allowedSorts = ['name', 'email', 'created_at', 'role', 'is_active'];
        $allowedDirections = ['asc', 'desc'];

        $role = $request->query('role');
        $status = $request->query('status');
        $sort = $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        $query = User::query();

        if (in_array($role, $allowedRoles, true)) {
            $query->where('role', $role);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, $allowedDirections, true)) {
            $direction = 'desc';
        }

        $users = $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.users', compact('users', 'role', 'status', 'sort', 'direction'));
    }

    // 3. Store New User
    public function storeUser(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,fs_team,rpwsis_team,cm_team,row_team,pcr_team,pao_team',
        ]);

        $isAdmin = $validated['role'] === 'admin';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => true,
            'email_verified_at' => $isAdmin ? Carbon::now() : null,
            'agreed_to_terms' => $isAdmin,
        ]);

        if ($isAdmin) {
            return $this->successResponse($request, 'Admin account created successfully.');
        }

        return $this->successResponse($request, 'User account created successfully.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        if (auth()->id() === $user->id && !(bool) $validated['is_active']) {
            $message = 'You cannot deactivate your own account while logged in.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return $this->errorResponse($request, $message);
        }

        $user->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        $message = "{$user->name}'s account was {$status} successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $user->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    public function updateUserPassword(Request $request, User $user)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'password' => 'required|string|min:8|max:255',
        ]);

        $user->password = $validated['password'];
        $user->save();

        return $this->successResponse($request, "{$user->name}'s password was updated successfully.", [
            'plain_password' => $validated['password'],
            'user_id' => $user->id,
        ]);
    }

    public function destroyUser(Request $request, User $user)
    {
        $this->checkAdmin();

        if (auth()->id() === $user->id) {
            return $this->errorResponse($request, 'You cannot delete your own account while logged in.');
        }

        $userName = $user->name;
        $user->delete();

        return $this->successResponse($request, "{$userName}'s account was deleted successfully.");
    }

    public function storeEvent(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'required|string|max:255',
            'event_category_id' => 'required' // Validate the dropdown!
        ]);

        Event::create([
            'title' => $request->title,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'event_category_id' => $request->event_category_id, // Save the ID!
        ]);

        return $this->successResponse($request, 'Event added to the calendar!');
    }

    // Delete an Event
    public function storeCategory(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:10',
        ]);

        $normalizedName = mb_strtolower(trim($validated['name']));

        $tagAlreadyExists = EventCategory::query()
            ->get()
            ->contains(fn($category) => mb_strtolower(trim($category->name)) === $normalizedName);

        if ($tagAlreadyExists) {
            return $this->errorResponse($request, 'Tag name already exists. Please use a different tag name.');
        }

        EventCategory::create([
            'name' => trim($validated['name']),
            'color' => $validated['color'],
        ]);

        return $this->successResponse($request, 'New tag added to legend!');
    }

    // 4. New! Delete a Custom Tag
    public function destroyCategory(Request $request, $id)
    {
        $this->checkAdmin();

        $category = EventCategory::withCount('events')->findOrFail($id);
        $deletedEvents = $category->events()->count();

        $category->events()->delete();
        $category->delete();

        $message = $deletedEvents > 0
            ? "Tag removed. {$deletedEvents} linked event(s) were also deleted."
            : 'Tag removed.';

        return $this->successResponse($request, $message);
    }

    public function destroyEvent(Request $request, $id)
    {
        $this->checkAdmin();

        Event::findOrFail($id)->delete();

        return $this->successResponse($request, 'Event removed from schedule.');
    }
}






// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use App\Models\IaResolution;
// use App\Models\Event;

// class AdminController extends Controller
// {
//     public function index()
//     {
//         $this->checkAdmin();

//         // Fetch all resolutions across the whole agency
//         $resolutions = IaResolution::latest()->get();

//         // Fetch upcoming events
//         $events = Event::whereDate('event_date', '>=', now())
//             ->orderBy('event_date', 'asc')
//             ->take(5)
//             ->get();

//         return view('admin.dashboard', compact('resolutions', 'events'));
//     }
//     // Security check to ensure only Admins can run these methods
//     private function checkAdmin()
//     {
//         if (strtolower(trim(auth()->user()->role)) !== 'admin') {
//             abort(403, 'Unauthorized Access. Admins only.');
//         }
//     }

//     // 1. View the User Management Dashboard
//     public function manageUsers()
//     {
//         $this->checkAdmin();
//         $users = User::latest()->get();
//         return view('admin.users', compact('users'));
//     }

//     // 2. Create a New User
//     public function storeUser(Request $request)
//     {
//         $this->checkAdmin();

//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email',
//             'password' => 'required|string|min:8',
//             // Lock down the roles perfectly to your database values:
//             'role' => 'required|in:admin,fs_team,rpwsis_team,cm_team,row_team,pcr_team,pao_team'
//         ]);

//         User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => Hash::make($request->password),
//             'role' => $request->role,
//         ]);

//         return back()->with('success', 'User account created successfully.');
//     }
// }
