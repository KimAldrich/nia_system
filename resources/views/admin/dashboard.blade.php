@extends('layouts.app')
@section('title', 'Admin Master Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        * { box-sizing: border-box; }
        
        /* Soft UI Background & Typography */
        .content { background-color: #f4f7fe; font-family: 'Poppins', sans-serif; padding: 30px 40px; color: #334155; }
        .header-title { font-size: 24px; font-weight: 700; margin-bottom: 25px; color: #1e293b; letter-spacing: -0.5px; }
        
        /* 🌟 NEW: KPI Top Row Grid 🌟 */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .kpi-card { position: relative; background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column; border: none; }
        .kpi-title { font-size: 14px; color: #a0aec0; font-weight: 500; }
        .kpi-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 8px 0; }
        .kpi-icon { position: absolute; top: 24px; right: 24px; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .kpi-icon.blue { background: #e0e7ff; color: #4f46e5; }
        .kpi-icon.green { background: #dcfce7; color: #10b981; }
        .kpi-icon.orange { background: #ffedd5; color: #f59e0b; }
        .kpi-icon.purple { background: #f3e8ff; color: #9333ea; }
        .kpi-trend { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 5px; margin-top: auto;}
        .trend-up { color: #10b981; background: #dcfce7; padding: 2px 6px; border-radius: 4px;}
        .trend-down { color: #ef4444; background: #fee2e2; padding: 2px 6px; border-radius: 4px;}
        .trend-text { color: #a0aec0; font-weight: 500; }

        /* Main Dashboard Grid */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
        
        /* Soft UI Cards */
        .ui-card { background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); margin-bottom: 24px; border: none; }
        .section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Soft Modals */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 24px 16px; overflow-y: auto; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 400px; max-height: calc(100vh - 48px); overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin: auto; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Soft UI Tables */
        .sleek-table { width: 100%; border-collapse: collapse; }
        .sleek-table th { text-align: left; padding-bottom: 15px; color: #a0aec0; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; }
        .sleek-table td { padding: 15px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; font-weight: 500; color: #475569; vertical-align: middle; }
        .sleek-table tr:last-child td { border-bottom: none; padding-bottom: 0; }
        
        /* Soft Badges */
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; text-align: center; min-width: 90px; text-transform: uppercase; letter-spacing: 0.5px;}
        .badge-dark { background: #dcfce7; color: #16a34a; } 
        .badge-light { background: #e0e7ff; color: #4f46e5; } 
        .badge-outline { background: #ffedd5; color: #ea580c; border: none; }

        /* Form Elements for Soft UI */
        .modern-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Poppins', sans-serif; outline: none; background: #f8fafc; color: #1e293b; transition: 0.2s; }
        .modern-input:focus { border-color: #4f46e5; background: #ffffff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .modern-label { display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .modern-btn { width: 100%; padding: 10px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif;}
        .modern-btn:hover { background: #4338ca; }
        .modern-btn-outline { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .modern-btn-outline:hover { background: #f1f5f9; color: #1e293b; }

        /* Calendar Soft UI */
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
        .calendar-carousel { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; }
        .nav-btn { background: #f8fafc; border: none; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; color: #64748b; flex-shrink: 0; }
        .nav-btn:hover:not(:disabled) { background: #e2e8f0; color: #1e293b; }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .calendar-viewport { flex: 1; overflow: hidden; position: relative; min-height: 280px; }
        
        .month-block { display: none; animation: slideFade 0.3s ease; }
        .month-block.active { display: block; }
        @keyframes slideFade { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; row-gap: 15px; }
        .day-name { font-size: 11px; font-weight: 600; color: #a0aec0; text-transform: uppercase; margin-bottom: 5px; }
        .day-num { font-size: 13px; font-weight: 500; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 50%; color: #475569; transition: 0.2s;}
        .day-num.empty { visibility: hidden; }
        .day-num.has-event { font-weight: 700; }
        .day-num.today { background: #4f46e5 !important; color: white !important; border: none !important; font-weight: 700; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }
        .day-num.clickable { cursor: pointer; transition: 0.2s; }
    .day-num.clickable:hover { background: #e0e7ff !important; color: #4f46e5 !important; border-color: #e0e7ff !important; font-weight: 700;}
    .day-num.past { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

        /* Mini Events */
        .mini-event { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-top: 1px solid #f1f5f9; }
        .mini-event-date { font-size: 16px; font-weight: 700; color: #4f46e5; min-width: 30px; text-align: center; background: #e0e7ff; padding: 5px; border-radius: 8px;}
        .mini-event-title { font-size: 13px; font-weight: 600; color: #1e293b; margin: 0; }
        .mini-event-time { font-size: 11px; color: #a0aec0; margin: 0; }
        .event-tag { font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; display: inline-block; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
        
        .chart-wrapper { position: relative; height: 220px; width: 100%; }
        .custom-pagination { display: flex; justify-content: flex-end; align-items: center; margin-top: 20px; gap: 8px; font-family: 'Poppins', sans-serif; }
        .custom-pagination svg { width: 16px; height: 16px; }
        .custom-pagination .page-item { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; border-radius: 8px; background: #ffffff; color: #64748b; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; transition: 0.2s; }
        .custom-pagination .page-item:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .custom-pagination .page-item.active { background: #4f46e5; color: #ffffff; border-color: #4f46e5; }
        .custom-pagination .page-item.disabled { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border-color: #f1f5f9; pointer-events: none; }
        .audit-preview-list { display: grid; gap: 14px; }
        .audit-preview-item { padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
        .audit-preview-item strong { display: block; font-size: 13px; color: #0f172a; margin-bottom: 4px; }
        .audit-preview-meta { font-size: 11px; color: #64748b; }
        .audit-preview-empty { font-size: 12px; color: #94a3b8; text-align: center; padding: 16px 0; }
        .section-link { font-size: 12px; font-weight: 700; color: #4f46e5; text-decoration: none; }
        .section-link:hover { text-decoration: underline; }

        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>

    <h1 class="header-title">Admin Master Dashboard</h1>

    @if(session('success'))
        <div style="background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif


    @php
        $totalDownloads = isset($downloadables) ? $downloadables->count() : 0;
        $eventTeamLabels = [
            'all' => 'All Teams (General Event)',
            'fs_team' => 'FS Team',
            'rpwsis_team' => 'Social and Environmental Team',
            'cm_team' => 'Contract Management Team',
            'row_team' => 'Right Of Way Team',
            'pcr_team' => 'Program Completion Report Team',
            'pao_team' => 'Programming Team',
        ];
        $adminEventEntries = collect($events ?? [])->map(function ($event) use ($eventTeamLabels) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'event_date' => optional($event->event_date)->format('Y-m-d'),
                'event_date_label' => optional($event->event_date)->format('F d, Y'),
                'event_time' => $event->event_time,
                'event_category_id' => $event->event_category_id,
                'category_name' => optional($event->category)->name,
                'category_color' => optional($event->category)->color,
                'team' => $event->team,
                'team_label' => $eventTeamLabels[$event->team] ?? strtoupper(str_replace('_', ' ', (string) $event->team)),
                'reminder_minutes' => $event->reminder_minutes,
                'recurrence_pattern' => $event->recurrence_pattern ?? 'none',
                'recurrence_until' => optional($event->recurrence_until)->format('Y-m-d'),
                'recurrence_until_label' => optional($event->recurrence_until)->format('F d, Y'),
            ];
        })->values();
    @endphp

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-title">Total Validated</div>
            <div class="kpi-value">{{ $validatedResolutions }}</div>
            <div class="kpi-icon green">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-up">+ Active</span>
                <span class="trend-text">Resolutions</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Pending Action</div>
            <div class="kpi-value">{{ $pendingResolutions }}</div>
            <div class="kpi-icon orange">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-down">Attention</span>
                <span class="trend-text">Needed</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Total Forms</div>
            <div class="kpi-value">{{ $totalDownloads }}</div>
            <div class="kpi-icon blue">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-text">Available to download</span>
            </div>
        </div>

        <div class="kpi-card" id="upcomingEventsKpi">
            <div class="kpi-title">Upcoming Events</div>
            <div class="kpi-value">{{ isset($events) ? $events->count() : '0' }}</div>
            <div class="kpi-icon purple">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-text">Scheduled this month</span>
            </div>
        </div>
    </div>
    <div class="dashboard-grid">
        <div class="main-column">
            <div class="ui-card">
                <div class="section-title">Agency Resolutions Overview</div>
                @include('partials.active-projects-table', [
                    'resolutions' => $resolutions ?? collect(),
                    'containerId' => 'activeProjectsContainer',
                    'editable' => false,
                    'paginationStyle' => 'right',
                ])
            </div>

            <div class="ui-card">
                <div class="section-title">Upload Downloadable File to Team</div>
                <form action="{{ route('admin.downloadables.upload') }}" method="POST" enctype="multipart/form-data" data-async="true" data-async-reset="true">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label class="modern-label">Select Team</label>
                        <select name="team" required class="modern-input">
                            <option value="" disabled selected>-- Choose Team --</option>
                            <option value="fs_team">FS Team</option>
                            <option value="rpwsis_team">Social and Environmental Team</option>
                            <option value="cm_team">CM Team</option>
                            <option value="row_team">ROW Team</option>
                            <option value="pcr_team">PCR Team</option>
                            <option value="pao_team">PAO Team</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="modern-label">Upload File</label>
                        <input type="file" name="document" required class="modern-input" style="padding: 7px 12px;">
                    </div>
                    <button type="submit" class="modern-btn">Upload File</button>
                </form>
            </div>

            <div class="ui-card">
                <div class="section-title">Upload IA Resolution File to Team</div>
                <form action="{{ route('admin.resolutions.upload') }}" method="POST" enctype="multipart/form-data" data-async="true" data-async-reset="true">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label class="modern-label">Select Team</label>
                        <select name="team" required class="modern-input">
                            <option value="" disabled selected>-- Choose Team --</option>
                            <option value="fs_team">FS Team</option>
                            <option value="rpwsis_team">Social and Environmental Team</option>
                            <option value="cm_team">CM Team</option>
                            <option value="row_team">ROW Team</option>
                            <option value="pcr_team">PCR Team</option>
                            <option value="pao_team">PAO Team</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="modern-label">Upload File</label>
                        <input type="file" name="document" required class="modern-input" style="padding: 7px 12px;">
                    </div>
                    <button type="submit" class="modern-btn">Upload Resolution</button>
                </form>
            </div>

            <div class="ui-card" id="analyticsCard">
                <div class="section-title">Analytics</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #475569;">Upload Activity</p>
                        <div class="chart-wrapper"><canvas id="barChart"></canvas></div>
                    </div>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #475569;">Completion Rate</p>
                        <div class="chart-wrapper"><canvas id="doughnutChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="section-title">
                    <span>Recent Activity Log</span>
                    <a href="{{ route('admin.audit') }}" class="section-link">View full log</a>
                </div>
                <div class="audit-preview-list">
                    @forelse(($recentAuditLogs ?? collect()) as $log)
                        <div class="audit-preview-item">
                            <strong>{{ $log->description }}</strong>
                            <div class="audit-preview-meta">
                                {{ $log->user_name ?? 'Unknown user' }} · {{ $log->action }} · {{ optional($log->created_at)->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="audit-preview-empty">No audit activity has been recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="side-column">
            <div class="ui-card" id="eventManagerCard">
                <div class="section-title" style="margin-bottom: 15px;">Event Manager</div>

                <script type="application/json" id="adminEventEntriesData">@json($adminEventEntries)</script>

                <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <p style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; margin: 0;">Event Legend</p>
                        <button onclick="document.getElementById('categoryModal').classList.add('active')" style="background: none; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; padding: 4px 8px; cursor: pointer; color: #4f46e5; font-weight: 600; transition: 0.2s;">⚙️ Manage Tags</button>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        @forelse($categories as $cat)
                            <div class="legend-item"><div class="legend-dot" style="background: {{ $cat->color }};"></div>{{ $cat->name }}</div>
                        @empty
                            <p style="font-size: 11px; color: #a0aec0; margin: 0;">No custom tags created yet.</p>
                        @endforelse
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                    <label>
                        <span style="display:block; font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; margin-bottom: 6px;">Filter by Tag</span>
                        <select id="adminEventTagFilter" class="modern-input">
                            <option value="">All tags</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span style="display:block; font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; margin-bottom: 6px;">Filter by Team</span>
                        <select id="adminEventTeamFilter" class="modern-input">
                            <option value="">All teams</option>
                            @foreach($eventTeamLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                            $today = \Carbon\Carbon::now();
                        @endphp

                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $daysInMonth = $monthDate->daysInMonth;
                                $firstDayOfWeek = $monthDate->dayOfWeek;
                                
$eventsForMonth = isset($events) ? $events->filter(function($e) use ($currentYear, $m) {
                                    return $e->event_date->year == $currentYear && $e->event_date->month == $m && $e->event_date->gte(now());
                                })->groupBy(function($e) {
                                    return $e->event_date->format('j');
                                }) : collect();
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>
                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div><div class="day-name">Mon</div><div class="day-name">Tue</div><div class="day-name">Wed</div><div class="day-name">Thu</div><div class="day-name">Fri</div><div class="day-name">Sat</div>
                                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = ($day == $today->day && $m == $today->month && $currentYear == $today->year);
                                            $dateString = $monthDate->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                                            $ringColor = ($hasEvent && $dayEvents->first()->category) ? $dayEvents->first()->category->color : '#18181b';
                                        @endphp
<div class="day-num clickable {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }} {{ ($dateString < $today->format('Y-m-d')) ? 'past' : '' }}"
                                             data-date="{{ $dateString }}"
                                             style="{{ $hasEvent && !$isToday ? 'border: 2px solid ' . $ringColor . '; color: ' . $ringColor . ';' : '' }}{{ ($dateString < $today->format('Y-m-d')) ? '; opacity: 0.4; cursor: not-allowed;' : '' }}"
                                             onclick="handleAdminCalendarDayClick('{{ $dateString }}')" title="{{ ($dateString < $today->format('Y-m-d')) ? 'Past dates cannot be scheduled' : 'Click to view or schedule events' }}">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                <div style="margin-top: 10px;">
                    <p style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; margin-bottom: 10px;">Upcoming Schedule</p>
                    @if(isset($events) && $events->count() > 0)
                        @foreach($events as $event)
                            <div class="mini-event admin-event-item"
                                data-event-id="{{ $event->id }}"
                                data-category-id="{{ $event->event_category_id }}"
                                data-team="{{ $event->team }}"
                                onclick="openAdminEventDetailsById({{ $event->id }})"
                                style="justify-content: space-between; cursor: pointer;">
                                <div style="display: flex; gap: 15px; align-items: center;">
                                    <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                                    <div>
                                        <h4 class="mini-event-title">{{ $event->title }}</h4>
                                        <p class="mini-event-time">{{ $event->event_date->format('F') }} · {{ $event->event_time }}</p>
                                        @if(!empty($event->team))
                                            <p class="mini-event-time" style="margin-top: 4px;">{{ $eventTeamLabels[$event->team] ?? strtoupper(str_replace('_', ' ', $event->team)) }}</p>
                                        @endif
                                        @if($event->category)
                                            <span class="event-tag" style="background-color: {{ $event->category->color }}15; color: {{ $event->category->color }}; border: 1px solid {{ $event->category->color }}30;">
                                                {{ $event->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; align-items:center;" onclick="event.stopPropagation()">
                                    <button type="button" class="modern-btn modern-btn-outline" style="padding: 8px 10px; font-size: 12px;" onclick="openAdminEventEditor({{ $event->id }})">Edit</button>
                                    <form action="{{ route('admin.events.destroy', $event->id ?? 0) }}" method="POST" style="margin: 0;" data-async-target="#eventManagerCard, #upcomingEventsKpi" data-async-confirm="Delete this event?">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 18px; padding: 0 5px; transition: 0.2s;" title="Delete Event" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#f87171'">×</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: #a0aec0; text-align: center; margin-top: 20px;">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="eventModal">
        <div class="modal-box">
            <div id="eventModalContent">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b;" id="eventModalTitle">Schedule Event</h3>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">Adding event for: <strong id="displayDate" style="color: #4f46e5;"></strong></p>
            
            <form action="{{ route('admin.events.store') }}" method="POST" id="eventForm" data-async-target="#eventManagerCard, #eventModalContent, #upcomingEventsKpi" data-async-reset="true" data-async-close="#eventModal" data-async-reload="true">
                @csrf
                <input type="hidden" name="_method" id="eventFormMethod" value="POST">
                <input type="hidden" id="eventIdInput" value="">
                <div style="margin-bottom: 15px;">
                    <label class="modern-label">Event Date</label>
                    <input type="text" name="event_date" id="eventDateInput" required class="modern-input" style="cursor: pointer;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label class="modern-label">Event Title</label>
                    <input type="text" name="title" id="eventTitleInput" required placeholder="e.g. System Maintenance" class="modern-input">
                </div>
                <div style="margin-bottom: 15px;">
                    <label class="modern-label">Full Details</label>
                    <textarea name="description" id="eventDescriptionInput" placeholder="Event agenda, venue, contact person, or notes" class="modern-input" rows="4"></textarea>
                </div>
                <input type="hidden" name="event_time" id="finalTimeInput">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="modern-label">Start Time</label>
                        <input type="text" id="startTime" required class="modern-input" style="cursor: pointer;">
                    </div>
                    <div>
                        <label class="modern-label">End Time</label>
                        <input type="text" id="endTime" required class="modern-input" style="cursor: pointer;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="modern-label">Team</label>
                        <select name="team" id="eventTeamInput" required class="modern-input">
                            @foreach($eventTeamLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Reminder</label>
                        <select name="reminder_minutes" id="eventReminderInput" class="modern-input">
                            <option value="">No reminder</option>
                            <option value="15">15 minutes before</option>
                            <option value="30">30 minutes before</option>
                            <option value="60">1 hour before</option>
                            <option value="1440">1 day before</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="modern-label">Recurring Event</label>
                        <select name="recurrence_pattern" id="eventRecurrenceInput" class="modern-input">
                            <option value="none">Does not repeat</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Repeat Until</label>
                        <input type="text" name="recurrence_until" id="eventRecurrenceUntilInput" class="modern-input" placeholder="Optional">
                    </div>
                </div>
                <div style="margin-bottom: 25px;">
                    <label class="modern-label">Category Tag</label>
                    <input type="hidden" name="event_category_id" id="eventCategoryValueInput">
                    <select id="eventCategoryInput" required class="modern-input">
                        @forelse($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @empty
                            <option value="" disabled selected>Add a tag first!</option>
                        @endforelse
                    </select>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeEventModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;" id="eventFormSubmitButton">Save Event</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="eventDetailsModal">
        <div class="modal-box">
            <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom: 18px;">
                <div>
                    <h3 style="margin:0; font-size:18px; color:#1e293b;" id="eventDetailsTitle">Event Details</h3>
                    <p style="margin:6px 0 0; font-size:12px; color:#64748b;" id="eventDetailsMeta"></p>
                </div>
                <button type="button" onclick="closeEventDetailsModal()" style="background:none; border:none; font-size:28px; line-height:1; cursor:pointer;">&times;</button>
            </div>
            <div id="eventDetailsBody" style="display:grid; gap:12px; color:#334155; font-size:14px;"></div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" onclick="closeEventDetailsModal()" class="modern-btn modern-btn-outline" style="flex:1;">Close</button>
                <button type="button" onclick="editActiveEventFromDetails()" class="modern-btn" style="flex:1;">Edit Event</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="categoryModal">
        <div class="modal-box">
            <div id="categoryModalContent">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b;">Manage Event Tags</h3>
            <div style="margin-bottom: 20px; max-height: 150px; overflow-y: auto;">
                @if($categories->count() > 0)
                    @foreach($categories as $cat)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                            <div class="legend-item"><div class="legend-dot" style="background: {{ $cat->color }};"></div>{{ $cat->name }}</div>
                            <form action="{{ route('admin.categories.destroy', $cat->id ?? 0) }}" method="POST" style="margin: 0;" onsubmit="return handleCategoryTagSubmit(event, true)">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 12px; font-weight: 600;">Delete</button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <p style="font-size: 12px; color: #a0aec0; text-align: center;">No custom tags created yet.</p>
                @endif
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST" onsubmit="return handleCategoryTagSubmit(event)">
                @csrf
                <p style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 10px;">Add New Tag</p>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <select name="color" required class="modern-input" style="width: 130px; padding: 8px 10px;">
                        <option value="#4f46e5">🔵 Blue</option>
                        <option value="#f59e0b">🟡 Yellow</option>
                        <option value="#ef4444">🔴 Red</option>
                        <option value="#10b981">🟢 Green</option>
                        <option value="#9333ea">🟣 Violet</option>
                        <option value="#ea580c">🟠 Orange</option>
                    </select>
                    <input type="text" name="name" required placeholder="Tag Name" class="modern-input" style="flex: 1; padding: 8px 10px;">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="document.getElementById('categoryModal').classList.remove('active')" class="modern-btn modern-btn-outline" style="flex: 1;">Close</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Save Tag</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <script>
        let activeMonth = {{ \Carbon\Carbon::now()->month }};
        let adminEventEntries = [];
        let activeEventId = null;

        function syncAdminEventEntries() {
            const entriesNode = document.getElementById('adminEventEntriesData');

            if (!entriesNode) {
                adminEventEntries = [];
                return;
            }

            try {
                adminEventEntries = JSON.parse(entriesNode.textContent || '[]');
            } catch (error) {
                adminEventEntries = [];
            }
        }

        function initializeAdminDashboard() {
            syncAdminEventEntries();
            initializeAdminCharts();
            initializeAdminEventForm();
            initializeAdminEventFilters();
            updateCalendarView();
            applyAdminEventFilters();
        }

        function initializeAdminCharts() {
            const barCanvas = document.getElementById('barChart');
            const doughnutCanvas = document.getElementById('doughnutChart');

            if (!barCanvas || !doughnutCanvas) {
                return;
            }

            if (barCanvas.dataset.chartInitialized === 'true' && doughnutCanvas.dataset.chartInitialized === 'true') {
                return;
            }

            updateCalendarView();

            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#a0aec0';

            const existingBarChart = Chart.getChart(barCanvas);
            if (existingBarChart) {
                existingBarChart.destroy();
            }

            const existingDoughnutChart = Chart.getChart(doughnutCanvas);
            if (existingDoughnutChart) {
                existingDoughnutChart.destroy();
            }

            const ctxBar = barCanvas.getContext('2d');
            new Chart(ctxBar, { 
                type: 'bar', 
                data: { labels: ['W1', 'W2', 'W3', 'W4'], datasets: [{ data: [12, 19, 15, 22], backgroundColor: '#4f46e5', borderRadius: 6 }] }, 
                options: { plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } } 
            });

            const ctxPie = doughnutCanvas.getContext('2d');
            new Chart(ctxPie, { 
                type: 'doughnut', 
                data: { labels: ['Done', 'Pending'], datasets: [{ data: [70, 30], backgroundColor: ['#10b981', '#f1f5f9'], borderWidth: 0 }] }, 
                options: { cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: '#475569', usePointStyle: true, boxWidth: 10 } } } } 
            });

            barCanvas.dataset.chartInitialized = 'true';
            doughnutCanvas.dataset.chartInitialized = 'true';
        }

        function initializeAdminEventForm() {
            const eventDateInput = document.getElementById('eventDateInput');
            const recurrenceUntilInput = document.getElementById('eventRecurrenceUntilInput');
            const startTimeInput = document.getElementById('startTime');
            const endTimeInput = document.getElementById('endTime');
            const eventCategoryInput = document.getElementById('eventCategoryInput');
            const eventForm = document.getElementById('eventForm');

            if (eventDateInput && !eventDateInput._flatpickr) {
                flatpickr(eventDateInput, { dateFormat: "Y-m-d", minDate: "today" });
            }

            if (recurrenceUntilInput && !recurrenceUntilInput._flatpickr) {
                flatpickr(recurrenceUntilInput, { dateFormat: "Y-m-d", minDate: "today" });
            }

            if (startTimeInput && !startTimeInput._flatpickr) {
                flatpickr(startTimeInput, { enableTime: true, noCalendar: true, dateFormat: "h:i K", defaultDate: "09:00" });
            }

            if (endTimeInput && !endTimeInput._flatpickr) {
                flatpickr(endTimeInput, { enableTime: true, noCalendar: true, dateFormat: "h:i K", defaultDate: "10:30" });
            }

            if (eventCategoryInput && eventCategoryInput.dataset.bound !== 'true') {
                eventCategoryInput.addEventListener('change', syncAdminEventCategoryInput);
                eventCategoryInput.dataset.bound = 'true';
            }

            syncAdminEventCategoryInput();

            if (eventForm && eventForm.dataset.bound !== 'true') {
                eventForm.addEventListener('submit', function() {
                    const start = document.getElementById('startTime').value;
                    const end = document.getElementById('endTime').value;
                    const recurrencePattern = document.getElementById('eventRecurrenceInput').value || 'none';
                    syncAdminEventCategoryInput();
                    document.getElementById('finalTimeInput').value = start + ' - ' + end;
                    if (recurrencePattern === 'none') {
                        document.getElementById('eventRecurrenceUntilInput').value = '';
                    }
                });
                eventForm.dataset.bound = 'true';
            }
        }

        function syncAdminEventCategoryInput() {
            const categorySelect = document.getElementById('eventCategoryInput');
            const categoryValueInput = document.getElementById('eventCategoryValueInput');

            if (!categorySelect || !categoryValueInput) {
                return;
            }

            categoryValueInput.value = categorySelect.value || '';
        }

        function initializeAdminEventFilters() {
            const tagFilter = document.getElementById('adminEventTagFilter');
            const teamFilter = document.getElementById('adminEventTeamFilter');

            if (tagFilter && tagFilter.dataset.bound !== 'true') {
                tagFilter.addEventListener('change', applyAdminEventFilters);
                tagFilter.dataset.bound = 'true';
            }

            if (teamFilter && teamFilter.dataset.bound !== 'true') {
                teamFilter.addEventListener('change', applyAdminEventFilters);
                teamFilter.dataset.bound = 'true';
            }
        }

        function getFilteredAdminEvents() {
            const selectedTag = document.getElementById('adminEventTagFilter')?.value || '';
            const selectedTeam = document.getElementById('adminEventTeamFilter')?.value || '';

            return adminEventEntries.filter((event) => {
                const matchesTag = !selectedTag || String(event.event_category_id || '') === String(selectedTag);
                const matchesTeam = !selectedTeam || String(event.team || '') === String(selectedTeam);
                return matchesTag && matchesTeam;
            });
        }

        function applyAdminEventFilters() {
            const filteredEvents = getFilteredAdminEvents();
            const visibleIds = new Set(filteredEvents.map(event => String(event.id)));
            const eventsByDate = filteredEvents.reduce((carry, event) => {
                if (!carry[event.event_date]) {
                    carry[event.event_date] = [];
                }
                carry[event.event_date].push(event);
                return carry;
            }, {});

            document.querySelectorAll('.admin-event-item').forEach((item) => {
                item.style.display = visibleIds.has(String(item.dataset.eventId)) ? '' : 'none';
            });

            document.querySelectorAll('.day-num.clickable[data-date]').forEach((dayNode) => {
                const dayEvents = eventsByDate[dayNode.dataset.date] || [];
                const firstEvent = dayEvents[0] || null;
                const isToday = dayNode.classList.contains('today');
                const isPast = dayNode.classList.contains('past');

                dayNode.classList.toggle('has-event', dayEvents.length > 0);
                if (dayEvents.length > 0 && !isToday) {
                    dayNode.style.border = `2px solid ${firstEvent.category_color || '#18181b'}`;
                    dayNode.style.color = firstEvent.category_color || '#18181b';
                } else if (!isToday) {
                    dayNode.style.border = '';
                    dayNode.style.color = '';
                }

                if (isPast) {
                    dayNode.style.opacity = '0.4';
                    dayNode.style.cursor = 'not-allowed';
                } else {
                    dayNode.style.opacity = '';
                    dayNode.style.cursor = 'pointer';
                }
            });
        }

        function pruneDeletedCategoryEvents(payload = {}) {
            const deletedCategoryId = payload.deleted_category_id;
            const deletedEventIds = new Set((payload.deleted_event_ids || []).map((id) => String(id)));

            if (!deletedCategoryId && deletedEventIds.size === 0) {
                return;
            }

            adminEventEntries = adminEventEntries.filter((event) => {
                if (deletedEventIds.size > 0 && deletedEventIds.has(String(event.id))) {
                    return false;
                }

                if (deletedCategoryId && String(event.event_category_id || '') === String(deletedCategoryId)) {
                    return false;
                }

                return true;
            });

            document.querySelectorAll('.admin-event-item').forEach((item) => {
                const matchesDeletedEvent = deletedEventIds.has(String(item.dataset.eventId));
                const matchesDeletedCategory = deletedCategoryId && String(item.dataset.categoryId || '') === String(deletedCategoryId);

                if (matchesDeletedEvent || matchesDeletedCategory) {
                    item.remove();
                }
            });

            const visibleEventCount = document.querySelectorAll('.admin-event-item').length;
            const upcomingScheduleSection = document.querySelector('#eventManagerCard div[style*="margin-top: 10px;"]');

            if (upcomingScheduleSection && visibleEventCount === 0 && !upcomingScheduleSection.querySelector('.admin-event-item')) {
                const emptyState = document.createElement('p');
                emptyState.style.fontSize = '12px';
                emptyState.style.color = '#a0aec0';
                emptyState.style.textAlign = 'center';
                emptyState.style.marginTop = '20px';
                emptyState.textContent = 'No upcoming events.';
                upcomingScheduleSection.appendChild(emptyState);
            }

            applyAdminEventFilters();
        }

        function resetAdminEventForm() {
            const form = document.getElementById('eventForm');
            if (!form) return;

            form.action = "{{ route('admin.events.store') }}";
            document.getElementById('eventFormMethod').value = 'POST';
            document.getElementById('eventIdInput').value = '';
            document.getElementById('eventModalTitle').innerText = 'Schedule Event';
            document.getElementById('eventFormSubmitButton').innerText = 'Save Event';
            document.getElementById('displayDate').innerText = '';
            form.reset();
            syncAdminEventCategoryInput();
        }

        function openEventModal(dateStr) {
            resetAdminEventForm();
            const today = new Date().toISOString().split('T')[0];
            if (dateStr < today) {
                alert('Cannot schedule events in the past. Please select today or a future date.');
                return;
            }

            document.getElementById('eventDateInput').value = dateStr;
            document.getElementById('displayDate').innerText = dateStr;
            document.getElementById('eventModal').classList.add('active');
        }

        function openAdminEventEditor(id) {
            const event = adminEventEntries.find(entry => String(entry.id) === String(id));
            if (!event) return;

            resetAdminEventForm();
            document.getElementById('eventForm').action = `/admin/events/${event.id}`;
            document.getElementById('eventFormMethod').value = 'PATCH';
            document.getElementById('eventIdInput').value = event.id;
            document.getElementById('eventModalTitle').innerText = 'Edit Event';
            document.getElementById('eventFormSubmitButton').innerText = 'Update Event';
            document.getElementById('displayDate').innerText = event.event_date;
            document.getElementById('eventDateInput').value = event.event_date;
            document.getElementById('eventTitleInput').value = event.title || '';
            document.getElementById('eventDescriptionInput').value = event.description || '';
            document.getElementById('eventTeamInput').value = event.team || 'fs_team';
            document.getElementById('eventReminderInput').value = event.reminder_minutes ?? '';
            document.getElementById('eventRecurrenceInput').value = event.recurrence_pattern || 'none';
            document.getElementById('eventRecurrenceUntilInput').value = event.recurrence_until || '';
            document.getElementById('eventCategoryInput').value = event.event_category_id || '';
            syncAdminEventCategoryInput();

            const [startTime = '', endTime = ''] = String(event.event_time || '').split(' - ');
            document.getElementById('startTime').value = startTime;
            document.getElementById('endTime').value = endTime;

            activeEventId = event.id;
            closeEventDetailsModal();
            document.getElementById('eventModal').classList.add('active');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.remove('active');
            resetAdminEventForm();
        }

        function handleAdminCalendarDayClick(dateStr) {
            const today = new Date().toISOString().split('T')[0];
            if (dateStr < today) {
                alert('Cannot schedule events in the past. Please select today or a future date.');
                return;
            }

            const dayEvents = getFilteredAdminEvents().filter(event => event.event_date === dateStr);
            if (dayEvents.length > 0) {
                openAdminEventDetailsList(dateStr, dayEvents);
                return;
            }

            openEventModal(dateStr);
        }

        function openAdminEventDetailsById(id) {
            const event = adminEventEntries.find(entry => String(entry.id) === String(id));
            if (!event) return;

            activeEventId = event.id;
            document.getElementById('eventDetailsTitle').innerText = event.title || 'Event Details';
            document.getElementById('eventDetailsMeta').innerText = `${event.event_date_label} · ${event.event_time}`;
            document.getElementById('eventDetailsBody').innerHTML = `
                <div><strong>Team:</strong> ${event.team_label || 'N/A'}</div>
                <div><strong>Tag:</strong> ${event.category_name || 'Uncategorized'}</div>
                <div><strong>Reminder:</strong> ${event.reminder_minutes ? `${event.reminder_minutes} minute(s) before` : 'No reminder'}</div>
                <div><strong>Recurring:</strong> ${event.recurrence_pattern && event.recurrence_pattern !== 'none' ? `${event.recurrence_pattern} until ${event.recurrence_until_label || event.recurrence_until}` : 'No'}</div>
                <div><strong>Details:</strong><br>${(event.description || 'No additional details provided.').replace(/\n/g, '<br>')}</div>
            `;
            document.getElementById('eventDetailsModal').classList.add('active');
        }

        function openAdminEventDetailsList(dateStr, eventsForDate) {
            activeEventId = eventsForDate[0]?.id || null;
            document.getElementById('eventDetailsTitle').innerText = `Events on ${dateStr}`;
            document.getElementById('eventDetailsMeta').innerText = `${eventsForDate.length} event(s) scheduled`;
            document.getElementById('eventDetailsBody').innerHTML = eventsForDate.map((event) => `
                <button type="button" onclick="openAdminEventDetailsById(${event.id})" style="text-align:left; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; cursor:pointer;">
                    <strong>${event.title}</strong><br>
                    <span style="font-size:12px; color:#64748b;">${event.event_time} · ${event.team_label || 'N/A'} · ${event.category_name || 'Uncategorized'}</span>
                </button>
            `).join('');
            document.getElementById('eventDetailsModal').classList.add('active');
        }

        function closeEventDetailsModal() {
            document.getElementById('eventDetailsModal').classList.remove('active');
        }

        function editActiveEventFromDetails() {
            if (!activeEventId) return;
            openAdminEventEditor(activeEventId);
        }

        function closeCategoryModal() {
            const categoryModal = document.getElementById('categoryModal');
            if (categoryModal) {
                categoryModal.classList.remove('active');
            }
        }

        async function handleCategoryTagSubmit(event, isDelete = false) {
            event.preventDefault();

            const form = event.target.closest('form');
            if (!form) {
                return false;
            }

            if (!validateFormBeforeSubmit(form)) {
                return false;
            }

            if (isDelete) {
                const confirmed = await requestAppConfirmation('Delete this tag?', {
                    title: 'Delete tag',
                    confirmText: 'Delete'
                });

                if (!confirmed) {
                    return false;
                }
            }

            const submitter = event.submitter || form.querySelector('button[type="submit"]');

            try {
                if (submitter && typeof submitter.disabled !== 'undefined') {
                    submitter.disabled = true;
                }

                form.classList.add('is-loading');
                showAppLoader(isDelete ? 'Deleting tag...' : 'Saving tag...');

                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json') ? await response.json() : {};

                if (!response.ok) {
                    if (payload.errors && Object.keys(payload.errors).length > 0) {
                        throw new Error(Object.values(payload.errors).flat().join(' '));
                    }

                    throw new Error(payload.message || 'Unable to save tag.');
                }

                closeCategoryModal();
                window.location.reload();
                return false;
            } catch (error) {
                showLiveAlert(error.message || 'Unable to save tag.', 'error');
                return false;
            } finally {
                form.classList.remove('is-loading');
                hideAppLoader();

                if (submitter && typeof submitter.disabled !== 'undefined') {
                    submitter.disabled = false;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeAdminDashboard();
        });

        document.addEventListener('app:async-refreshed', function() {
            initializeAdminDashboard();
        });

        document.addEventListener('app:async-form-success', function(event) {
            const form = event.detail?.form;
            const payload = event.detail?.payload || {};
            const fallbackCategoryId = form?.dataset?.categoryId || '';
            const action = form?.getAttribute('action') || '';

            if (action.includes('/admin/event-categories')) {
                closeCategoryModal();
            }

            if (!payload.deleted_category_id && !(payload.deleted_event_ids || []).length && !fallbackCategoryId) {
                return;
            }

            pruneDeletedCategoryEvents({
                ...payload,
                deleted_category_id: payload.deleted_category_id || fallbackCategoryId,
            });
        });

        function changeMonth(direction) {
            activeMonth += direction;
            if (activeMonth < 1) activeMonth = 1;
            if (activeMonth > 12) activeMonth = 12;
            updateCalendarView();
        }

        function updateCalendarView() {
            document.querySelectorAll('.month-block').forEach(block => {
                block.classList.remove('active');
            });
            const currentBlock = document.getElementById('month-' + activeMonth);
            if(currentBlock) { currentBlock.classList.add('active'); }
            document.getElementById('prevMonthBtn').disabled = (activeMonth === 1);
            document.getElementById('nextMonthBtn').disabled = (activeMonth === 12);
        }

    </script>
@endsection
