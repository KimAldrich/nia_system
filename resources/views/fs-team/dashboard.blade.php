@extends('layouts.app')
@section('title', 'FS Team Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { box-sizing: border-box; }
        
        .content { 
            background-color: #f7f8fa; 
            font-family: 'Poppins', sans-serif; 
            padding: 40px; 
            color: #0c4d05; 
            max-width: 100vw;
            overflow-x: hidden;
        }
        
        .header-title { font-size: 32px; font-weight: 700; margin-bottom: 30px; letter-spacing: -0.5px; }
        
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; align-items: start; }
        
        .main-column, .side-column { min-width: 0; width: 100%; max-width: 100%; }

        .ui-card { 
            background: #ffffff; 
            border-radius: 16px; 
            padding: 24px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); 
            margin-bottom: 24px; 
            border: none; 
            width: 100%; 
            min-width: 0;
            max-width: 100%;
            display: block;
            box-sizing: border-box;
            overflow: hidden; 
        }
        
        .ui-card.dark { background: #0c4d05; color: #ffffff; border: none; }
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }

        .status-hero { display: flex; justify-content: space-between; align-items: center; }
        .status-hero h3 { margin: 0 0 5px 0; font-size: 18px; font-weight: 600; }
        .status-hero p { margin: 0; font-size: 13px; color: #a1a1aa; }
        .squiggle-line { width: 80px; height: auto; opacity: 0.8; }

        /* SCROLLBAR & TABLE STYLES */
        .table-responsive { 
            width: 100%; max-width: 100%; display: block; overflow-x: auto; 
            -webkit-overflow-scrolling: touch; padding-bottom: 15px; scrollbar-width: thin; 
        }
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sleek-table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        .sleek-table th { text-align: left; padding: 12px 15px; color: #a0aec0; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; white-space: normal; vertical-align: middle; line-height: 1.4;}
        .sleek-table td { padding: 15px 15px; border-bottom: 1px solid #f1f5f9; font-size: 12px; font-weight: 500; color: #475569; vertical-align: middle; white-space: normal; word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;}
        .sleek-table tr:hover td { background-color: #f8fafc; transition: 0.2s; }
        .sleek-table tr:last-child td { border-bottom: none; }
        
        .col-system { font-weight: 700; color: #1e293b; }
        .col-desc { color: #64748b; line-height: 1.5; }

        .text-clamp { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer; transition: color 0.2s; text-overflow: ellipsis; }
        .text-clamp:hover { color: #0c4d05; }
        .text-clamp.expanded { display: block; -webkit-line-clamp: unset; }

        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; display: inline-block; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; max-width: 100%; white-space: normal; word-wrap: break-word; text-align: center; }
        .badge-schedule { background: #ffedd5; color: #ea580c; }
        .badge-interpretation { background: #e0e7ff; color: #4f46e5; }
        .badge-submission { background: #f3e8ff; color: #9333ea; }
        .badge-relocation { background: #fee2e2; color: #ef4444; }
        .badge-feasible { background: #dcfce7; color: #16a34a; }
        .badge-na { background: #f1f5f9; color: #64748b; }
        .badge-dark { background: #0c4d05; color: #fff; }
        .badge-light { background: #fda611; color: #ffffff; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }
        
        .acc-badge { background: #f8fafc; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 6px; font-weight: 700; color: #1e293b; font-size: 11px; display: inline-block; white-space: nowrap;}

        .btn-delete { background: #fee2e2; color: #ef4444; border: none; padding: 10px 18px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 105px; line-height: 1; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1); }
        .btn-delete:hover { background: #fecaca; color: #b91c1c; transform: translateY(-1px); }
        .btn-edit-icon { background: #e0e7ff; color: #4f46e5; border: none; min-width: 40px; height: 40px; padding: 0 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; line-height: 1; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.12); flex-shrink: 0; white-space: nowrap; }
        .btn-edit-icon:hover { background: #c7d2fe; color: #3730a3; transform: translateY(-1px); }
        .action-cell { text-align: center; white-space: nowrap !important; word-wrap: normal !important; overflow-wrap: normal !important; word-break: normal !important; }
        .action-buttons { display: flex; align-items: center; justify-content: center; flex-wrap: nowrap; gap: 5px; min-width: max-content; }
        .action-buttons form { display: inline-flex; margin: 0; }

        .status-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #e4e4e7; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 600; background: #ffffff; color: #18181b; cursor: pointer; outline: none; transition: 0.2s; width: 100%; max-width: 150px; }
        .status-select:hover { border-color: #18181b; }

        /* CALENDAR STYLES */
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

        /* KPI GRID STYLES */
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

        /* Pagination & Modals */
        .custom-pagination { display: flex; justify-content: flex-end; align-items: center; margin-top: 20px; gap: 8px; font-family: 'Poppins', sans-serif;}
        .custom-pagination svg { width: 16px; height: 16px; }
        .custom-pagination .page-item { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; border-radius: 8px; background: #ffffff; color: #64748b; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; transition: 0.2s; }
        .custom-pagination .page-item:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .custom-pagination .page-item.active { background: #4f46e5; color: #ffffff; border-color: #4f46e5; }
        .custom-pagination .page-item.disabled { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border-color: #f1f5f9; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .modern-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; outline: none; background: #ffffff; color: #1e293b; transition: 0.2s; margin-bottom: 15px; }
        .modern-input:focus { border-color: #0c4d05; box-shadow: 0 0 0 3px rgba(12, 77, 5, 0.1); }
        .modern-label { display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .modern-btn { width: 100%; padding: 10px; background: #0c4d05; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; }
        .modern-btn:hover { background: #083803; }
        .modern-btn-outline { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .modern-btn-outline:hover { background: #f1f5f9; color: #1e293b; }

        /* 🌟 NEW: Style for date picker icon consistency 🌟 */
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; }
        input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        @media (max-width: 1300px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <h1 class="header-title">FS Team Dashboard</h1>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-title">Total SPIP Projects</div>
            <div class="kpi-value">{{ $totalProjects ?? 0 }}</div>
            <div class="kpi-icon blue"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
            <div class="kpi-trend"><span class="trend-text">Recorded in database</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Georesistivity Conducted</div>
            <div class="kpi-value">{{ $conducted ?? 0 }}</div>
            <div class="kpi-icon green"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div class="kpi-trend"><span class="trend-up">On Track</span><span class="trend-text">Successfully mapped</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Remaining Sites</div>
            <div class="kpi-value">{{ $remaining ?? 0 }}</div>
            <div class="kpi-icon orange"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div class="kpi-trend"><span class="trend-neutral">Pending</span><span class="trend-text">Awaiting schedule</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Feasible Projects</div>
            <div class="kpi-value">{{ $feasible ?? 0 }}</div>
            <div class="kpi-icon purple"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
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
                <div class="table-responsive" id="activeProjectsContainer">
                    <table class="sleek-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Document Name</th>
                                <th style="width: 30%;">Status</th>
                                @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                                    <th style="text-align: right; width: 20%;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resolutions ?? [] as $res)
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
                                            <form action="{{ route('fs.resolutions.update_status', $res->id) }}" method="POST" data-async-target="#activeProjectsContainer">
                                                @csrf
                                                <select name="status" class="status-select" data-auto-submit>
                                                    <option value="not-validated" {{ $res->status == 'not-validated' ? 'selected' : '' }}>Not-Validated</option>
                                                    <option value="on-going" {{ $res->status == 'on-going' ? 'selected' : '' }}>On-Going</option>
                                                    <option value="validated" {{ $res->status == 'validated' ? 'selected' : '' }}>Validated</option>
                                                </select>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']) ? '3' : '2' }}" style="text-align:center; color:#a1a1aa; padding: 30px 0;">No projects uploaded yet.</td></tr>
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Upload Activity</p>
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
                    <p style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">Event Legend</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($categories ?? [] as $cat)
                            <div class="legend-item"><div class="legend-dot" style="background: {{ $cat->color }};"></div>{{ $cat->name }}</div>
                        @empty
                            <p style="font-size: 11px; color: #a1a1aa;">No tags available.</p>
                        @endforelse
                    </div>
                </div>

                @php
                    $today = \Carbon\Carbon::now();
                    $daysInMonth = $today->daysInMonth;
                    $firstDayOfWeek = $today->copy()->startOfMonth()->dayOfWeek;
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

    <div class="ui-card" id="hydroSection" style="margin-top: 24px;">
        <div class="section-title">
            Hydro-Georesistivity Status Monitoring
            <div style="display: flex; gap: 10px;">
                @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                    <button onclick="openAddModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Data
                    </button>
                @endif
                <a href="{{ route('fs.hydro.export') }}" onclick="handleHydroExport(event, this.href)" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Export Excel
                </a>
            </div>
        </div>
        
                <div class="table-responsive" id="hydroTableContainer">
            <table class="sleek-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th style="width: 5%;">Year</th>
                        <th style="width: 10%;">District</th>
                        <th style="width: 12%;">Project Code</th>
                        <th style="width: 15%;">System Name</th>
                        <th style="width: 20%;">Description / Remarks</th>
                        <th style="width: 13%;">Municipality</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Result</th>
                        @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                            <th style="text-align: center; width: 5%;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($hydroProjects ?? [] as $project)
                        <tr>
                            <td>{{ $project->year }}</td>
                            <td>{{ $project->district }}</td>
                            <td>{{ $project->project_code }}</td>
                            <td class="col-system" style="max-width: 150px;">{{ $project->system_name }}</td>
                            <td class="col-desc" style="max-width: 200px;">
                                <div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand/collapse">
                                    {{ $project->description }}
                                </div>
                            </td>
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
                                @if(str_contains(strtolower(trim($project->result ?? '')), 'feasible'))
                                    <span class="status-badge badge-feasible">{{ $project->result }}</span>
                                @else
                                    {{ $project->result ?? '-' }}
                                @endif
                            </td>
                            @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                                <td class="action-cell">
                                    <div class="action-buttons">
                                    <button type="button"
                                        class="btn-edit-icon"
                                        title="Edit Project"
                                        onclick="openHydroEditModal({{ $project->id }}, {{ $project->year }}, '{{ addslashes($project->district) }}', '{{ addslashes($project->project_code) }}', '{{ addslashes($project->system_name) }}', '{{ addslashes($project->description) }}', '{{ addslashes($project->municipality) }}', '{{ addslashes($project->status) }}', '{{ addslashes($project->result ?? '') }}')"
                                        >
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('fs.hydro.destroy', $project->id) }}" method="POST" data-async-target="#hydroSection" data-async-confirm="Are you sure you want to delete this Hydro-Geo project?" data-async-success="silent">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete Project">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin'])) ? '9' : '8' }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No projects found in the database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($hydroProjects) && $hydroProjects->hasPages())
            <div class="custom-pagination">
                @if ($hydroProjects->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $hydroProjects->withQueryString()->previousPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#hydroSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif
                @foreach ($hydroProjects->withQueryString()->links()->elements as $element)
                    @if (is_string($element)) <span class="page-item disabled">{{ $element }}</span> @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $hydroProjects->currentPage()) <span class="page-item active">{{ $page }}</span>
                            @else <a href="{{ $url }}" class="page-item" data-async-pagination="true" data-async-target="#hydroSection">{{ $page }}</a> @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($hydroProjects->hasMorePages())
                    <a href="{{ $hydroProjects->withQueryString()->nextPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#hydroSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    <div class="ui-card" id="fsdeSection">
        <div class="section-title">
            Monthly FSDE Status Report
            <div style="display: flex; gap: 10px;">
                @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                    <button onclick="openFsdeAddModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Data
                    </button>
                @endif
                <a href="{{ route('fs.fsde.export') }}" onclick="handleFsdeExport(event, this.href)" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Export Excel
                </a>
            </div>
        </div>
        
                <div class="table-responsive" id="fsdeTableContainer">
            <table class="sleek-table" style="min-width: 1700px;">
                <thead>
                    <tr>
                        <th style="width: 4%;">Year</th>
                        <th style="width: 14%;">Project Name</th>
                        <th style="width: 8%;">Municipality</th>
                        <th style="width: 10%;">Study Type</th>
                        <th style="width: 12%;">Consultant</th>
                        <th style="width: 10%;">Period of Engagement<br><span style="font-size:9px; color:#a0aec0; font-weight:500;">(Start - End)</span></th>
                        <th style="width: 7%;">Contract Amount</th>
                        <th style="width: 7%;">Actual Obligation</th>
                        <th style="width: 7%;">Value of Acc.</th>
                        <th style="width: 7%;">Actual Expend.</th>
                        
                        <th style="min-width: 140px; background: #e0e7ff; border-radius: 6px 6px 0 0; width: 10%;">
                            Accomplishment As Of
                            <select id="accMonthSelector" onchange="toggleAccMonth(this.value)" class="modern-input" style="padding: 4px; font-size: 10px; height: auto; margin-top: 5px; width: 100%; border-color: #c7d2fe; cursor: pointer;">
                                <option value="jan">January</option><option value="feb">February</option><option value="mar">March</option>
                                <option value="apr">April</option><option value="may">May</option><option value="jun">June</option>
                                <option value="jul">July</option><option value="aug">August</option><option value="sep">September</option>
                                <option value="oct">October</option><option value="nov">November</option><option value="dec">December</option>
                            </select>
                        </th>
                        
                        <th style="width: 12%;">Remarks</th>
                        @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                            <th style="text-align: center; width: 6%;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($fsdeProjects ?? [] as $project)
                        <tr>
                            <td>{{ $project->year }}</td>
                            <td class="col-system" style="max-width: 200px;">{{ $project->project_name }}</td>
                            <td>{{ $project->municipality }}</td>
                            <td>{{ $project->type_of_study }}</td>
                            <td class="col-desc" style="max-width: 180px;">{{ $project->consultant }}</td>
                            <td style="font-size:11px; line-height: 1.8;">
                                <strong style="color: #16a34a;">Start:</strong> {{ $project->period_start ?? '-' }}<br>
                                <strong style="color: #ef4444;">End:</strong> {{ $project->period_end ?? '-' }}
                            </td>
                            <td style="font-weight: 600; color: #1e293b;">{{ $project->contract_amount ?? '-' }}</td>
                            <td style="font-weight: 600; color: #1e293b;">{{ $project->actual_obligation ?? '-' }}</td>
                            <td style="font-weight: 600; color: #1e293b;">{{ $project->value_of_acc ?? '-' }}</td>
                            <td style="font-weight: 600; color: #1e293b;">{{ $project->actual_expenditures ?? '-' }}</td>
                            
                            <td style="background: #f8fafc; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                @php
                                    $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
                                @endphp
                                @foreach($months as $m)
                                    <div class="acc-data-{{ $m }}" style="display: {{ $m == 'jan' ? 'block' : 'none' }};">
                                        <div style="font-size: 10px; font-weight: 700; color: #4f46e5; margin-bottom: 5px; text-transform: uppercase;">
                                            {{ ucfirst($m) }} {{ $project->acc_year ?? '' }}
                                        </div>
                                        <span class="acc-badge" style="background:#e0e7ff; color:#4f46e5; border-color:#c7d2fe;">PHY: {{ $project->{$m.'_phy'} ?? '0' }}%</span><br>
                                        <span class="acc-badge" style="background:#ffedd5; color:#ea580c; border-color:#fed7aa; margin-top:4px;">FIN: {{ $project->{$m.'_fin'} ?? '0' }}%</span>
                                    </div>
                                @endforeach
                            </td>

                            <td class="col-desc" style="max-width: 200px;">
                                <div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand/collapse">
                                    {{ $project->remarks }}
                                </div>
                            </td>
                            
                            @if (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                                <td class="action-cell">
                                    <div class="action-buttons">
                                    <button type="button"
                                        class="btn-edit-icon"
                                        title="Edit Project"
                                        onclick="openFsdeEditModal({{ Illuminate\Support\Js::from([
                                            "id" => $project->id,
                                            "year" => $project->year,
                                            "project_name" => $project->project_name,
                                            "municipality" => $project->municipality,
                                            "type_of_study" => $project->type_of_study,
                                            "consultant" => $project->consultant,
                                            "period_start" => $project->period_start,
                                            "period_end" => $project->period_end,
                                            "contract_amount" => $project->contract_amount,
                                            "actual_obligation" => $project->actual_obligation,
                                            "value_of_acc" => $project->value_of_acc,
                                            "actual_expenditures" => $project->actual_expenditures,
                                            "acc_year" => $project->acc_year,
                                            "remarks" => $project->remarks,
                                            "jan_phy" => $project->jan_phy, "jan_fin" => $project->jan_fin,
                                            "feb_phy" => $project->feb_phy, "feb_fin" => $project->feb_fin,
                                            "mar_phy" => $project->mar_phy, "mar_fin" => $project->mar_fin,
                                            "apr_phy" => $project->apr_phy, "apr_fin" => $project->apr_fin,
                                            "may_phy" => $project->may_phy, "may_fin" => $project->may_fin,
                                            "jun_phy" => $project->jun_phy, "jun_fin" => $project->jun_fin,
                                            "jul_phy" => $project->jul_phy, "jul_fin" => $project->jul_fin,
                                            "aug_phy" => $project->aug_phy, "aug_fin" => $project->aug_fin,
                                            "sep_phy" => $project->sep_phy, "sep_fin" => $project->sep_fin,
                                            "oct_phy" => $project->oct_phy, "oct_fin" => $project->oct_fin,
                                            "nov_phy" => $project->nov_phy, "nov_fin" => $project->nov_fin,
                                            "dec_phy" => $project->dec_phy, "dec_fin" => $project->dec_fin,
                                        ]) }})">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('fs.fsde.destroy', $project->id) }}" method="POST" data-async-target="#fsdeSection" data-async-confirm="Are you sure you want to delete this FSDE project?" data-async-success="silent">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete Project">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> 
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ (auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin'])) ? '12' : '11' }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No FSDE reports found in the database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($fsdeProjects) && $fsdeProjects->hasPages())
            <div class="custom-pagination">
                @if ($fsdeProjects->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $fsdeProjects->withQueryString()->previousPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#fsdeSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif
                @foreach ($fsdeProjects->withQueryString()->links()->elements as $element)
                    @if (is_string($element)) <span class="page-item disabled">{{ $element }}</span> @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $fsdeProjects->currentPage()) <span class="page-item active">{{ $page }}</span>
                            @else <a href="{{ $url }}" class="page-item" data-async-pagination="true" data-async-target="#fsdeSection">{{ $page }}</a> @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($fsdeProjects->hasMorePages())
                    <a href="{{ $fsdeProjects->withQueryString()->nextPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#fsdeSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    <div class="modal-overlay" id="addDataModal">
        <div class="modal-box">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add New Hydro-Geo Data</h3>
            <form action="{{ route('fs.hydro.store') }}" method="POST" data-async-target="#hydroSection" data-async-reset="true" data-async-close="#addDataModal" data-async-success-modal="#fsSuccessModal">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Year</label><input type="number" name="year" required placeholder="e.g. 2026" class="modern-input" min="2000" max="2100" step="1"></div>
                    <div><label class="modern-label">District</label><input type="text" name="district" required placeholder="e.g. 1st District" class="modern-input" maxlength="100"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Project Code</label><input type="text" name="project_code" required placeholder="e.g. EGPIP-Solar" class="modern-input" maxlength="100"></div>
                    <div>
                        <label class="modern-label">Municipality / City</label>
                        <input type="text" name="municipality" list="pangasinanMunisHydro" class="modern-input" placeholder="Select or type..." required maxlength="100">
                        <datalist id="pangasinanMunisHydro">
                            <option value="Agno"></option><option value="Aguilar"></option><option value="Alaminos City"></option>
                            <option value="Alcala"></option><option value="Anda"></option><option value="Asingan"></option>
                            <option value="Balungao"></option><option value="Bani"></option><option value="Basista"></option>
                            <option value="Bautista"></option><option value="Bayambang"></option><option value="Binalonan"></option>
                            <option value="Binmaley"></option><option value="Bolinao"></option><option value="Bugallon"></option>
                            <option value="Burgos"></option><option value="Calasiao"></option><option value="Dagupan City"></option>
                            <option value="Dasol"></option><option value="Infanta"></option><option value="Labrador"></option>
                            <option value="Laoac"></option><option value="Lingayen"></option><option value="Mabini"></option>
                            <option value="Malasiqui"></option><option value="Manaoag"></option><option value="Mangaldan"></option>
                            <option value="Mangatarem"></option><option value="Mapandan"></option><option value="Natividad"></option>
                            <option value="Pozorrubio"></option><option value="Rosales"></option><option value="San Carlos City"></option>
                            <option value="San Fabian"></option><option value="San Jacinto"></option><option value="San Manuel"></option>
                            <option value="San Nicolas"></option><option value="San Quintin"></option><option value="Santa Barbara"></option>
                            <option value="Santa Maria"></option><option value="Santo Tomas"></option><option value="Sison"></option>
                            <option value="Sual"></option><option value="Tayug"></option><option value="Umingan"></option>
                            <option value="Urbiztondo"></option><option value="Urdaneta City"></option><option value="Villasis"></option>
                        </datalist>
                    </div>
                </div>
                <div><label class="modern-label">System Name</label><input type="text" name="system_name" required placeholder="e.g. Alilao SPIP" class="modern-input" maxlength="255"></div>
                <div><label class="modern-label">Description / Remarks</label><textarea name="description" required rows="3" class="modern-input" style="resize: none;" placeholder="Project description..." maxlength="2000"></textarea></div>
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
                    <div><label class="modern-label">Result</label><input type="text" name="result" placeholder="e.g. Feasible, -, etc." class="modern-input" maxlength="100"></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="addFsdeModal">
        <div class="modal-box" style="max-width: 600px;">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add New FSDE Data</h3>
            <form action="{{ route('fs.fsde.store') }}" method="POST" data-async-target="#fsdeSection" data-async-reset="true" data-async-close="#addFsdeModal" data-async-success-modal="#fsSuccessModal">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Year</label><input type="number" name="year" required class="modern-input" min="2000" max="2100" step="1"></div>
                    <div><label class="modern-label">Type of Study</label><input type="text" name="type_of_study" required class="modern-input" maxlength="255"></div>
                </div>
                
                <div><label class="modern-label">Project Name</label><input type="text" name="project_name" required class="modern-input" maxlength="1000"></div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Municipality / City</label>
                        <input type="text" name="municipality" list="pangasinanMunisFsde" class="modern-input" placeholder="Select or type..." required maxlength="100">
                        <datalist id="pangasinanMunisFsde">
                            <option value="Agno"></option><option value="Aguilar"></option><option value="Alaminos City"></option>
                            <option value="Alcala"></option><option value="Anda"></option><option value="Asingan"></option>
                            <option value="Balungao"></option><option value="Bani"></option><option value="Basista"></option>
                            <option value="Bautista"></option><option value="Bayambang"></option><option value="Binalonan"></option>
                            <option value="Binmaley"></option><option value="Bolinao"></option><option value="Bugallon"></option>
                            <option value="Burgos"></option><option value="Calasiao"></option><option value="Dagupan City"></option>
                            <option value="Dasol"></option><option value="Infanta"></option><option value="Labrador"></option>
                            <option value="Laoac"></option><option value="Lingayen"></option><option value="Mabini"></option>
                            <option value="Malasiqui"></option><option value="Manaoag"></option><option value="Mangaldan"></option>
                            <option value="Mangatarem"></option><option value="Mapandan"></option><option value="Natividad"></option>
                            <option value="Pozorrubio"></option><option value="Rosales"></option><option value="San Carlos City"></option>
                            <option value="San Fabian"></option><option value="San Jacinto"></option><option value="San Manuel"></option>
                            <option value="San Nicolas"></option><option value="San Quintin"></option><option value="Santa Barbara"></option>
                            <option value="Santa Maria"></option><option value="Santo Tomas"></option><option value="Sison"></option>
                            <option value="Sual"></option><option value="Tayug"></option><option value="Umingan"></option>
                            <option value="Urbiztondo"></option><option value="Urdaneta City"></option><option value="Villasis"></option>
                        </datalist>
                    </div>
                    <div><label class="modern-label">Consultant</label><input type="text" name="consultant" required class="modern-input" maxlength="255"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Period Start</label><input type="date" name="period_start" class="modern-input"></div>
                    <div><label class="modern-label">Period End</label><input type="date" name="period_end" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract Amount</label><input type="number" name="contract_amount" class="modern-input" min="0" step="0.01"></div>
                    <div><label class="modern-label">Actual Obligation</label><input type="number" name="actual_obligation" class="modern-input" min="0" step="0.01"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Value of Acc.</label><input type="number" name="value_of_acc" class="modern-input" min="0" step="0.01"></div>
                    <div><label class="modern-label">Actual Expend.</label><input type="number" name="actual_expenditures" class="modern-input" min="0" step="0.01"></div>
                </div>

                <div style="margin-bottom: 15px; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; background: #f8fafc;">
                    <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Accomplishment As Of</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                        <select name="acc_month" class="modern-input" required style="margin-bottom: 0;">
                            <option value="" disabled selected>Select Month...</option>
                            <option value="jan">January</option>
                            <option value="feb">February</option>
                            <option value="mar">March</option>
                            <option value="apr">April</option>
                            <option value="may">May</option>
                            <option value="jun">June</option>
                            <option value="jul">July</option>
                            <option value="aug">August</option>
                            <option value="sep">September</option>
                            <option value="oct">October</option>
                            <option value="nov">November</option>
                            <option value="dec">December</option>
                        </select>
                        <input type="number" name="acc_year" class="modern-input" placeholder="Year (e.g. 2026)" style="margin-bottom: 0;" required min="2000" max="2100" step="1">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><input type="number" name="acc_phy" class="modern-input" placeholder="PHY (%)" style="background: #ffffff; margin-bottom: 0;" min="0" max="100" step="0.01"></div>
                        <div><input type="number" name="acc_fin" class="modern-input" placeholder="FIN (%)" style="background: #ffffff; margin-bottom: 0;" min="0" max="100" step="0.01"></div>
                    </div>
                </div>

                <div><label class="modern-label">Remarks</label><textarea name="remarks" rows="2" class="modern-input" style="resize: none;" maxlength="2000"></textarea></div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeFsdeAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editHydroModal">
        <div class="modal-box">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Edit Hydro-Geo Data</h3>
            <form id="editHydroForm" method="POST" data-async-target="#hydroSection" data-async-close="#editHydroModal" data-async-success-modal="#fsSuccessModal">
                @csrf
                @method('PUT')
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Year</label><input type="number" id="edit-hydro-year" name="year" required class="modern-input" min="2000" max="2100" step="1"></div>
                    <div><label class="modern-label">District</label><input type="text" id="edit-hydro-district" name="district" required class="modern-input" maxlength="100"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Project Code</label><input type="text" id="edit-hydro-project_code" name="project_code" required class="modern-input" maxlength="100"></div>
                    <div><label class="modern-label">System Name</label><input type="text" id="edit-hydro-system_name" name="system_name" required class="modern-input" maxlength="255"></div>
                </div>
                <div><label class="modern-label">Description / Remarks</label><textarea id="edit-hydro-description" name="description" rows="3" class="modern-input" style="resize: none;" maxlength="2000"></textarea></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Municipality / City</label>
                        <input type="text" id="edit-hydro-municipality" name="municipality" list="pangasinanMunisHydro" class="modern-input" required maxlength="100">
                    </div>
                    <div>
                        <label class="modern-label">Status</label>
                        <select id="edit-hydro-status" name="status" required class="modern-input">
                            <option value="For Schedule">For Schedule</option>
                            <option value="For Interpretation">For Interpretation</option>
                            <option value="For Submission of Raw data">For Submission of Raw data</option>
                            <option value="Relocation">Relocation</option>
                            <option value="Interpreted">Interpreted</option>
                            <option value="Not Applicable">Not Applicable</option>
                            <option value="C/O Contractor">C/O Contractor</option>
                            <option value="Open Source">Open Source</option>
                            <option value="With Geo-res">With Geo-res</option>
                        </select>
                    </div>
                </div>
                <div><label class="modern-label">Result</label><input type="text" id="edit-hydro-result" name="result" class="modern-input" maxlength="100"></div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeHydroEditModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editFsdeModal">
        <div class="modal-box" style="max-width: 600px;">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Edit FSDE Data</h3>
            <form id="editFsdeForm" method="POST" data-async-target="#fsdeSection" data-async-close="#editFsdeModal" data-async-success-modal="#fsSuccessModal">
                @csrf
                @method('PUT')
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Year</label><input type="number" id="edit-fsde-year" name="year" required class="modern-input" min="2000" max="2100" step="1"></div>
                    <div><label class="modern-label">Type of Study</label><input type="text" id="edit-fsde-type_of_study" name="type_of_study" required class="modern-input" maxlength="255"></div>
                </div>
                <div><label class="modern-label">Project Name</label><input type="text" id="edit-fsde-project_name" name="project_name" required class="modern-input" maxlength="1000"></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Municipality / City</label>
                        <input type="text" id="edit-fsde-municipality" name="municipality" list="pangasinanMunisHydro" class="modern-input" required maxlength="100">
                    </div>
                    <div><label class="modern-label">Consultant</label><input type="text" id="edit-fsde-consultant" name="consultant" required class="modern-input" maxlength="255"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Period Start</label><input type="date" id="edit-fsde-period_start" name="period_start" class="modern-input"></div>
                    <div><label class="modern-label">Period End</label><input type="date" id="edit-fsde-period_end" name="period_end" class="modern-input"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract Amount</label><input type="number" id="edit-fsde-contract_amount" name="contract_amount" class="modern-input" min="0" step="0.01"></div>
                    <div><label class="modern-label">Actual Obligation</label><input type="number" id="edit-fsde-actual_obligation" name="actual_obligation" class="modern-input" min="0" step="0.01"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Value of Accomplishment</label><input type="number" id="edit-fsde-value_of_acc" name="value_of_acc" class="modern-input" min="0" step="0.01"></div>
                    <div><label class="modern-label">Actual Expenditures</label><input type="number" id="edit-fsde-actual_expenditures" name="actual_expenditures" class="modern-input" min="0" step="0.01"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Accomplishment Month</label>
                        <select id="edit-fsde-acc_month" name="acc_month" class="modern-input" onchange="syncFsdeMonthFields(this.value)">
                            <option value="jan">January</option><option value="feb">February</option><option value="mar">March</option>
                            <option value="apr">April</option><option value="may">May</option><option value="jun">June</option>
                            <option value="jul">July</option><option value="aug">August</option><option value="sep">September</option>
                            <option value="oct">October</option><option value="nov">November</option><option value="dec">December</option>
                        </select>
                    </div>
                    <div><label class="modern-label">Accomplishment Year</label><input type="number" id="edit-fsde-acc_year" name="acc_year" class="modern-input" min="2000" max="2100" step="1"></div>
                    <div></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">PHY (%)</label><input type="number" id="edit-fsde-acc_phy" name="acc_phy" class="modern-input" min="0" max="100" step="0.01"></div>
                    <div><label class="modern-label">FIN (%)</label><input type="number" id="edit-fsde-acc_fin" name="acc_fin" class="modern-input" min="0" max="100" step="0.01"></div>
                </div>
                <div><label class="modern-label">Remarks</label><textarea id="edit-fsde-remarks" name="remarks" rows="3" class="modern-input" style="resize: none;" maxlength="2000"></textarea></div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeFsdeEditModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="fsSuccessModal">
        <div class="modal-box">
            <h3 data-success-title style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Success</h3>
            <p data-success-message style="font-size: 14px; color: #475569; margin-bottom: 25px;">Saved successfully.</p>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeFsSuccessModal()" class="modern-btn" style="flex: 1;">OK</button>
            </div>
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
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 20 } } }
                }
            });
        });

        let activeMonth = new Date().getMonth() + 1;
        document.addEventListener('DOMContentLoaded', function() { updateCalendarView(); });

        function changeMonth(direction) {
            activeMonth += direction;
            if (activeMonth < 1) activeMonth = 1;
            if (activeMonth > 12) activeMonth = 12;
            updateCalendarView();
        }

        function updateCalendarView() {
            document.querySelectorAll('.month-block').forEach(block => { block.classList.remove('active'); });
            const current = document.getElementById('month-' + activeMonth);
            if (current) current.classList.add('active');
            document.getElementById('prevMonthBtn').disabled = (activeMonth === 1);
            document.getElementById('nextMonthBtn').disabled = (activeMonth === 12);
        }

        function openAddModal() { document.getElementById('addDataModal').classList.add('active'); }
        function closeAddModal() { document.getElementById('addDataModal').classList.remove('active'); }

        function openFsdeAddModal() { document.getElementById('addFsdeModal').classList.add('active'); }
        function closeFsdeAddModal() { document.getElementById('addFsdeModal').classList.remove('active'); }
        let currentFsdeEditRecord = null;

        function openHydroEditModal(id, year, district, projectCode, systemName, description, municipality, status, result) {
            const form = document.getElementById('editHydroForm');
            form.action = `/fs-team/hydro-geo/${id}`;
            document.getElementById('edit-hydro-year').value = year;
            document.getElementById('edit-hydro-district').value = district;
            document.getElementById('edit-hydro-project_code').value = projectCode;
            document.getElementById('edit-hydro-system_name').value = systemName;
            document.getElementById('edit-hydro-description').value = description;
            document.getElementById('edit-hydro-municipality').value = municipality;
            document.getElementById('edit-hydro-status').value = status;
            document.getElementById('edit-hydro-result').value = result;
            document.getElementById('editHydroModal').classList.add('active');
        }

        function closeHydroEditModal() {
            document.getElementById('editHydroModal').classList.remove('active');
        }

        function openFsdeEditModal(record) {
            currentFsdeEditRecord = record;
            const form = document.getElementById('editFsdeForm');
            form.action = `/fs-team/fsde/${record.id}`;
            document.getElementById('edit-fsde-year').value = record.year ?? '';
            document.getElementById('edit-fsde-type_of_study').value = record.type_of_study ?? '';
            document.getElementById('edit-fsde-project_name').value = record.project_name ?? '';
            document.getElementById('edit-fsde-municipality').value = record.municipality ?? '';
            document.getElementById('edit-fsde-consultant').value = record.consultant ?? '';
            document.getElementById('edit-fsde-period_start').value = record.period_start ?? '';
            document.getElementById('edit-fsde-period_end').value = record.period_end ?? '';
            document.getElementById('edit-fsde-contract_amount').value = record.contract_amount ?? '';
            document.getElementById('edit-fsde-actual_obligation').value = record.actual_obligation ?? '';
            document.getElementById('edit-fsde-value_of_acc').value = record.value_of_acc ?? '';
            document.getElementById('edit-fsde-actual_expenditures').value = record.actual_expenditures ?? '';
            document.getElementById('edit-fsde-acc_year').value = record.acc_year ?? '';
            document.getElementById('edit-fsde-remarks').value = record.remarks ?? '';

            const monthOrder = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            const detectedMonth = monthOrder.find(month => record[`${month}_phy`] !== null || record[`${month}_fin`] !== null) || 'jan';
            document.getElementById('edit-fsde-acc_month').value = detectedMonth;
            syncFsdeMonthFields(detectedMonth);
            document.getElementById('editFsdeModal').classList.add('active');
        }

        function syncFsdeMonthFields(month) {
            if (!currentFsdeEditRecord) return;
            document.getElementById('edit-fsde-acc_phy').value = currentFsdeEditRecord[`${month}_phy`] ?? '';
            document.getElementById('edit-fsde-acc_fin').value = currentFsdeEditRecord[`${month}_fin`] ?? '';
        }

        function closeFsdeEditModal() {
            document.getElementById('editFsdeModal').classList.remove('active');
            currentFsdeEditRecord = null;
        }

        function closeFsSuccessModal() {
            document.getElementById('fsSuccessModal').classList.remove('active');
        }

        async function handleHydroExport(event, url) {
            event.preventDefault();

            const suggestedName = 'HYDRO-GEO.xlsx';

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Download failed.');
                }

                const blob = await response.blob();

                if ('showSaveFilePicker' in window) {
                    const handle = await window.showSaveFilePicker({
                        suggestedName,
                        types: [{
                            description: 'Excel Workbook',
                            accept: {
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx']
                            }
                        }]
                    });

                    const writable = await handle.createWritable();
                    await writable.write(blob);
                    await writable.close();
                    return;
                }

                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = suggestedName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(blobUrl);
            } catch (error) {
                window.location.href = url;
            }
        }

        async function handleFsdeExport(event, url) {
            event.preventDefault();

            const suggestedName = `MONTHLY FSDE STATUS REPORT ${new Date().toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            }).replace(',', '')}.xlsx`;

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Download failed.');
                }

                const blob = await response.blob();

                if ('showSaveFilePicker' in window) {
                    const handle = await window.showSaveFilePicker({
                        suggestedName,
                        types: [{
                            description: 'Excel Workbook',
                            accept: {
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx']
                            }
                        }]
                    });

                    const writable = await handle.createWritable();
                    await writable.write(blob);
                    await writable.close();
                    return;
                }

                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = suggestedName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(blobUrl);
            } catch (error) {
                window.location.href = url;
            }
        }

        function toggleAccMonth(val) {
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            months.forEach(m => {
                document.querySelectorAll('.acc-data-' + m).forEach(el => {
                    el.style.display = (m === val) ? 'block' : 'none';
                });
            });
        }
    </script>
@endsection
