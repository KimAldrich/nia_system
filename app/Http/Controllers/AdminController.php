<?php

namespace App\Http\Controllers;

use App\Models\Downloadable;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IaResolution; // <-- Added this
use App\Models\Event;        // <-- Added this
use Illuminate\Support\Facades\Hash;
use App\Models\EventCategory;

class AdminController extends Controller
{
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
        $events = Event::with('category')->orderBy('event_date', 'asc')->get();

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

        return back()->with('success', 'File uploaded to selected team.');
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

        return back()->with('success', 'Resolution uploaded to selected team.');
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

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', 'User account created successfully.');
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

            return back()->with('error', $message);
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

    public function destroyUser(User $user)
    {
        $this->checkAdmin();

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "{$userName}'s account was deleted successfully.");
    }

    public function storeEvent(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required|string|max:255',
            'event_category_id' => 'required' // Validate the dropdown!
        ]);

        Event::create([
            'title' => $request->title,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'event_category_id' => $request->event_category_id, // Save the ID!
        ]);

        return back()->with('success', 'Event added to the calendar!');
    }

    // Delete an Event
    public function storeCategory(Request $request)
    {
        $this->checkAdmin();
        $request->validate(['name' => 'required|string|max:50', 'color' => 'required|string|max:10']);
        EventCategory::create(['name' => $request->request->get('name'), 'color' => $request->request->get('color')]);
        return back()->with('success', 'New tag added to legend!');
    }

    // 4. New! Delete a Custom Tag
    public function destroyCategory($id)
    {
        $this->checkAdmin();
        EventCategory::findOrFail($id)->delete();
        return back()->with('success', 'Tag removed.');
    }

    public function destroyEvent($id)
    {
        $this->checkAdmin();

        Event::findOrFail($id)->delete();

        return back()->with('success', 'Event removed from schedule.');
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