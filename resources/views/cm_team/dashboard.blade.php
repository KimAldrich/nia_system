@extends('layouts.app')
@section('title', 'Contract Management Team Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

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

        .table-responsive { width: 100%; max-width: 100%; display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 15px; scrollbar-width: thin; }
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sleek-table { width: 100%; border-collapse: collapse; table-layout: fixed;}
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
        .badge-dark { background: #0c4d05; color: #fff; }
        .badge-light { background: #fda611; color: #ffffff; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }

        .btn-delete { background: #fee2e2; color: #ef4444; border: none; padding: 10px 18px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 105px; line-height: 1; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1); }
        .btn-delete:hover { background: #fecaca; color: #b91c1c; transform: translateY(-1px);}

        .status-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #e4e4e7; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 600; background: #ffffff; color: #18181b; cursor: pointer; outline: none; transition: 0.2s; }
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

        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; }
        input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        @media (max-width: 1300px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <h1 class="header-title">Contract Management Team Dashboard</h1>

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
                                @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
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
                                    @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                                        <td style="text-align: right;">
                                            <form action="{{ route('cm.resolutions.update_status', $res->id) }}" method="POST" onsubmit="return handleAjaxSubmit(event, 'activeProjectsContainer')">
                                                @csrf
                                                <select name="status" class="status-select" onchange="this.form.dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))">
                                                    <option value="not-validated" {{ $res->status == 'not-validated' ? 'selected' : '' }}>Not-Validated</option>
                                                    <option value="on-going" {{ $res->status == 'on-going' ? 'selected' : '' }}>On-Going</option>
                                                    <option value="validated" {{ $res->status == 'validated' ? 'selected' : '' }}>Validated</option>
                                                </select>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']) ? '3' : '2' }}" style="text-align:center; color:#a1a1aa; padding: 30px 0;">No projects uploaded yet.</td></tr>
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
                                    })->groupBy(function ($e) { return $e->event_date->format('j'); });
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header"><h4>{{ $monthDate->format('F Y') }}</h4></div>
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
                                        <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}" style="{{ $hasEvent && !$isToday ? 'border-color:' . $ringColor . '; color:' . $ringColor : '' }}">
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
                                <div><h4 class="mini-event-title">{{ $event->title }}</h4><p class="mini-event-time">{{ $event->event_time }}</p></div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: #a1a1aa; text-align: center; margin-top: 20px;">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="ui-card" style="margin-top: 24px;">
    <div class="section-title">
        Procurement Status Monitoring
        
        <div style="display: flex; gap: 15px; align-items: center;">
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

            @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                <button onclick="openProcAddModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                    + Add Data
                </button>
            @endif
            
<button onclick="exportToExcel()" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Export Excel
</button>
        </div>
    </div>
    
    <div class="table-responsive" id="procurementTableContainer">
        <table class="sleek-table" id="procTable" style="min-width: 1700px;">
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
                    @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                        <th style="text-align: center; width: 7%;">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($procurementProjects ?? [] as $project)
                    <tr>
                        <td style="font-weight: 700; color: #0c4d05; font-size: 14px;">{{ $project->proj_no }}</td>
                        <td class="col-system" style="max-width: 250px; white-space: normal; word-break: break-word;">
                            <div style="font-size:9px; color:#a1a1aa; font-weight:600; margin-bottom:4px; text-transform:uppercase; letter-spacing: 0.5px;">{{ $project->category }}</div>
                            <span style="display: block;">{{ $project->name_of_project }}</span>
                        </td>
                        <td>{{ $project->municipality }}</td>
                        <td style="line-height: 1.8;"><span style="color:#16a34a; font-weight:700;">Alloc:</span> {{ $project->allocation ?: '-' }}<br><span style="color:#4f46e5; font-weight:700;">ABC:</span> {{ $project->abc ?: '-' }}</td>
                        <td style="line-height: 1.8; font-size: 11px;"><strong style="color:#1e293b;">Bid Out:</strong> {{ $project->bid_out ?: '0' }}<br><strong style="color:#1e293b;">For Bidding:</strong> {{ $project->for_bidding ?: '0' }}<br><strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->date_of_bidding ?: '-' }}</span></td>
                        <td style="line-height: 1.8; font-size: 11px;"><strong style="color:#1e293b;">Awarded:</strong> {{ $project->awarded ?: '0' }}<br><strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->date_of_award ?: '-' }}</span></td>
                        <td style="line-height: 1.8;"><strong style="color:#1e293b; font-size: 11px;">No:</strong> {{ $project->contract_no ?: '-' }}<br><span style="color:#ea580c; font-weight:700;">Amt:</span> {{ $project->contract_amount ?: '-' }}</td>
                        <td>{{ $project->name_of_contractor ?: '-' }}</td>
                        <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">{{ $project->remarks }}</div></td>
                        <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">{{ $project->project_description }}</div></td>
                        @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                            <td style="text-align: center;">
                                <form action="{{ route('cm.procurement.destroy', $project->id) }}" method="POST" onsubmit="return handleAjaxSubmit(event, 'procurementTableContainer', 'Are you sure you want to delete this project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Delete Project">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Del
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin'])) ? '11' : '10' }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No Procurement records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($procurementProjects) && $procurementProjects->hasPages())
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

        @if(isset($procurementProjects) && $procurementProjects->hasPages())
            <div class="custom-pagination">
                @if ($procurementProjects->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $procurementProjects->withQueryString()->previousPageUrl() }}" class="page-item"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif
                @foreach ($procurementProjects->withQueryString()->links()->elements as $element)
                    @if (is_string($element)) <span class="page-item disabled">{{ $element }}</span> @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $procurementProjects->currentPage()) <span class="page-item active">{{ $page }}</span>
                            @else <a href="{{ $url }}" class="page-item">{{ $page }}</a> @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($procurementProjects->hasMorePages())
                    <a href="{{ $procurementProjects->withQueryString()->nextPageUrl() }}" class="page-item"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    <div class="modal-overlay" id="addProcModal">
        <div class="modal-box" style="max-width: 600px;">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add Procurement Data</h3>
            
            <form action="{{ route('cm.procurement.store') }}" method="POST" onsubmit="return handleAjaxSubmit(event, 'procurementTableContainer', null, true)">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Category</label>
                        <input type="text" name="category" list="catList" class="modern-input" placeholder="Select or type new category..." required>
                        <datalist id="catList">
                            @foreach($procCategories ?? [] as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div><label class="modern-label">Project No.</label><input type="text" name="proj_no" class="modern-input" placeholder="e.g. 1" required></div>
                </div>
                
                <div><label class="modern-label">Name of Project</label><input type="text" name="name_of_project" required class="modern-input"></div>
                
                <div>
                    <label class="modern-label">Municipality / City</label>
                    <input type="text" name="municipality" list="pangasinanMunis" class="modern-input" placeholder="Select or type municipality..." required>
                    <datalist id="pangasinanMunis">
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Allocation</label><input type="text" name="allocation" class="modern-input" placeholder="0.00"></div>
                    <div><label class="modern-label">ABC</label><input type="text" name="abc" class="modern-input" placeholder="0.00"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Bid-Out</label><input type="text" name="bid_out" class="modern-input"></div>
                    <div><label class="modern-label">For Bidding</label><input type="text" name="for_bidding" class="modern-input"></div>
                    <div><label class="modern-label">Date of Bidding</label><input type="date" name="date_of_bidding" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Awarded</label><input type="text" name="awarded" class="modern-input"></div>
                    <div><label class="modern-label">Date of Award</label><input type="date" name="date_of_award" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract No.</label><input type="text" name="contract_no" class="modern-input"></div>
                    <div><label class="modern-label">Contract Amount</label><input type="text" name="contract_amount" class="modern-input"></div>
                </div>

                <div><label class="modern-label">Contractor Name</label><input type="text" name="name_of_contractor" class="modern-input"></div>
                
                <div><label class="modern-label">Remarks</label><input type="text" name="remarks" class="modern-input"></div>
                <div><label class="modern-label">Project Description</label><textarea name="project_description" rows="2" class="modern-input" style="resize: none;"></textarea></div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeProcAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;" id="saveProcBtn">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#a1a1aa';

            const ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, { type: 'bar', data: { labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], datasets: [{ label: 'Uploads', data: [5, 12, 8, 15], backgroundColor: '#0c4d05', borderRadius: 6, barPercentage: 0.5 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f4f4f5' }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } } });

            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, { type: 'doughnut', data: { labels: ['Validated', 'On-Going', 'Pending'], datasets: [{ data: [45, 30, 25], backgroundColor: ['#0c4d05', '#fda611', '#e1e1ef'], borderColor: '#e4e4e7', borderWidth: 2, hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 20 } } } } });
        });

        let activeMonth = new Date().getMonth() + 1;
        document.addEventListener('DOMContentLoaded', function() { updateCalendarView(); });

        function changeMonth(direction) { activeMonth += direction; if (activeMonth < 1) activeMonth = 1; if (activeMonth > 12) activeMonth = 12; updateCalendarView(); }
        function updateCalendarView() { document.querySelectorAll('.month-block').forEach(block => { block.classList.remove('active'); }); const current = document.getElementById('month-' + activeMonth); if (current) current.classList.add('active'); document.getElementById('prevMonthBtn').disabled = (activeMonth === 1); document.getElementById('nextMonthBtn').disabled = (activeMonth === 12); }
        function openProcAddModal() { document.getElementById('addProcModal').classList.add('active'); }
        function closeProcAddModal() { document.getElementById('addProcModal').classList.remove('active'); }

        // 🌟 NEW: SMART EXCEL EXPORTER THAT FORMATS PERFECTLY 🌟
// 🌟 SMART EXCEL EXPORTER THAT FORMATS PERFECTLY & GRABS ALL DATA 🌟
const rawExportData = @json($procExportData ?? []);

function exportToExcel() {
    if (rawExportData.length === 0) {
        alert("No data available to export.");
        return;
    }

    // 1. Map data to perfectly clean column headers
    const formattedData = rawExportData.map(row => ({
        "No.": row.proj_no || '',
        "Category": row.category || '',
        "Project Name": row.name_of_project || '',
        "Municipality": row.municipality || '',
        "Allocation": row.allocation || '',
        "ABC": row.abc || '',
        "Bid-Out": row.bid_out || '',
        "For Bidding": row.for_bidding || '',
        "Date of Bidding": row.date_of_bidding || '',
        "Awarded": row.awarded || '',
        "Date of Award": row.date_of_award || '',
        "Contract No.": row.contract_no || '',
        "Contract Amount": row.contract_amount || '',
        "Contractor Name": row.name_of_contractor || '',
        "Remarks": row.remarks || '',
        "Description": row.project_description || ''
    }));

    // 2. Generate Worksheet directly from the raw database JSON, NOT the HTML!
    const worksheet = XLSX.utils.json_to_sheet(formattedData);

    // 3. Set exact column widths so it looks beautiful
    worksheet['!cols'] = [
        { wch: 6 },   // No.
        { wch: 35 },  // Category
        { wch: 45 },  // Project Name
        { wch: 20 },  // Municipality
        { wch: 18 },  // Allocation
        { wch: 18 },  // ABC
        { wch: 10 },  // Bid-Out
        { wch: 12 },  // For Bidding
        { wch: 16 },  // Date of Bidding
        { wch: 10 },  // Awarded
        { wch: 16 },  // Date of Award
        { wch: 20 },  // Contract No
        { wch: 20 },  // Contract Amount
        { wch: 25 },  // Contractor Name
        { wch: 30 },  // Remarks
        { wch: 50 }   // Description
    ];

    // 4. Create Workbook
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Procurement Status");

    // 5. Create dynamic filename based on Dropdown Selection
    const urlParams = new URLSearchParams(window.location.search);
    let currentCategory = urlParams.get('proc_category');
    let filename = "Procurement_Status_Report";

    if (currentCategory && currentCategory !== 'All Projects') {
        // Strip bad characters out of the filename
        filename += "_" + currentCategory.replace(/[^a-z0-9]/gi, '_');
    }
    filename += ".xlsx";

    // 6. Download the perfectly formatted file with ALL rows!
    XLSX.writeFile(workbook, filename);
}
    </script>
@endsection