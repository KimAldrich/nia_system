<?php

namespace App\Http\Controllers;

use App\Models\Downloadable;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IaResolution; // <-- Added this
use App\Models\Event;        // <-- Added this
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\EventCategory;

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
    public function index()
    {
        $users = User::all();
        $this->checkAdmin();
        $resolutions = \App\Models\IaResolution::latest()->get();

        // Added 'with('category')' so the colored badges load efficiently
        $events = Event::with('category')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->get()
            ->filter(fn ($event) => $event->isUpcoming())
            ->values();

        // Fetch custom tags for the legend
        $categories = EventCategory::all();
        $downloadables = Downloadable::all();

        return view('admin.dashboard', compact('resolutions', 'events', 'categories', 'downloadables'));
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
    public function manageUsers()
    {
        $this->checkAdmin();
        $users = User::latest()->get();
        return view('admin.users', compact('users'));
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'email_verified_at' => $validated['role'] === 'admin' ? Carbon::now() : null,
        ]);

        if ($user->requiresEmailVerification()) {
            $user->sendEmailVerificationNotification();

            return $this->successResponse($request, 'User account created successfully. A verification email has been sent.');
        }

        return $this->successResponse($request, 'Admin account created successfully.');
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
            ->contains(fn ($category) => mb_strtolower(trim($category->name)) === $normalizedName);

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
        EventCategory::findOrFail($id)->delete();
        return $this->successResponse($request, 'Tag removed.');
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
