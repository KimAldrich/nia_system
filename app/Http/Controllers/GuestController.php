<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsResolutionAnalytics;
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
use App\Models\PcrStatusReport;
use App\Models\RpwsisAccomplishment;
use App\Models\RpwsisAccomplishmentSummary;
use App\Models\RpwsisNurseryEstablishment;
use App\Models\RpwsisSignage;
use App\Models\RpwsisInfrastructure;

class GuestController extends Controller
{
    use BuildsResolutionAnalytics;

    private function teamDisplayLabel(string $dbTeam): string
    {
        return match ($dbTeam) {
            'fs_team' => 'FS',
            'pao_team' => 'Programming',
            'pcr_team' => 'Program Completion',
            'cm_team' => 'Contract Management',
            'row_team' => 'Right of Way',
            'rpwsis_team' => 'Social and Environmental',
            default => ucwords(str_replace('_', ' ', $dbTeam)),
        };
    }

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
    public function index(Request $request)
    {
        if (!session('guest_terms_accepted')) {
            return redirect()->route('guest.terms');
        }

        // Fetch ALL files from ALL teams for the guest
        $downloadables = Downloadable::latest()->get();
        $resolutions = IaResolution::latest()
            ->paginate(8, ['*'], 'active_projects_page')
            ->withQueryString();

        // Fetch all events so the dashboard can show upcoming and past entries.
        $events = \App\Models\Event::with('category')
            ->orderBy('event_date', 'asc')
            ->get();

        $upcomingEventsQuery = \App\Models\Event::with('category')
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
        $analytics = $this->buildResolutionAnalytics();

        return view('guest.dashboard', compact('downloadables', 'resolutions', 'events', 'paginatedEvents', 'categories', 'analytics'));
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
        $resolutions = IaResolution::where('team', $db_team)
            ->orderBy('created_at', 'desc')
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
        $analytics = $this->buildResolutionAnalytics($db_team);

        // 🌟 2. Set default empty variables
        $totalProjects = $conducted = $remaining = $feasible = 0;
        $hydroProjects = $fsdeProjects = $procurementProjects = $pcrStatusReports = null;
        $records = $summaryRecords = $nurseryRecords = $signageRecords = $infrastructureRecords = null;
        $rpwsisStatusRegions = $rpwsisStatusBatches = [];
        $procCategories = collect();
        $procMunicipalities = collect();
        $powData = null;
        $hydroDistricts = $hydroStatuses = $fsdeYears = $fsdeMunicipalities = collect();
        $powDistricts = $pcrFundSources = collect();

        // 🌟 3. Fetch FS TEAM specific data
        if ($db_team === 'fs_team') {
            $totalProjects = HydroGeoProject::count();
            $conducted = HydroGeoProject::whereIn('status', ['For Interpretation', 'Interpreted', 'For Submission of Raw data'])->count();
            $remaining = HydroGeoProject::where('status', 'For Schedule')->count();
            $feasible = HydroGeoProject::where('result', 'LIKE', '%Feasible%')->count();

            $hydroQuery = HydroGeoProject::query();
            if ($request->filled('hydro_search')) {
                $search = trim((string) $request->input('hydro_search'));
                $hydroQuery->where(function ($query) use ($search) {
                    $query->where('year', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhere('system_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('result', 'like', "%{$search}%");
                });
            }
            if ($request->filled('hydro_status')) {
                $hydroQuery->where('status', $request->input('hydro_status'));
            }
            if ($request->filled('hydro_district')) {
                $hydroQuery->where('district', $request->input('hydro_district'));
            }

            $fsdeQuery = FsdeProject::query();
            if ($request->filled('fsde_search')) {
                $search = trim((string) $request->input('fsde_search'));
                $fsdeQuery->where(function ($query) use ($search) {
                    $query->where('year', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('type_of_study', 'like', "%{$search}%")
                        ->orWhere('consultant', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('fsde_year')) {
                $fsdeQuery->where('year', $request->input('fsde_year'));
            }
            if ($request->filled('fsde_municipality')) {
                $fsdeQuery->where('municipality', $request->input('fsde_municipality'));
            }

            $hydroProjects = $hydroQuery->orderByDesc('year')->paginate(8, ['*'], 'hydro_page')->withQueryString();
            $fsdeProjects = $fsdeQuery->orderByDesc('year')->paginate(8, ['*'], 'fsde_page')->withQueryString();
            $hydroDistricts = HydroGeoProject::select('district')->whereNotNull('district')->distinct()->orderBy('district')->pluck('district');
            $hydroStatuses = HydroGeoProject::select('status')->whereNotNull('status')->distinct()->orderBy('status')->pluck('status');
            $fsdeYears = FsdeProject::select('year')->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year');
            $fsdeMunicipalities = FsdeProject::select('municipality')->whereNotNull('municipality')->distinct()->orderBy('municipality')->pluck('municipality');
        }

        // 🌟 4. Fetch CM TEAM specific data
        if ($db_team === 'cm_team') {
            $procCategories = ProcurementProject::select('category')->distinct()->pluck('category');
            $procMunicipalities = ProcurementProject::select('municipality')->whereNotNull('municipality')->distinct()->orderBy('municipality')->pluck('municipality');

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
            $procurementProjects = $procQuery->paginate(10)->appends($request->query());
        }

        if ($db_team === 'pao_team') {
            $powQuery = PaoPowData::query();
            if ($request->filled('pow_search')) {
                $search = trim((string) $request->input('pow_search'));
                $powQuery->where(function ($query) use ($search) {
                    $query->where('district', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('total_allocation', 'like', "%{$search}%");
                });
            }
            if ($request->filled('pow_district')) {
                $powQuery->where('district', $request->input('pow_district'));
            }
            $powData = $powQuery->orderBy('district')->paginate(8, ['*'], 'pow_page')->withQueryString();
            $powDistricts = PaoPowData::select('district')->whereNotNull('district')->distinct()->orderBy('district')->pluck('district');
        }

        if ($db_team === 'pcr_team') {
            $pcrQuery = PcrStatusReport::query();
            if ($request->filled('pcr_search')) {
                $search = trim((string) $request->input('pcr_search'));
                $pcrQuery->where(function ($query) use ($search) {
                    $query->where('fund_source', 'like', "%{$search}%")
                        ->orWhere('allocation', 'like', "%{$search}%");
                });
            }
            if ($request->filled('pcr_fund_source')) {
                $pcrQuery->where('fund_source', $request->input('pcr_fund_source'));
            }
            $pcrStatusReports = $pcrQuery->orderByDesc('fund_source')->paginate(8, ['*'], 'pcr_page')->withQueryString();
            $pcrFundSources = PcrStatusReport::select('fund_source')->whereNotNull('fund_source')->distinct()->orderByDesc('fund_source')->pluck('fund_source');
        }

        if ($db_team === 'rpwsis_team') {
            $recordsQuery = RpwsisAccomplishment::query();
            if ($request->filled('rpwsis_status_search')) {
                $search = trim((string) $request->input('rpwsis_status_search'));
                $recordsQuery->where(function ($query) use ($search) {
                    $query->where('region', 'like', "%{$search}%")
                        ->orWhere('batch', 'like', "%{$search}%")
                        ->orWhere('allocation', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('activity', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('rpwsis_status_region')) {
                $recordsQuery->where('region', $request->input('rpwsis_status_region'));
            }
            if ($request->filled('rpwsis_status_batch')) {
                $recordsQuery->where('batch', $request->input('rpwsis_status_batch'));
            }

            $rpwsisStatusRegions = (clone $recordsQuery)
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->orderBy('region')
                ->pluck('region')
                ->all();

            $rpwsisStatusBatches = (clone $recordsQuery)
                ->whereNotNull('batch')
                ->where('batch', '!=', '')
                ->distinct()
                ->orderBy('batch')
                ->pluck('batch')
                ->all();

            $summaryQuery = RpwsisAccomplishmentSummary::query();
            if ($request->filled('rpwsis_summary_search')) {
                $search = trim((string) $request->input('rpwsis_summary_search'));
                $summaryQuery->where(function ($query) use ($search) {
                    $query->where('region', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('plantation_type', 'like', "%{$search}%")
                        ->orWhere('nis_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('rpwsis_summary_province')) {
                $summaryQuery->where('province', $request->input('rpwsis_summary_province'));
            }
            if ($request->filled('rpwsis_summary_municipality')) {
                $summaryQuery->where('municipality', $request->input('rpwsis_summary_municipality'));
            }

            $nurseryQuery = RpwsisNurseryEstablishment::query();
            if ($request->filled('rpwsis_nursery_search')) {
                $search = trim((string) $request->input('rpwsis_nursery_search'));
                $nurseryQuery->where(function ($query) use ($search) {
                    $query->where('region', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('nursery_type', 'like', "%{$search}%")
                        ->orWhere('nis_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('rpwsis_nursery_municipality')) {
                $nurseryQuery->where('municipality', $request->input('rpwsis_nursery_municipality'));
            }
            if ($request->filled('rpwsis_nursery_type')) {
                $nurseryQuery->where('nursery_type', $request->input('rpwsis_nursery_type'));
            }

            $signageQuery = RpwsisSignage::query();
            if ($request->filled('rpwsis_signage_search')) {
                $search = trim((string) $request->input('rpwsis_signage_search'));
                $signageQuery->where(function ($query) use ($search) {
                    $query->where('region', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('signage_type', 'like', "%{$search}%")
                        ->orWhere('nis_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('rpwsis_signage_municipality')) {
                $signageQuery->where('municipality', $request->input('rpwsis_signage_municipality'));
            }
            if ($request->filled('rpwsis_signage_type')) {
                $signageQuery->where('signage_type', $request->input('rpwsis_signage_type'));
            }

            $infrastructureQuery = RpwsisInfrastructure::query();
            if ($request->filled('rpwsis_infrastructure_search')) {
                $search = trim((string) $request->input('rpwsis_infrastructure_search'));
                $infrastructureQuery->where(function ($query) use ($search) {
                    $query->where('region', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('infrastructure_type', 'like', "%{$search}%")
                        ->orWhere('nis_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }
            if ($request->filled('rpwsis_infrastructure_municipality')) {
                $infrastructureQuery->where('municipality', $request->input('rpwsis_infrastructure_municipality'));
            }
            if ($request->filled('rpwsis_infrastructure_type')) {
                $infrastructureQuery->where('infrastructure_type', $request->input('rpwsis_infrastructure_type'));
            }

            $records = $recordsQuery->latest()->paginate(8, ['*'], 'rpwsis_status_page')->withQueryString();
            $summaryRecords = $summaryQuery->latest()->paginate(8, ['*'], 'rpwsis_summary_page')->withQueryString();
            $nurseryRecords = $nurseryQuery->latest()->paginate(8, ['*'], 'rpwsis_nursery_page')->withQueryString();
            $signageRecords = $signageQuery->latest()->paginate(8, ['*'], 'rpwsis_signage_page')->withQueryString();
            $infrastructureRecords = $infrastructureQuery->latest()->paginate(8, ['*'], 'rpwsis_infrastructure_page')->withQueryString();
        }

        return view('guest.dashboard', compact(
            'resolutions',
            'events',
            'paginatedEvents',
            'categories',
            'analytics',
            'pageTitle',
            'db_team',
            'totalProjects',
            'conducted',
            'remaining',
            'feasible',
            'hydroProjects',
            'fsdeProjects',
            'powData',
            'pcrStatusReports',
            'records',
            'rpwsisStatusRegions',
            'rpwsisStatusBatches',
            'summaryRecords',
            'nurseryRecords',
            'signageRecords',
            'infrastructureRecords',
            'procCategories',
            'procMunicipalities',
            'procurementProjects'
            ,
            'hydroDistricts',
            'hydroStatuses',
            'fsdeYears',
            'fsdeMunicipalities',
            'powDistricts',
            'pcrFundSources'
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
        $teamLabel = $this->teamDisplayLabel($db_team);
        $pageTitle = "{$teamLabel} Downloadable Forms";

        return view('guest.downloadables', compact('files', 'pageTitle', 'teamLabel'));
    }

    // 3. Show Team Resolutions (Read-Only)
    public function teamResolutions($team_slug)
    {
        if (!session('guest_terms_accepted'))
            return redirect()->route('guest.terms');

        // THE FIX: Convert URL dash to Database underscore
        $db_team = str_replace('-', '_', $team_slug);

        $resolutions = IaResolution::with('files')->where('team', $db_team)->latest()->get();
        $teamLabel = $this->teamDisplayLabel($db_team);
        $pageTitle = $db_team === 'fs_team'
            ? "{$teamLabel} IA Resolutions"
            : "{$teamLabel} Files";

        return view('guest.resolutions', compact('resolutions', 'pageTitle', 'teamLabel'));
    }
}
