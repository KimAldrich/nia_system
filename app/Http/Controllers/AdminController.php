<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.dashboard', compact('users'));
    }

    public function createUser(Request $request)
    {
        // Basic user creation 
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password123'), // Default password
            'role' => $request->role,
            'team_id' => $request->team_id,
        ]);

        return back()->with('success', 'User added successfully.');
    }
}
