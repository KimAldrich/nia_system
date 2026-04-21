@extends('layouts.app')
@section('title', $pageTitle ?? 'Guest Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { box-sizing: border-box; }
        .content { background-color: #f7f8fa; font-family: 'Poppins', sans-serif; padding: 40px; color: #0c4d05; max-width: 100vw; overflow-x: hidden; }
        .header-title { font-size: 32px; font-weight: 700; margin-bottom: 30px; letter-spacing: -0.5px; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; align-items: start; }
        .main-column, .side-column { min-width: 0; width: 100%; max-width: 100%; }

        .ui-card { background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); margin-bottom: 24px; border: none; width: 100%; min-width: 0; max-width: 100%; display: block; box-sizing: border-box; overflow: hidden; }
        .ui-card.dark { background: #0c4d05; color: #ffffff; border: none; }
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .status-hero { display: flex; justify-content: space-between; align-items: center; }
        .status-hero h3 { margin: 0 0 5px 0; font-size: 18px; font-weight: 600; }
        .status-hero p { margin: 0; font-size: 13px; color: #a1a1aa; }
        .squiggle-line { width: 80px; height: auto; opacity: 0.8; }

        /* SCROLLBAR & TABLE STYLES */
        .table-responsive { width: 100%; max-width: 100%; display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 15px; scrollbar-width: thin; }
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        
        .sleek-table { width: 100%; border-collapse: collapse; table-layout: fixed;}
        .sleek-table th { text-align: left; padding: 12px 15px; color: #a0aec0; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; white-space: normal; vertical-align: middle; line-height: 1.4;}
        .sleek-table td { padding: 15px 15px; border-bottom: 1px solid #f1f5f9; font-size: 12px; font-weight: 500; color: #475569; vertical-align: middle; white-space: normal; word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;}
        .sleek-table tr:hover td { background-color: #f8fafc; transition: 0.2s; }
        .sleek-table tr:last-child td { border-bottom: none; }
        
        .col-system { font-weight: 700; color: #1e293b; }
        .col-desc { color: #64748b; line-height: 1.5; }

        .text-clamp { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer; transition: color 0.2s; text-overflow: ellipsis; }
        .text-clamp.expanded { display: block; -webkit-line-clamp: unset; }

        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; display: inline-block; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; max-width: 100%; white-space: normal; word-wrap: break-word; text-align: center; }
        .badge-dark { background: #0c4d05; color: #fff; }
        .badge-light { background: #fda611; color: #ffffff; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }
        .badge-schedule { background: #ffedd5; color: #ea580c; }
        .badge-interpretation { background: #e0e7ff; color: #4f46e5; }
        .badge-submission { background: #f3e8ff; color: #9333ea; }
        .badge-relocation { background: #fee2e2; color: #ef4444; }
        .badge-feasible { background: #dcfce7; color: #16a34a; }
        .badge-na { background: #f1f5f9; color: #64748b; }
        .acc-badge { background: #f8fafc; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 6px; font-weight: 700; color: #1e293b; font-size: 11px; display: inline-block; white-space: nowrap;}

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
        .trend-neutral { color: #f59e0b; background: #ffedd5; padding: 2px 6px; border-radius: 4px;}
        .trend-text { color: #a0aec0; font-weight: 500; }

        /* Pagination */
        .custom-pagination { display: flex; justify-content: flex-end; align-items: center; margin-top: 20px; gap: 8px; font-family: 'Poppins', sans-serif;}
        .custom-pagination .page-item { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; border-radius: 8px; background: #ffffff; color: #64748b; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; transition: 0.2s; }
        .custom-pagination .page-item:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .custom-pagination .page-item.active { background: #4f46e5; color: #ffffff; border-color: #4f46e5; }
        
        /* 🌟 FIX: Disabled links can no longer be clicked! 🌟 */
        .custom-pagination .page-item.disabled { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border-color: #f1f5f9; pointer-events: none; }
        
        .modern-input { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; outline: none; background: #ffffff; color: #1e293b; transition: 0.2s;}

        @media (max-width: 1300px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <h1 class="header-title">{{ $pageTitle ?? 'Dashboard' }}</h1>

    @if(isset($db_team) && $db_team === 'fs_team')
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
    @endif

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
                                <th style="width: 70%;">Document Name</th>
                                <th style="width: 30%;">Status</th>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align:center; color:#a1a1aa; padding: 30px 0;">No projects uploaded yet.</td>
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
                <div class="section-title" style="margin-bottom: 15px;">Events Calendar</div>

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
                @endphp

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">&lt;</button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
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
                        @foreach ($events->take(5) as $event)
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

    @if(isset($db_team) && $db_team === 'fs_team' && isset($hydroProjects) && isset($fsdeProjects))
        <div class="ui-card" style="margin-top: 24px;">
            <div class="section-title">Hydro-Georesistivity Status Monitoring</div>
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>Year</th><th>District</th><th>Project Code</th><th>System Name</th>
                            <th>Description / Remarks</th><th>Municipality</th><th>Status</th><th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hydroProjects as $project)
                            <tr>
                                <td>{{ $project->year }}</td><td>{{ $project->district }}</td><td>{{ $project->project_code }}</td>
                                <td class="col-system">{{ $project->system_name }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $project->description }}</div></td>
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
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center; padding: 30px 0; color: #a0aec0;">No projects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($hydroProjects->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $hydroProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $hydroProjects->onFirstPage() ? 'disabled' : '' }}">&lt;</a>
                    
                    @php
                        $hStart = max($hydroProjects->currentPage() - 2, 1);
                        $hEnd = min($hStart + 4, $hydroProjects->lastPage());
                        if ($hEnd - $hStart < 4) { $hStart = max($hEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $hStart; $page <= $hEnd; $page++)
                        <a href="{{ $hydroProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $hydroProjects->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $hydroProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$hydroProjects->hasMorePages() ? 'disabled' : '' }}">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card">
            <div class="section-title">Monthly FSDE Status Report</div>
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th>Year</th><th>Project Name</th><th>Municipality</th><th>Study Type</th>
                            <th>Consultant</th><th>Period of Engagement</th><th>Contract Amount</th>
                            <th>Actual Obligation</th><th>Value of Acc.</th><th>Actual Expend.</th>
                            <th style="min-width: 140px; background: #e0e7ff; border-radius: 6px 6px 0 0;">
                                Accomplishment As Of
                                <select id="accMonthSelector" onchange="toggleAccMonth(this.value)" class="modern-input" style="padding: 4px; font-size: 10px; height: auto; margin-top: 5px; width: 100%; border-color: #c7d2fe; cursor: pointer;">
                                    <option value="jan">January</option><option value="feb">February</option><option value="mar">March</option>
                                    <option value="apr">April</option><option value="may">May</option><option value="jun">June</option>
                                    <option value="jul">July</option><option value="aug">August</option><option value="sep">September</option>
                                    <option value="oct">October</option><option value="nov">November</option><option value="dec">December</option>
                                </select>
                            </th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fsdeProjects as $project)
                            <tr>
                                <td>{{ $project->year }}</td><td class="col-system" style="max-width: 200px;">{{ $project->project_name }}</td>
                                <td>{{ $project->municipality }}</td><td>{{ $project->type_of_study }}</td>
                                <td class="col-desc" style="min-width:180px;">{{ $project->consultant }}</td>
                                <td style="min-width:150px; font-size:11px;"><strong style="color:#16a34a;">Start:</strong> {{ $project->period_start ?? '-' }}<br><strong style="color:#ef4444;">End:</strong> {{ $project->period_end ?? '-' }}</td>
                                <td style="font-weight: 600; color: #1e293b;">{{ $project->contract_amount ?? '-' }}</td><td style="font-weight: 600; color: #1e293b;">{{ $project->actual_obligation ?? '-' }}</td>
                                <td style="font-weight: 600; color: #1e293b;">{{ $project->value_of_acc ?? '-' }}</td><td style="font-weight: 600; color: #1e293b;">{{ $project->actual_expenditures ?? '-' }}</td>
                                <td style="background: #f8fafc; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                    @php $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']; @endphp
                                    @foreach($months as $m)
                                        <div class="acc-data-{{ $m }}" style="display: {{ $m == 'jan' ? 'block' : 'none' }};">
                                            <div style="font-size: 10px; font-weight: 700; color: #4f46e5; margin-bottom: 5px; text-transform: uppercase;">{{ ucfirst($m) }} {{ $project->acc_year ?? '' }}</div>
                                            <span class="acc-badge" style="background:#e0e7ff; color:#4f46e5; border-color:#c7d2fe;">PHY: {{ $project->{$m.'_phy'} ?? '0' }}%</span><br>
                                            <span class="acc-badge" style="background:#ffedd5; color:#ea580c; border-color:#fed7aa; margin-top:4px;">FIN: {{ $project->{$m.'_fin'} ?? '0' }}%</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $project->remarks }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="12" style="text-align:center; padding: 30px 0; color: #a0aec0;">No FSDE reports found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($fsdeProjects->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $fsdeProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $fsdeProjects->onFirstPage() ? 'disabled' : '' }}">&lt;</a>
                    
                    @php
                        $fStart = max($fsdeProjects->currentPage() - 2, 1);
                        $fEnd = min($fStart + 4, $fsdeProjects->lastPage());
                        if ($fEnd - $fStart < 4) { $fStart = max($fEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $fStart; $page <= $fEnd; $page++)
                        <a href="{{ $fsdeProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $fsdeProjects->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $fsdeProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$fsdeProjects->hasMorePages() ? 'disabled' : '' }}">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'pao_team' && isset($powData))
        <div class="ui-card" style="margin-top: 24px;">
            <div class="section-title">
                Program of Works Status Monitoring
                <a href="{{ route('guest.pao.pow.export') }}" onclick="handlePowExport(event, this.href)"
                    style="background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </a>
            </div>
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1400px;">
                    <thead>
                        <tr>
                            <th rowspan="2">District</th>
                            <th rowspan="2">No. of Projects</th>
                            <th rowspan="2">Total Allocation</th>
                            <th rowspan="2">No. of Plans Received</th>
                            <th rowspan="2">No. of Project Estimate Received</th>
                            <th colspan="3" style="text-align: center; font-weight: 600; color: #a0aec0;">Status of Program of Works</th>
                            <th rowspan="2">On Going POW Preparation</th>
                            <th rowspan="2">POW for Submission</th>
                            <th rowspan="2">Remarks</th>
                        </tr>
                        <tr>
                            <th>No. of POW Prepared</th>
                            <th>No. of POW Approved</th>
                            <th>No. of POW Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($powData as $data)
                            <tr>
                                <td>{{ $data->district }}</td>
                                <td>{{ $data->no_of_projects }}</td>
                                <td>&#8369;{{ number_format($data->total_allocation, 2) }}</td>
                                <td>{{ $data->no_of_plans_received }}</td>
                                <td>{{ $data->no_of_project_estimate_received }}</td>
                                <td>{{ $data->pow_received }}</td>
                                <td>{{ $data->pow_approved }}</td>
                                <td>{{ $data->pow_submitted }}</td>
                                <td>{{ $data->ongoing_pow_preparation }}</td>
                                <td>{{ $data->pow_for_submission }}</td>
                                <td class="col-desc">{{ $data->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align:center; padding: 30px 0; color: #a0aec0;">No data found in the database.</td>
                            </tr>
                        @endforelse

                        @if(isset($powData) && $powData->count())
                            <tr style="font-weight: 700; background: #f8fafc; border-top: 2px solid #0c4d05;">
                                <td style="font-weight: 800; color: #0c4d05;">Total</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('no_of_projects') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">&#8369;{{ number_format($powData->sum('total_allocation'), 2) }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('no_of_plans_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('no_of_project_estimate_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('pow_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('pow_approved') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('pow_submitted') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('ongoing_pow_preparation') }}</td>
                                <td style="font-weight: 800; color: #0c4d05;">{{ $powData->sum('pow_for_submission') }}</td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(isset($powData) && $powData->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $powData->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $powData->onFirstPage() ? 'disabled' : '' }}">&lt;</a>

                    @php
                        $pStart = max($powData->currentPage() - 2, 1);
                        $pEnd = min($pStart + 4, $powData->lastPage());
                        if ($pEnd - $pStart < 4) { $pStart = max($pEnd - 4, 1); }
                    @endphp

                    @for ($page = $pStart; $page <= $pEnd; $page++)
                        <a href="{{ $powData->appends(request()->query())->url($page) }}" class="page-item {{ $page == $powData->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endfor

                    <a href="{{ $powData->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$powData->hasMorePages() ? 'disabled' : '' }}">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'cm_team' && isset($procurementProjects))
        <div class="ui-card" style="margin-top: 24px;">
            <div class="section-title">
                Procurement Status Monitoring
                
                <form action="{{ url()->current() }}" method="GET" style="margin: 0;">
                    @foreach(request()->except(['proc_category', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <select name="proc_category" onchange="this.form.submit()" class="modern-input" style="margin-bottom: 0; padding: 8px 12px; width: 280px; font-weight: 600; cursor: pointer; border-color: #0c4d05;">
                        <option value="All Projects">-- Show All Categories --</option>
                        @foreach($procCategories ?? [] as $cat)
                            <option value="{{ $cat }}" {{ request('proc_category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No.</th>
                            <th style="width: 16%;">Project Name</th>
                            <th style="width: 10%;">Municipality</th>
                            <th style="width: 14%;">Allocation / ABC</th>
                            <th style="width: 10%;">Bidding Info</th>
                            <th style="width: 10%;">Award Info</th>
                            <th style="width: 13%;">Contract Info</th>
                            <th style="width: 8%;">Contractor</th>
                            <th style="width: 8%;">Remarks</th>
                            <th style="width: 15%;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($procurementProjects as $project)
                            <tr>
                                <td style="font-weight: 700; color: #0c4d05; font-size: 14px;">{{ $project->proj_no }}</td>
                                
                                <td class="col-system" style="max-width: 250px; white-space: normal; word-break: break-word;">
                                    <div style="font-size:9px; color:#a1a1aa; font-weight:600; margin-bottom:4px; text-transform:uppercase; letter-spacing: 0.5px; white-space: normal; word-break: break-word;">
                                        {{ $project->category }}
                                    </div>
                                    <span style="white-space: normal; word-break: break-word; display: block;">
                                        {{ $project->name_of_project }}
                                    </span>
                                </td>
                                
                                <td>{{ $project->municipality }}</td>
                                <td style="line-height: 1.8;">
                                    <span style="color:#16a34a; font-weight:700;">Alloc:</span> {{ $project->allocation ?: '-' }}<br>
                                    <span style="color:#4f46e5; font-weight:700;">ABC:</span> {{ $project->abc ?: '-' }}
                                </td>
                                <td style="line-height: 1.8; font-size: 11px;">
                                    <strong style="color:#1e293b;">Bid Out:</strong> {{ $project->bid_out ?: '0' }}<br>
                                    <strong style="color:#1e293b;">For Bidding:</strong> {{ $project->for_bidding ?: '0' }}<br>
                                    <strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->date_of_bidding ?: '-' }}</span>
                                </td>
                                <td style="line-height: 1.8; font-size: 11px;">
                                    <strong style="color:#1e293b;">Awarded:</strong> {{ $project->awarded ?: '0' }}<br>
                                    <strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->date_of_award ?: '-' }}</span>
                                </td>
                                <td style="line-height: 1.8;">
                                    <strong style="color:#1e293b; font-size: 11px;">No:</strong> {{ $project->contract_no ?: '-' }}<br>
                                    <span style="color:#ea580c; font-weight:700;">Amt:</span> {{ $project->contract_amount ?: '-' }}
                                </td>
                                <td>{{ $project->name_of_contractor ?: '-' }}</td>
                                <td class="col-desc">
                                    <div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">
                                        {{ $project->remarks }}
                                    </div>
                                </td>
                                <td class="col-desc">
                                    <div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">
                                        {{ $project->project_description }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align:center; padding: 30px 0; color: #a0aec0;">No Procurement records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($procurementProjects->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $procurementProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $procurementProjects->onFirstPage() ? 'disabled' : '' }}">&lt;</a>
                    
                    @php
                        $pStart = max($procurementProjects->currentPage() - 2, 1);
                        $pEnd = min($pStart + 4, $procurementProjects->lastPage());
                        if ($pEnd - $pStart < 4) { $pStart = max($pEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $pStart; $page <= $pEnd; $page++)
                        <a href="{{ $procurementProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $procurementProjects->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $procurementProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$procurementProjects->hasMorePages() ? 'disabled' : '' }}">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    <script>
        async function handlePowExport(event, url) {
            event.preventDefault();

            const suggestedName = `program_of_works_status_monitoring_${new Date().toISOString().slice(0, 19).replace(/[-:T]/g, '')}.xlsx`;

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

        // 🌟 1. CHART RENDERER 🌟
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#a1a1aa';

            const ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: { labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], datasets: [{ label: 'Uploads', data: [5, 12, 8, 15], backgroundColor: '#0c4d05', borderRadius: 6, barPercentage: 0.5 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f4f4f5' }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } }
            });

            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: { labels: ['Validated', 'On-Going', 'Pending'], datasets: [{ data: [45, 30, 25], backgroundColor: ['#0c4d05', '#fda611', '#e1e1ef'], borderColor: '#e4e4e7', borderWidth: 2, hoverOffset: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 20 } } } }
            });
        });

        // 🌟 2. CALENDAR RENDERER 🌟
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

        // 🌟 3. TABLE FILTER 🌟
        function toggleAccMonth(val) {
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            months.forEach(m => {
                document.querySelectorAll('.acc-data-' + m).forEach(el => { el.style.display = (m === val) ? 'block' : 'none'; });
            });
        }
    </script>
@endsection
