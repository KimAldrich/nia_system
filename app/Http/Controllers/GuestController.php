<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IaResolution;
use App\Models\Downloadable;
use App\Models\EventCategory;
use App\Models\Event;

class GuestController extends Controller
{
    // 1. Process the 1-Click Guest Login
    public function authenticate(Request $request)
    {
        // Give them a secure guest badge in their session
        session(['is_guest' => true]);

        // Send them straight to the terms page
        return redirect()->route('guest.terms');
    }

    // 2. Show the Terms & Conditions Page
    public function terms()
    {
        // Security Check: Make sure they clicked the "Continue as Guest" button
        if (!session('is_guest')) {
            return redirect('/login');
        }

        return view('guest.terms');
    }

    // 3. Process the "I Agree" Button
    public function acceptTerms(Request $request)
    {
        // Log that they accepted the rules
        session(['guest_terms_accepted' => true]);

        return redirect()->route('guest.dashboard');
    }

    // 4. Show the Main Guest Dashboard (Read-Only)
    public function index()
    {
        if (!session('guest_terms_accepted')) {
            return redirect()->route('guest.terms');
        }

        // Fetch ALL files from ALL teams for the guest
        $downloadables = Downloadable::latest()->get();
        $resolutions = IaResolution::latest()->get();

        // Fetch Calendar Events
        $events = \App\Models\Event::whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();
        $categories = EventCategory::all();

        return view('guest.dashboard', compact('downloadables', 'resolutions', 'events', 'categories'));
    }

    // 5. Secure Logout
    public function logout(Request $request)
    {
        // Destroy the guest session variables so they lose access
        $request->session()->forget(['is_guest', 'guest_terms_accepted']);

        return redirect('/login')->with('success', 'You have securely logged out of the Guest Portal.');
    }

    public function teamDashboard($team_slug)
    {
        if (!session('guest_terms_accepted'))
            return redirect()->route('guest.terms');

        // THE FIX: Convert URL dash to Database underscore (e.g., 'fs-team' becomes 'fs_team')
        $db_team = str_replace('-', '_', $team_slug);

        $downloadables = Downloadable::where('team', $db_team)->latest()->get();
        $resolutions = IaResolution::where('team', $db_team)->latest()->get();

        $events = Event::whereDate('event_date', '>=', now())->orderBy('event_date', 'asc')->take(5)->get();
        $categories = EventCategory::all();
        $pageTitle = strtoupper(str_replace('_', ' ', $db_team)) . " Dashboard";

        return view('guest.dashboard', compact('downloadables', 'resolutions', 'events', 'categories', 'pageTitle'));
    }

    // 2. Show Team Downloadables (Read-Only)
    public function teamDownloadables($team_slug)
    {
        if (!session('guest_terms_accepted'))
            return redirect()->route('guest.terms');

        // THE FIX: Convert URL dash to Database underscore
        $db_team = str_replace('-', '_', $team_slug);

        $files = Downloadable::where('team', $db_team)->latest()->get();
        $pageTitle = strtoupper(str_replace('_', ' ', $db_team)) . " Downloadables";

        return view('guest.downloadables', compact('files', 'pageTitle'));
    }

    // 3. Show Team Resolutions (Read-Only)
    public function teamResolutions($team_slug)
    {
        if (!session('guest_terms_accepted'))
            return redirect()->route('guest.terms');

        // THE FIX: Convert URL dash to Database underscore
        $db_team = str_replace('-', '_', $team_slug);

        $resolutions = IaResolution::where('team', $db_team)->latest()->get();
        $pageTitle = strtoupper(str_replace('_', ' ', $db_team)) . " IA Resolutions";

        return view('guest.resolutions', compact('resolutions', 'pageTitle'));
    }
}