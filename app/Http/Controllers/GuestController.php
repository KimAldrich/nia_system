<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Downloadable;
use App\Models\IaResolution; // Or whatever Resolution model you are using here
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\HydroGeoProject;
use App\Models\FsdeProject;
use App\Models\ProcurementProject;
use App\Models\PaoPowData;

class GuestController extends Controller
{
    // 1. Process the 1-Click Guest Login
    public function authenticate(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate();
        $request->session()->put('is_guest', true);

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
        $request->session()->put('guest_terms_accepted', true);

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
        $events = \App\Models\Event::with('category')
            ->whereDate('event_date', '>=', now())
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
        $request->session()->forget(['is_guest', 'guest_terms_accepted', 'agreed_to_terms']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have securely logged out of the Guest Portal.');
    }

    public function teamDashboard(Request $request, $team_slug)
    {
        if (!session('guest_terms_accepted'))
            return redirect()->route('guest.terms');

        $db_team = str_replace('-', '_', $team_slug);
        $teamTitles = [
            'fs_team' => 'FS Team',
            'rpwsis_team' => 'Social And Environmental Team',
            'cm_team' => 'Contract Management Team',
            'row_team' => 'Right Of Way Team',
            'pcr_team' => 'Program Completion Report Team',
            'pao_team' => 'Programming Team',
        ];
        $pageTitle = ($teamTitles[$db_team] ?? strtoupper(str_replace('_', ' ', $db_team))) . ' Dashboard';

        // 🌟 1. Fetch the exact same data the Teams see!
        $resolutions = IaResolution::orderBy('created_at', 'desc')->get();
        $events = Event::with('category')->get();
        $categories = EventCategory::all();

        // 🌟 2. Set default empty variables
        $totalProjects = $conducted = $remaining = $feasible = 0;
        $hydroProjects = $fsdeProjects = $procurementProjects = null;
        $procCategories = collect();
        $powData = null;

        // 🌟 3. Fetch FS TEAM specific data
        if ($db_team === 'fs_team') {
            $totalProjects = HydroGeoProject::count();
            $conducted = HydroGeoProject::whereIn('status', ['For Interpretation', 'Interpreted', 'For Submission of Raw data'])->count();
            $remaining = HydroGeoProject::where('status', 'For Schedule')->count();
            $feasible = HydroGeoProject::where('result', 'LIKE', '%Feasible%')->count();

            $hydroProjects = HydroGeoProject::paginate(8, ['*'], 'hydro_page');
            $fsdeProjects = FsdeProject::paginate(8, ['*'], 'fsde_page');
        }

        // 🌟 4. Fetch CM TEAM specific data
        if ($db_team === 'cm_team') {
            $procCategories = ProcurementProject::select('category')->distinct()->pluck('category');

            $procQuery = ProcurementProject::query();
            if ($request->filled('proc_category') && $request->proc_category !== 'All Projects') {
                $procQuery->where('category', $request->proc_category);
            }
            $procurementProjects = $procQuery->paginate(10)->appends($request->query());
        }

        if ($db_team === 'pao_team') {
            $powData = PaoPowData::paginate(8);
        }

        return view('guest.dashboard', compact(
            'resolutions',
            'events',
            'categories',
            'pageTitle',
            'db_team',
            'totalProjects',
            'conducted',
            'remaining',
            'feasible',
            'hydroProjects',
            'fsdeProjects',
            'powData',
            'procCategories',
            'procurementProjects'
        ));
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
