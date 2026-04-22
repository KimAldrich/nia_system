@extends('layouts.app')
@section('title', 'Programming Team Dashboard')

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
            /* ✅ */
        }

        .header-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
            border: 1px solid #e4e4e7;
        }

        .ui-card.dark {
            background: #0c4d05;
            /* ✅ */
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

        .status-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-hero h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .status-hero p {
            margin: 0;
            font-size: 13px;
            color: #a1a1aa;
        }

        .squiggle-line {
            width: 80px;
            height: auto;
            opacity: 0.8;
        }

        .sleek-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sleek-table th {
            text-align: left;
            padding-bottom: 15px;
            color: #a1a1aa;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f4f4f5;
        }

        .sleek-table td {
            padding: 15px 0;
            border-bottom: 1px solid #f4f4f5;
            font-size: 13px;
            font-weight: 500;
            vertical-align: middle;
        }

        .sleek-table tr:last-child td {
            border-bottom: none;
            padding-bottom: 0;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 90px;
        }

        .badge-dark {
            background: #0c4d05;
            /* ✅ */
            color: #fff;
        }

        .badge-light {
            background: #fda611;
            /* ✅ */
            color: #ffffff;
        }

        .badge-outline {
            border: 1px solid #e4e4e7;
            color: #71717a;
        }

        .status-select {
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #e4e4e7;
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 600;
            background: #ffffff;
            color: #18181b;
            cursor: pointer;
            outline: none;
            transition: 0.2s;
        }

        .status-select:hover {
            border-color: #18181b;
        }

        .btn-delete {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 105px;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
        }

        .btn-delete:hover {
            background: #fecaca;
            color: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-edit-icon {
            background: #e0e7ff;
            color: #4f46e5;
            border: none;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.12);
            flex-shrink: 0;
            white-space: nowrap;
        }

        .btn-edit-icon:hover {
            background: #c7d2fe;
            color: #3730a3;
            transform: translateY(-1px);
        }

        .action-cell {
            text-align: center;
            white-space: nowrap !important;
            word-wrap: normal !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            gap: 5px;
            min-width: max-content;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .calendar-carousel {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn {
            background: #fff;
            border: 1px solid #0c4d05;
            /* ✅ */
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
        }

        .calendar-viewport {
            flex: 1;
        }

        .month-block {
            display: none;
        }

        .month-block.active {
            display: block;
        }

        .cal-nav {
            display: flex;
            gap: 10px;
        }

        .cal-nav button {
            background: none;
            border: 1px solid #0c4d05;
            /* ✅ */
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #a1a1aa;
            transition: 0.2s;
        }

        .cal-nav button:hover {
            border-color: #18181b;
            color: #18181b;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            row-gap: 15px;
            margin-bottom: 25px;
        }

        .day-name {
            font-size: 11px;
            font-weight: 600;
            color: #a1a1aa;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .day-num {
            font-size: 13px;
            font-weight: 600;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
            color: #18181b;
        }

        .day-num.empty {
            visibility: hidden;
        }

        .day-num.has-event {
            border: 2px solid #18181b;
        }

        .day-num.today {
            background: #4fc94d;
            /* ✅ */
        }

        .mini-event {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-top: 1px solid #f4f4f5;
        }

        .mini-event-date {
            font-size: 16px;
            font-weight: 700;
            color: #18181b;
            min-width: 30px;
            text-align: center;
        }

        .mini-event-title {
            font-size: 13px;
            font-weight: 600;
            color: #18181b;
            margin: 0;
        }

        .mini-event-time {
            font-size: 11px;
            color: #a1a1aa;
            margin: 0;
        }

        .chart-wrapper {
            position: relative;
            height: 220px;
            width: 100%;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #71717a;
            text-transform: uppercase;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        /* =========================================
           🌟 NEW: ADD DATA MODAL STYLES 🌟
           ========================================= */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .modern-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; outline: none; background: #f8fafc; color: #1e293b; transition: 0.2s; margin-bottom: 15px; }
        .modern-input:focus { border-color: #0c4d05; background: #ffffff; }
        .modern-label { display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .modern-btn { width: 100%; padding: 10px; background: #0c4d05; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; }
        .modern-btn:hover { background: #083803; }
        .modern-btn-outline { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .modern-btn-outline:hover { background: #f1f5f9; color: #1e293b; }

        /* Table styles for data monitoring */
        .table-responsive { 
            width: 100%; 
            overflow-x: auto; 
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            scrollbar-width: thin;
        }
        
        .table-responsive::-webkit-scrollbar { height: 8px; width: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sleek-table { 
            border-collapse: collapse; 
            width: 100%; 
            min-width: 1400px;
            table-layout: fixed;
        }
        .sleek-table th { 
            text-align: left; 
            padding: 12px 15px; 
            color: #a0aec0; 
            font-weight: 600; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            border-bottom: 1px solid #e2e8f0; 
            background: #f8fafc; 
            white-space: normal;
            word-break: break-word;
            vertical-align: middle;
        }
        .sleek-table td { 
            padding: 15px; 
            border-bottom: 1px solid #f1f5f9; 
            font-size: 12px; 
            font-weight: 500; 
            color: #475569; 
            vertical-align: middle;
            min-width: 120px;
        }
        .sleek-table tr:hover td { background-color: #f8fafc; transition: 0.2s; }
        .sleek-table tr:last-child td { border-bottom: none; }
        
        .col-system { font-weight: 700; color: #1e293b; white-space: nowrap; }
        .col-desc { 
            max-width: 150px; 
            white-space: normal; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            word-wrap: break-word;
        }

        /* Soft UI Pagination Styling */
        .custom-pagination { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin-top: 20px; 
            gap: 8px; 
            font-family: 'Poppins', sans-serif;
            flex-wrap: wrap;
            padding: 0 15px;
        }
        .custom-pagination svg { width: 16px; height: 16px; }
        .custom-pagination .page-item { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            min-width: 32px; 
            height: 32px; 
            border-radius: 8px; 
            background: #ffffff; 
            color: #64748b; 
            font-size: 12px; 
            font-weight: 600; 
            text-decoration: none; 
            border: 1px solid #e2e8f0; 
            transition: 0.2s; 
        }
        .custom-pagination .page-item:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .custom-pagination .page-item.active { background: #4f46e5; color: #ffffff; border-color: #4f46e5; }
        .custom-pagination .page-item.disabled { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border-color: #f1f5f9; }
    </style>

    <h1 class="header-title">Programming Team Dashboard</h1>

    <div class="dashboard-grid">

        <div class="main-column">

            <div class="ui-card dark">
                <div class="status-hero">
                    <div>
                        <h3>Project Status Overview</h3>
                        <p>Track your deliverables, resolutions, and milestones.</p>
                    </div>
                    <svg class="squiggle-line" viewBox="0 0 100 30" fill="none" stroke="white" stroke-width="3"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 15 Q 15 5, 25 15 T 45 15 T 65 15 T 85 15 T 95 5" />
                    </svg>
                </div>
            </div>

            <div class="ui-card">
                <div class="section-title">Active Projects</div>
                <table class="sleek-table" id="activeProjectsContainer">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>

                            @if (auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']))
                                <th style="text-align: right;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolutions as $res)
                            <tr>
                                <td>
                                    <strong>{{ $res->title }}</strong><br>
                                    <span
                                        style="font-size: 11px; color: #a1a1aa;">{{ $res->created_at->format('M d, Y') }}</span>
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

                                @if (auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']))
                                    <td style="text-align: right;">
                                        <form action="{{ route('pao.resolutions.update_status', $res->id) }}"
                                            method="POST" data-async-target="#activeProjectsContainer">
                                            @csrf
                                            <select name="status" class="status-select" data-auto-submit>
                                                <option value="not-validated"
                                                    {{ $res->status == 'not-validated' ? 'selected' : '' }}>
                                                    Not-Validated</option>
                                                <option value="on-going" {{ $res->status == 'on-going' ? 'selected' : '' }}>
                                                    On-Going
                                                </option>
                                                <option value="validated"
                                                    {{ $res->status == 'validated' ? 'selected' : '' }}>
                                                    Validated</option>
                                            </select>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']) ? '3' : '2' }}"
                                    style="text-align:center; color:#a1a1aa; padding: 30px 0;">
                                    No projects uploaded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ui-card">
                <div class="section-title">
                    Analytics
                    <span style="font-size: 12px; color: #a1a1aa; font-weight: 500;">Project Status</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Upload Activity
                        </p>
                        <div class="chart-wrapper"><canvas id="barChart"></canvas></div>
                    </div>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Completion Rate
                        </p>
                        <div class="chart-wrapper"><canvas id="doughnutChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-column">
            <div class="ui-card">
                <div class="section-title" style="margin-bottom: 15px;">New Events</div>

                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f4f4f5;">
                    <p
                        style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">
                        Event Legend
                    </p>

                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($categories as $cat)
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
                        ? $events
                            ->map(function ($e) {
                                return $e->event_date->format('j');
                            })
                            ->toArray()
                        : [];
                @endphp

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">
                        &lt;
                    </button>

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

                                $eventsForMonth = $events
                                    ->filter(function ($e) use ($currentYear, $m) {
                                        return $e->event_date->year == $currentYear && $e->event_date->month == $m;
                                    })
                                    ->groupBy(function ($e) {
                                        return $e->event_date->format('j');
                                    });
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>

                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div>
                                    <div class="day-name">Mon</div>
                                    <div class="day-name">Tue</div>
                                    <div class="day-name">Wed</div>
                                    <div class="day-name">Thu</div>
                                    <div class="day-name">Fri</div>
                                    <div class="day-name">Sat</div>

                                    @for ($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor

                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = $day == $today->day && $m == $today->month;
                                            $ringColor =
                                                $hasEvent && $dayEvents->first()->category
                                                    ? $dayEvents->first()->category->color
                                                    : '#18181b';
                                        @endphp

                                        <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                            style="{{ $hasEvent ? 'border-color:' . $ringColor . '; color:' . $ringColor : '' }}">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">
                        &gt;
                    </button>
                </div>

                <div style="margin-top: 10px;">
                    <p
                        style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">
                        Upcoming Schedule</p>

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
                        <p style="font-size: 12px; color: #a1a1aa; text-align: center; margin-top: 20px;">No upcoming
                            events.
                        </p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @php
        $canManagePow = auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']);
    @endphp

    <div class="ui-card">
        <div class="section-title">
            Program of Works Status Monitoring
            
            <div style="display: flex; gap: 10px;">
                @if ($canManagePow)
                    <button onclick="openAddModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Data
                    </button>
                @endif
                <a href="{{ route('pao.pow.export') }}" onclick="handlePowExport(event, this.href)" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </a>
            </div>
        </div>
        
        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; -webkit-overflow-scrolling: touch;">
            <div class="table-responsive" id="powTableContainer">
                <table class="sleek-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 100px;">District</th>
                            <th rowspan="2" style="width: 110px;">No. of Projects</th>
                            <th rowspan="2" style="width: 130px;">Total Allocation</th>
                            <th rowspan="2" style="width: 140px;">No. of Plans Received</th>
                            <th rowspan="2" style="width: 160px;">No. of Project Estimate Received</th>
                            <th colspan="3" style="width: 420px; text-align: center;">Status of Program of Works</th>
                            <th rowspan="2" style="width: 160px;">On Going POW Preparation</th>
                            <th rowspan="2" style="width: 150px;">POW for Submission</th>
                            <th rowspan="2" style="width: 120px;">Remarks</th>
                            @if ($canManagePow)
                                <th rowspan="2" style="width: 140px;">Actions</th>
                            @endif
                        </tr>
                        <tr>
                            <th style="width: 140px; text-align: center;">No. of POW Prepared</th>
                            <th style="width: 140px; text-align: center;">No. of POW Approved</th>
                            <th style="width: 140px; text-align: center;">No. of POW Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($powData ?? [] as $data)
                            <tr>
                                <td style="width: 100px;">{{ $data->district }}</td>
                                <td style="width: 110px;">{{ $data->no_of_projects }}</td>
                                <td style="width: 130px;">₱{{ number_format($data->total_allocation, 2) }}</td>
                                <td style="width: 140px;">{{ $data->no_of_plans_received }}</td>
                                <td style="width: 160px;">{{ $data->no_of_project_estimate_received }}</td>
                                <td style="width: 140px; text-align: center;">{{ $data->pow_received }}</td>
                                <td style="width: 140px; text-align: center;">{{ $data->pow_approved }}</td>
                                <td style="width: 140px; text-align: center;">{{ $data->pow_submitted }}</td>
                                <td style="width: 160px;">{{ $data->ongoing_pow_preparation }}</td>
                                <td style="width: 150px;">{{ $data->pow_for_submission }}</td>
                                <td style="width: 120px;" class="col-desc">{{ $data->remarks }}</td>
                                @if ($canManagePow)
                                    <td class="action-cell" style="width: 140px;">
                                        <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Data" onclick="openEditModal({{ $data->id }}, '{{ $data->district }}', {{ $data->no_of_projects }}, {{ $data->total_allocation }}, {{ $data->no_of_plans_received }}, {{ $data->no_of_project_estimate_received }}, {{ $data->pow_received }}, {{ $data->pow_approved }}, {{ $data->pow_submitted }}, {{ $data->ongoing_pow_preparation }}, {{ $data->pow_for_submission }}, '{{ addslashes($data->remarks) }}')">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal({{ $data->id }})" class="btn-delete" title="Delete Data">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManagePow ? 12 : 11 }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No data found in the database.</td>
                            </tr>
                        @endforelse

                        @if(isset($powData) && $powData->count())
                            <tr style="font-weight: 700; background: #f8fafc; border-top: 2px solid #0c4d05;">
                                <td style="font-weight: 800; color: #0c4d05; width: 100px;">Total</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 110px;">{{ $powData->sum('no_of_projects') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 130px;">₱{{ number_format($powData->sum('total_allocation'), 2) }}</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 140px;">{{ $powData->sum('no_of_plans_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 160px;">{{ $powData->sum('no_of_project_estimate_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; text-align: center; width: 140px;">{{ $powData->sum('pow_received') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; text-align: center; width: 140px;">{{ $powData->sum('pow_approved') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; text-align: center; width: 140px;">{{ $powData->sum('pow_submitted') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 160px;">{{ $powData->sum('ongoing_pow_preparation') }}</td>
                                <td style="font-weight: 800; color: #0c4d05; width: 150px;">{{ $powData->sum('pow_for_submission') }}</td>
                                <td style="width: 120px;"></td>
                                @if ($canManagePow)
                                    <td style="width: 140px;"></td>
                                @endif
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($powData) && $powData->hasPages())
            <div class="custom-pagination">
                {{-- Previous Page Link --}}
                @if ($powData->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $powData->previousPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#powSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($powData->links()->elements as $element)
                    @if (is_string($element))
                        <span class="page-item disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $powData->currentPage())
                                <span class="page-item active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-item" data-async-pagination="true" data-async-target="#powSection">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($powData->hasMorePages())
                    <a href="{{ $powData->nextPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#powSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    @if ($canManagePow)
        <div class="modal-overlay" id="addDataModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add New Program of Works Data</h3>
                
                <form action="{{ route('pao.pow.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">District</label>
                            <select name="district" required class="modern-input">
                                <option value="">Select District</option>
                                <option value="District 1">District 1</option>
                                <option value="District 2">District 2</option>
                                <option value="District 3">District 3</option>
                                <option value="District 4">District 4</option>
                                <option value="District 5">District 5</option>
                                <option value="District 6">District 6</option>
                            </select>
                        </div>
                        <div>
                            <label class="modern-label">No. of Projects</label>
                            <input type="number" name="no_of_projects" required placeholder="e.g. 5" class="modern-input" min="0">
                        </div>
                    </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Total Allocation (₱)</label>
                        <input type="number" name="total_allocation" required placeholder="e.g. 5000000" class="modern-input" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="modern-label">No. of Plans Received</label>
                        <input type="number" name="no_of_plans_received" required placeholder="e.g. 3" class="modern-input" min="0">
                    </div>
                </div>

                <div>
                    <label class="modern-label">No. of Project Estimate Received</label>
                    <input type="number" name="no_of_project_estimate_received" required placeholder="e.g. 2" class="modern-input" min="0">
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                    <label class="modern-label" style="margin-bottom: 10px; display: block;">Status of Program of Works</label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">NO. of POW Prepared</label>
                            <input type="number" name="pow_received" required placeholder="e.g. 4" class="modern-input" min="0">
                        </div>
                        <div>
                            <label class="modern-label">No. of POW Approved</label>
                            <input type="number" name="pow_approved" required placeholder="e.g. 3" class="modern-input" min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">No. of POW Submitted</label>
                            <input type="number" name="pow_submitted" required placeholder="e.g. 2" class="modern-input" min="0">
                        </div>
                        <div>
                            <label class="modern-label">Ongoing POW Preparation</label>
                            <input type="number" name="ongoing_pow_preparation" required placeholder="e.g. 1" class="modern-input" min="0">
                        </div>
                    </div>

                    <div>
                        <label class="modern-label">POW for Submission</label>
                        <input type="number" name="pow_for_submission" required placeholder="e.g. 1" class="modern-input" min="0">
                    </div>
                </div>

                <div>
                    <label class="modern-label">Remarks</label>
                    <textarea name="remarks" rows="3" class="modern-input" style="resize: none;" placeholder="Additional remarks..." maxlength="2000"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Save Data</button>
                </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="deleteConfirmModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Delete Program of Works Data</h3>
                <p style="font-size: 14px; color: #475569; margin-bottom: 25px;">Are you sure you want to delete this record? This action cannot be undone.</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeDeleteModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                        <button type="submit" class="modern-btn" style="flex: 1; background: #ef4444;">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="editDataModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Edit Program of Works Data</h3>
                
                <form action="{{ route('pao.pow.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">District</label>
                            <select name="district" id="edit-district" required class="modern-input">
                                <option value="">Select District</option>
                                <option value="District 1">District 1</option>
                                <option value="District 2">District 2</option>
                                <option value="District 3">District 3</option>
                                <option value="District 4">District 4</option>
                                <option value="District 5">District 5</option>
                                <option value="District 6">District 6</option>
                            </select>
                        </div>
                        <div>
                            <label class="modern-label">No. of Projects</label>
                            <input type="number" name="no_of_projects" id="edit-no_of_projects" required placeholder="e.g. 5" class="modern-input" min="0">
                        </div>
                    </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Total Allocation (₱)</label>
                        <input type="number" name="total_allocation" id="edit-total_allocation" required placeholder="e.g. 5000000" class="modern-input" min="0" step="0.01">
                    </div>
                    <div>
                        <label class="modern-label">No. of Plans Received</label>
                        <input type="number" name="no_of_plans_received" id="edit-no_of_plans_received" required placeholder="e.g. 3" class="modern-input" min="0">
                    </div>
                </div>

                <div>
                    <label class="modern-label">No. of Project Estimate Received</label>
                    <input type="number" name="no_of_project_estimate_received" id="edit-no_of_project_estimate_received" required placeholder="e.g. 2" class="modern-input" min="0">
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                    <label class="modern-label" style="margin-bottom: 10px; display: block;">Status of Program of Works</label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">NO. of POW Prepared</label>
                            <input type="number" name="pow_received" id="edit-pow_received" required placeholder="e.g. 4" class="modern-input" min="0">
                        </div>
                        <div>
                            <label class="modern-label">No. of POW Approved</label>
                            <input type="number" name="pow_approved" id="edit-pow_approved" required placeholder="e.g. 3" class="modern-input" min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="modern-label">No. of POW Submitted</label>
                            <input type="number" name="pow_submitted" id="edit-pow_submitted" required placeholder="e.g. 2" class="modern-input" min="0">
                        </div>
                        <div>
                            <label class="modern-label">Ongoing POW Preparation</label>
                            <input type="number" name="ongoing_pow_preparation" id="edit-ongoing_pow_preparation" required placeholder="e.g. 1" class="modern-input" min="0">
                        </div>
                    </div>

                    <div>
                        <label class="modern-label">POW for Submission</label>
                        <input type="number" name="pow_for_submission" id="edit-pow_for_submission" required placeholder="e.g. 1" class="modern-input" min="0">
                    </div>
                </div>

                <div>
                    <label class="modern-label">Remarks</label>
                    <textarea name="remarks" id="edit-remarks" rows="3" class="modern-input" style="resize: none;" placeholder="Additional remarks..." maxlength="2000"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeEditModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Update Data</button>
                </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        async function handlePowExport(event, url) {
            event.preventDefault();

            const now = new Date();
            const formattedDate = now.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            const suggestedName = `STATUS OF POW CY ${now.getFullYear()} - as of ${formattedDate}.xlsx`;

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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f4f4f5'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
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
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                padding: 20
                            }
                        }
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

        const deleteBaseUrl = "{{ url('pao_team/pow/delete') }}";

        function openAddModal() {
            document.getElementById('addDataModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addDataModal').classList.remove('active');
        }

        function openDeleteModal(id) {
            document.getElementById('deleteForm').action = deleteBaseUrl + '/' + id;
            document.getElementById('deleteConfirmModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').classList.remove('active');
        }

        function openEditModal(id, district, no_of_projects, total_allocation, no_of_plans_received, no_of_project_estimate_received, pow_received, pow_approved, pow_submitted, ongoing_pow_preparation, pow_for_submission, remarks) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-district').value = district;
            document.getElementById('edit-no_of_projects').value = no_of_projects;
            document.getElementById('edit-total_allocation').value = total_allocation;
            document.getElementById('edit-no_of_plans_received').value = no_of_plans_received;
            document.getElementById('edit-no_of_project_estimate_received').value = no_of_project_estimate_received;
            document.getElementById('edit-pow_received').value = pow_received;
            document.getElementById('edit-pow_approved').value = pow_approved;
            document.getElementById('edit-pow_submitted').value = pow_submitted;
            document.getElementById('edit-ongoing_pow_preparation').value = ongoing_pow_preparation;
            document.getElementById('edit-pow_for_submission').value = pow_for_submission;
            document.getElementById('edit-remarks').value = remarks;
            document.getElementById('editDataModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editDataModal').classList.remove('active');
        }
    </script>
@endsection
