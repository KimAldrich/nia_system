<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downloadable;

class GuestController extends Controller
{
    // 1. Process the 1-Click Login
    public function authenticate(Request $request)
    {
        // Give them a secure guest badge in their session
        session(['is_guest' => true]);

        // Send them straight to the terms page
        return redirect()->route('guest.terms');
    }

    // 2. Show the Terms Page
    public function terms()
    {
        // Security Check: Make sure they actually clicked the login button
        if (!session('is_guest')) {
            return redirect('/login');
        }

        return view('guest.terms');
    }

    // 3. Process the "I Agree" button
    public function acceptTerms(Request $request)
    {
        // Log that they accepted the terms
        session(['guest_terms_accepted' => true]);

        return redirect()->route('guest.dashboard');
    }

    // 4. Show the Read-Only Dashboard
    public function index()
    {
        // Security Check: If they haven't accepted the terms, kick them back to the terms page!
        if (!session('guest_terms_accepted')) {
            return redirect()->route('guest.terms');
        }

        // Pull ALL downloadables for them to view
        $downloadables = Downloadable::latest()->get();

        return view('guest.dashboard', compact('downloadables'));
    }

    // 5. Secure Logout
    public function logout(Request $request)
    {
        // Destroy the guest session variables
        $request->session()->forget(['is_guest', 'guest_terms_accepted']);
        return redirect('/login');
    }
}