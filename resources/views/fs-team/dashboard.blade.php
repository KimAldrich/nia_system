@extends('layouts.app')
@section('title', 'FS Team Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: #0c4d05;
        }

        .header-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .main-column, .side-column {
            min-width: 0; 
            width: 100%;
            overflow: hidden;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
            border: none;
            width: 100%;
            max-width: 100%;
            overflow: hidden; 
        }

        .ui-card.dark {
            background: #0c4d05;
            color: #ffffff;
            border: none;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Dark Summary Card */
        .status-hero { display: flex; justify-content: space-between; align-items: center; }
        .status-hero h3 { margin: 0 0 5px 0; font-size: 18px; font-weight: 600; }
        .status-hero p { margin: 0; font-size: 13px; color: #a1a1aa; }
        .squiggle-line { width: 80px; height: auto; opacity: 0.8; }

        /* =========================================
           SCROLLBAR & TABLE STYLES
           ========================================= */
        .table-responsive { 
            width: 100%; 
            overflow-x: hidden; 
            -webkit-overflow-scrolling: touch;
            padding-bottom: 15px; 
            scrollbar-width: thin;
        }
        
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sleek-table { border-collapse: collapse; width: max-content; min-width: 100%; }
        .sleek-table th { text-align: left; padding: 12px 15px; color: #a0aec0; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; white-space: nowrap; }
        .sleek-table td { padding: 15px 15px; border-bottom: 1px solid #f1f5f9; font-size: 12px; font-weight: 500; color: #475569; vertical-align: middle; white-space: normal; word-break: break-word; }
        .sleek-table tr:hover td { background-color: #f8fafc; transition: 0.2s; }
        .sleek-table tr:last-child td { border-bottom: none; }
        
        .col-system { font-weight: 700; color: #1e293b; white-space: nowrap; }
        .col-desc { max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Soft Badges for Statuses */
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; display: inline-block; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; max-width: 120px; white-space: normal; word-break: break-word; text-align: center; }
        .badge-schedule { background: #ffedd5; color: #ea580c; }
        .badge-interpretation { background: #e0e7ff; color: #4f46e5; }
        .badge-submission { background: #f3e8ff; color: #9333ea; }
        .badge-relocation { background: #fee2e2; color: #ef4444; }
        .badge-feasible { background: #dcfce7; color: #16a34a; }
        .badge-na { background: #f1f5f9; color: #64748b; }
        .badge-dark { background: #0c4d05; color: #fff; }
        .badge-light { background: #fda611; color: #ffffff; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }

        .status-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #e4e4e7; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 600; background: #ffffff; color: #18181b; cursor: pointer; outline: none; transition: 0.2s; }
        .status-select:hover { border-color: #18181b; }

        /* =========================================
           CALENDAR STYLES (FIXED SQUISHING)
           ========================================= */
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header h4 { margin: 0; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .calendar-carousel { display: flex; align-items: center; gap: 10px; }
        .nav-btn { background: #fff; border: 1px solid #0c4d05; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; flex-shrink: 0; }
        .calendar-viewport { flex: 1; }
        .month-block { display: none; }
        .month-block.active { display: block; }
        
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; row-gap: 15px; margin-bottom: 25px; }
        .day-name { font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px; }
        
        .day-num { font-size: 13px; font-weight: 600; width: 30px; height: 30px; min-width: 30px; min-height: 30px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 50%; color: #18181b; }
        .day-num.empty { visibility: hidden; }
        .day-num.has-event { border: 2px solid #18181b; }
        .day-num.today { background: #4fc94d; color: white; border: none; }

        .mini-event { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-top: 1px solid #f4f4f5; }
        .mini-event-date { font-size: 16px; font-weight: 700; color: #18181b; min-width: 30px; text-align: center; }
        .mini-event-title { font-size: 13px; font-weight: 600; color: #18181b; margin: 0; }
        .mini-event-time { font-size: 11px; color: #a1a1aa; margin: 0; }

        .chart-wrapper { position: relative; height: 220px; width: 100%; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #71717a; text-transform: uppercase; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }

        /* =========================================
           KPI GRID STYLES
           ========================================= */
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
        .trend-neutral { color: #f59e0b; background: #ffedd5; padding: 2px 6px; border-radius: 4px;}
        .trend-text { color: #a0aec0; font-weight: 500; }

        /* Soft UI Pagination Styling */
        .custom-pagination { display: flex; justify-content: flex-end; align-items: center; margin-top: 20px; gap: 8px; font-family: 'Poppins', sans-serif;}
        .custom-pagination svg { width: 16px; height: 16px; }
        .custom-pagination .page-item { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; border-radius: 8px; background: #ffffff; color: #64748b; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; transition: 0.2s; }
        .custom-pagination .page-item:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .custom-pagination .page-item.active { background: #4f46e5; color: #ffffff; border-color: #4f46e5; }
        .custom-pagination .page-item.disabled { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border-color: #f1f5f9; }

        @media (max-width: 1300px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        /* =========================================
           🌟 NEW: ADD DATA MODAL STYLES 🌟
           ========================================= */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .modern-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; outline: none; background: #f8fafc; color: #1e293b; transition: 0.2s; margin-bottom: 15px; }
        .modern-input:focus { border-color: #0c4d05; background: #ffffff; }
        .modern-label { display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .modern-btn { width: 100%; padding: 10px; background: #0c4d05; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; }
        .modern-btn:hover { background: #083803; }
        .modern-btn-outline { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .modern-btn-outline:hover { background: #f1f5f9; color: #1e293b; }
    </style>

    <h1 class="header-title">FS Team Dashboard</h1>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-title">Total SPIP Projects</div>
            <div class="kpi-value">{{ $totalProjects ?? 0 }}</div>
            <div class="kpi-icon blue">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div class="kpi-trend"><span class="trend-text">Recorded in database</span></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Georesistivity Conducted</div>
            <div class="kpi-value">{{ $conducted ?? 0 }}</div>
            <div class="kpi-icon green">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend"><span class="trend-up">On Track</span><span class="trend-text">Successfully mapped</span></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Remaining Sites</div>
            <div class="kpi-value">{{ $remaining ?? 0 }}</div>
            <div class="kpi-icon orange">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend"><span class="trend-neutral">Pending</span><span class="trend-text">Awaiting schedule</span></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Feasible Projects</div>
            <div class="kpi-value">{{ $feasible ?? 0 }}</div>
            <div class="kpi-icon purple">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="kpi-trend"><span class="trend-text">Validated as feasible</span></div>
        </div>
    </div>

    <div class="dashboard-grid">

        <div class="main-column">

            <div class="ui-card dark">
                <div class="status-hero">
                    <div>
                        <h3>Project Status Overview</h3>
                        <p>Track your deliverables, resolutions, and milestones.</p>
                    </div>
                    <svg class="squiggle-line" viewBox="0 0 100 30" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 15 Q 15 5, 25 15 T 45 15 T 65 15 T 85 15 T 95 5" />
                    </svg>
                </div>
            </div>

            <div class="ui-card">
                <div class="section-title">Active Projects</div>
                <div class="table-responsive">
                    <table class="sleek-table">
                        <thead>
                            <tr>
                                <th>Document Name</th>
                                <th>Status</th>
                                @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                                    <th style="text-align: right;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resolutions as $res)
                                <tr>
                                    <td>
                                        <strong>{{ $res->title }}</strong><br>
                                        <span style="font-size: 11px; color: #a1a1aa;">{{ $res->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        @if ($res->status == 'validated')
                                            <span class="status-badge badge-dark">Validated</span>
                                        @elseif($res->status == 'on-going')
                                            <span class="status-badge badge-light">On-Going</span>
                                        @else
                                            <span class="status-badge badge-outline">Not-Validated</span>
                                        @endif
                                    </td>

                                    @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                                        <td style="text-align: right;">
                                            <form action="{{ route('fs.resolutions.update_status', $res->id) }}" method="POST">
                                                @csrf
                                                <select name="status" class="status-select" onchange="this.form.submit()">
                                                    <option value="not-validated" {{ $res->status == 'not-validated' ? 'selected' : '' }}>Not-Validated</option>
                                                    <option value="on-going" {{ $res->status == 'on-going' ? 'selected' : '' }}>On-Going</option>
                                                    <option value="validated" {{ $res->status == 'validated' ? 'selected' : '' }}>Validated</option>
                                                </select>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']) ? '3' : '2' }}" style="text-align:center; color:#a1a1aa; padding: 30px 0;">
                                        No projects uploaded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ui-card">
                <div class="section-title">
                    Analytics
                    <span style="font-size: 12px; color: #a1a1aa; font-weight: 500;">Project Status</span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color:#71717a;">Upload Activity</p>
                        <div class="chart-wrapper"><canvas id="barChart"></canvas></div>
                    </div>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Completion Rate</p>
                        <div class="chart-wrapper"><canvas id="doughnutChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-column">
            <div class="ui-card">
                <div class="section-title" style="margin-bottom: 15px;">New Events</div>

                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f4f4f5;">
                    <p style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">
                        Event Legend
                    </p>

                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($categories ?? [] as $cat)
                            <div class="legend-item">
                                <div class="legend-dot" style="background: {{ $cat->color }};"></div>
                                {{ $cat->name }}
                            </div>
                        @empty
                            <p style="font-size: 11px; color: #a1a1aa;">No tags available.</p>
                        @endforelse
                    </div>
                </div>

                @php
                    $today = \Carbon\Carbon::now();
                    $daysInMonth = $today->daysInMonth;
                    $firstDayOfWeek = $today->copy()->startOfMonth()->dayOfWeek;

                    $eventDays = isset($events)
                        ? $events->map(function ($e) { return $e->event_date->format('j'); })->toArray()
                        : [];
                @endphp

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">&lt;</button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                            $today = \Carbon\Carbon::now();
                        @endphp

                        @for ($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $daysInMonth = $monthDate->daysInMonth;
                                $firstDayOfWeek = $monthDate->dayOfWeek;

                                $eventsForMonth = collect($events ?? [])->filter(function ($e) use ($currentYear, $m) {
                                    return $e->event_date->year == $currentYear && $e->event_date->month == $m;
                                })->groupBy(function ($e) {
                                    return $e->event_date->format('j');
                                });
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>

                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div><div class="day-name">Mon</div><div class="day-name">Tue</div>
                                    <div class="day-name">Wed</div><div class="day-name">Thu</div><div class="day-name">Fri</div><div class="day-name">Sat</div>

                                    @for ($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor

                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = $day == $today->day && $m == $today->month;
                                            $ringColor = $hasEvent && $dayEvents->first()->category ? $dayEvents->first()->category->color : '#18181b';
                                        @endphp

                                        <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                            style="{{ $hasEvent && !$isToday ? 'border-color:' . $ringColor . '; color:' . $ringColor : '' }}">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">&gt;</button>
                </div>

                <div style="margin-top: 10px;">
                    <p style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">Upcoming Schedule</p>

                    @if (isset($events) && $events->count() > 0)
                        @foreach ($events as $event)
                            <div class="mini-event">
                                <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                                <div>
                                    <h4 class="mini-event-title">{{ $event->title }}</h4>
                                    <p class="mini-event-time">{{ $event->event_time }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: #a1a1aa; text-align: center; margin-top: 20px;">No upcoming events.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="ui-card">
        <div class="section-title">
            Hydro-Georesistivity Status Monitoring
            
            <div style="display: flex; gap: 10px;">
                <button onclick="openAddModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                    + Add Data
                </button>
                <button style="background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export CSV
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="sleek-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>District</th>
                        <th>Project Code</th>
                        <th>System Name</th>
                        <th>Description / Remarks</th>
                        <th>Municipality</th>
                        <th>Status</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hydroProjects ?? [] as $project)
                        <tr>
                            <td>{{ $project->year }}</td>
                            <td>{{ $project->district }}</td>
                            <td>{{ $project->project_code }}</td>
                            <td class="col-system">{{ $project->system_name }}</td>
                            <td class="col-desc">{{ $project->description }}</td>
                            <td>{{ $project->municipality }}</td>
                            <td>
                                @php
                                    $statusLower = strtolower($project->status ?? '');
                                    $badgeClass = 'badge-na';
                                    
                                    if (str_contains($statusLower, 'schedule')) $badgeClass = 'badge-schedule';
                                    elseif (str_contains($statusLower, 'interpret')) $badgeClass = 'badge-interpretation';
                                    elseif (str_contains($statusLower, 'submission')) $badgeClass = 'badge-submission';
                                    elseif (str_contains($statusLower, 'relocation') || str_contains($statusLower, 'c/o contractor')) $badgeClass = 'badge-relocation';
                                @endphp
                                <span class="status-badge {{ $badgeClass }}">{{ $project->status }}</span>
                            </td>
                            <td>
                                @if(str_contains(strtolower($project->result ?? ''), 'feasible'))
                                    <span class="status-badge badge-feasible">{{ $project->result }}</span>
                                @else
                                    {{ $project->result ?? '-' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 30px 0; color: #a0aec0;">No projects found in the database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($hydroProjects) && $hydroProjects->hasPages())
            <div class="custom-pagination">
                {{-- Previous Page Link --}}
                @if ($hydroProjects->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $hydroProjects->previousPageUrl() }}" class="page-item"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($hydroProjects->links()->elements as $element)
                    @if (is_string($element))
                        <span class="page-item disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $hydroProjects->currentPage())
                                <span class="page-item active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-item">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($hydroProjects->hasMorePages())
                    <a href="{{ $hydroProjects->nextPageUrl() }}" class="page-item"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    <div class="modal-overlay" id="addDataModal">
        <div class="modal-box">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add New Hydro-Geo Data</h3>
            
            <form action="{{ route('fs.hydro.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Year</label>
                        <input type="text" name="year" required placeholder="e.g. 2026" class="modern-input">
                    </div>
                    <div>
                        <label class="modern-label">District</label>
                        <input type="text" name="district" required placeholder="e.g. 1st District" class="modern-input">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Project Code</label>
                        <input type="text" name="project_code" required placeholder="e.g. EGPIP-Solar" class="modern-input">
                    </div>
                    <div>
                        <label class="modern-label">Municipality</label>
                        <input type="text" name="municipality" required placeholder="e.g. Dasol" class="modern-input">
                    </div>
                </div>

                <div>
                    <label class="modern-label">System Name</label>
                    <input type="text" name="system_name" required placeholder="e.g. Alilao SPIP" class="modern-input">
                </div>

                <div>
                    <label class="modern-label">Description / Remarks</label>
                    <textarea name="description" required rows="3" class="modern-input" style="resize: none;" placeholder="Project description..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Status</label>
                        <select name="status" required class="modern-input">
                            <option value="For Schedule">For Schedule</option>
                            <option value="For Interpretation">For Interpretation</option>
                            <option value="For Submission of Raw data">For Submission of Raw data</option>
                            <option value="Relocation">Relocation</option>
                            <option value="Interpreted">Interpreted</option>
                            <option value="Not Applicable">Not Applicable</option>
                            <option value="C/O Contractor">C/O Contractor</option>
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Result</label>
                        <input type="text" name="result" placeholder="e.g. Feasible, -, etc." class="modern-input">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#a1a1aa';

            const ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Uploads',
                        data: [5, 12, 8, 15],
                        backgroundColor: '#0c4d05',
                        borderRadius: 6,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f4f4f5' }, border: { display: false } },
                        x: { grid: { display: false }, border: { display: false } }
                    }
                }
            });

            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Validated', 'On-Going', 'Pending'],
                    datasets: [{
                        data: [45, 30, 25],
                        backgroundColor: ['#0c4d05', '#fda611', '#e1e1ef'],
                        borderColor: '#e4e4e7',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 20 } }
                    }
                }
            });
        });

        let activeMonth = new Date().getMonth() + 1;

        document.addEventListener('DOMContentLoaded', function() {
            updateCalendarView();
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

            const current = document.getElementById('month-' + activeMonth);
            if (current) current.classList.add('active');

            document.getElementById('prevMonthBtn').disabled = (activeMonth === 1);
            document.getElementById('nextMonthBtn').disabled = (activeMonth === 12);
        }

        // 🌟 NEW: Modal Toggle Functions 🌟
        function openAddModal() {
            document.getElementById('addDataModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addDataModal').classList.remove('active');
        }
    </script>
@endsection