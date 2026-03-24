<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IaResolution; // <-- Added this
use App\Models\Event;        // <-- Added this
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $this->checkAdmin(); // <-- Added security check

        // Fetch all resolutions across the whole agency so the Admin can see everything
        $resolutions = IaResolution::latest()->get();

        // Fetch upcoming events for the calendar
        $events = Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        // Pass resolutions and events to the view, NOT users!
        return view('admin.dashboard', compact('resolutions', 'events', 'users'));
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

        // 1. Manually run the validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Fails if email already exists
            'password' => 'required|string|min:8', // Fails if password is under 8 chars
            'role' => 'required|in:admin,fs_team,rpwsis_team,cm_team,row_team,pcr_team,pao_team'
        ]);

        // 2. IF IT FAILS, FREEZE THE SCREEN AND SHOW THE ERROR
        if ($validator->fails()) {
            dd('VALIDATION FAILED!', $validator->errors());
        }

        // 3. IF IT PASSES, CREATE THE USER
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return back()->with('success', 'User account created successfully.');
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