@extends('layouts.app')
@section('title', 'Social And Environmental Team Dashboard')

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

        /* Sleek Table with Actions */
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
            color: #fff;
        }

        .badge-light {
            background: #fda611;
            color: #ffffff;
        }

        .badge-outline {
            border: 1px solid #e4e4e7;
            color: #71717a;
        }

        /* Custom Status Dropdown */
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

        /* Dynamic Visual Calendar */
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

        /* The black circle */
        .day-num.today {
            background: #4fc94d;
        }

        /* Mini Event List Below Calendar */
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



        /* STATUS MODULE ONLY */
        .status-module table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        /* Header */
        .status-module thead th {
            background: #f9fafb;
            font-size: 11px;
            font-weight: 600;
            color: #71717a;
            padding: 10px;
            text-align: center;
            border: none;
        }

        /* Body */
        .status-module tbody td {
            background: #fff;
            padding: 12px;
            font-size: 12px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
        }

        /* Row hover */
        .status-module tbody tr:hover td {
            background: #f9fafb;
        }

        /* First column left align */
        .status-module tbody td:first-child {
            text-align: left;
            font-weight: 600;
        }

        /* Action button */
        .status-action-btn {
            background: #fee2e2;
            color: #991b1b;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        /* Modal UI */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
        }

        .modal-box {
            width: 95%;
            max-width: 1000px;
            background: #fff;
            margin: 40px auto;
            border-radius: 14px;
            padding: 25px;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* CLEAN TABLE UI FIX */
        .custom-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1200px;
            /* prevents squishing */
        }

        .custom-table thead th {
            font-size: 11px;
            color: #71717a;
            font-weight: 600;
            text-align: center;
            padding: 10px 6px;
            border-bottom: 1px solid #e4e4e7;
        }

        .custom-table tbody td {
            padding: 12px 8px;
            font-size: 13px;
            text-align: center;
            border-bottom: 1px solid #f4f4f5;
        }

        .custom-table tbody tr:hover {
            background: #f9fafb;
            transition: 0.2s;
        }

        /* FIX INPUT LOOK */
        .status-select {
            width: 100%;
        }

        /* COLLAPSIBLE COLUMNS */
        .hide-impl th.impl,
        .hide-impl td.impl {
            display: none;
        }

        /* smooth look */
        .custom-table td,
        .custom-table th {
            transition: 0.2s;
        }

        /* 1. CLEAN TABLE UI FIX */
        .custom-table {
            border-collapse: collapse;
            /* Changed to collapse for clean grid borders */
            min-width: 2000px;
            /* Ensures the table is wide enough to prevent squishing */
            background: #fff;
        }

        .custom-table thead th {
            font-size: 11px;
            color: #3f3f46;
            font-weight: 600;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #e4e4e7;
            /* Added full borders */
            background: #f8fafc;
            /* Slight grey background for headers */
            vertical-align: middle;
        }

        .custom-table tbody td {
            padding: 12px 10px;
            font-size: 12px;
            text-align: center;
            border: 1px solid #e4e4e7;
            /* Added full borders */
            vertical-align: middle;
            color: #18181b;
            transition: 0.2s;
        }

        .custom-table tbody tr:hover td {
            background: #f0f9ff;
            /* Subtle blue highlight on hover */
        }

        /* ✅ SPECIFIC COLUMN WIDTHS TO FIX SQUISHING */
        .col-activity {
            min-width: 280px;
            /* Gives the text plenty of room */
            text-align: left !important;
            /* Easier to read long sentences */
            line-height: 1.5;
            white-space: normal;
            /* Allows natural wrapping */
        }

        .col-remarks {
            min-width: 180px;
            white-space: normal;
        }

        .col-amount {
            min-width: 120px;
            font-weight: 600;
        }

        /* 2. RESPONSIVE TABLE WRAPPER */
        .table-responsive-wrapper {
            width: 100%;
            max-width: 100%;
            /* ✅ Prevents the container from breaking out of the card */
            overflow-x: scroll;
            /* ✅ Changed from 'auto' to 'scroll' to FORCE the bar to always show */
            max-height: 600px;
            /* ✅ ADDED: Optional vertical scrolling if you have too many rows */
            overflow-y: auto;
            border-radius: 10px;
            border: 1px solid #e4e4e7;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;

            /* ✅ Firefox Support */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        /* CUSTOM HORIZONTAL & VERTICAL SCROLLBARS (Chrome/Edge/Safari) */
        .table-responsive-wrapper::-webkit-scrollbar {
            height: 12px;
            /* Bottom horizontal scrollbar thickness */
            width: 12px;
            /* Side vertical scrollbar thickness */
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 0 0 10px 10px;
            border-top: 1px solid #e4e4e7;
            /* Visual separation from table */
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f8fafc;
            /* Adds padding inside the track */
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <h1 class="header-title">Social And Environmental Team Dashboard</h1>

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
                <table class="sleek-table">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>

                            @if (auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']))
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

                                @if (auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']))
                                    <td style="text-align: right;">
                                        <form action="{{ route('rpwsis.resolutions.update_status', $res->id) }}"
                                            method="POST">
                                            @csrf
                                            <select name="status" class="status-select" onchange="this.form.submit()">
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
                                <td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']) ? '3' : '2' }}"
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

    {{-- //STATUS --}}
    <div class="ui-card">

        <!-- ACTION BUTTONS -->
        <div class="section-title">
            ACCOMPLISHMENT AS OF FEBRUARY 15, 2025 OF R&P WRSIS
            <div style="display:flex; gap:8px;">
                <button onclick="openModal()" class="status-select"
                    style="background-color: #2563eb; color: white; border-color: #2563eb;">
                    + Add Record
                </button>

                <button onclick="exportCSV()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a;">
                    Export CSV
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive-wrapper">
            <table class="sleek-table custom-table" id="simpleTable">
                <thead>
                    <!-- TOP HEADER -->
                    <tr>
                        <th rowspan="3">Region</th>
                        <th rowspan="3">Batch</th>
                        <th rowspan="3">Allocation</th>
                        <th rowspan="3">NIS</th>
                        <th rowspan="3">Activity Type</th>
                        <th rowspan="3">Remarks</th>
                        <th rowspan="3">Amount</th>

                        <th colspan="12">B. Implementation Stage</th>

                        <th rowspan="3">PHY %</th>
                        <th rowspan="3">FIN %</th>
                        <th rowspan="3">Expenditures</th>

                        <th rowspan="3" style="text-align:right;">Action</th>
                    </tr>

                    <!-- SECOND HEADER -->
                    <tr>
                        <th colspan="8">1. Preparation and Establishment</th>
                        <th colspan="3">2. Conduct of IEC</th>
                        <th colspan="1">3. Monitoring and Evaluation</th>
                    </tr>

                    <!-- THIRD HEADER -->
                    <tr>
                        <th class="impl">POW Formulation</th>
                        <th class="impl">Nursery area/Bunk House/STW</th>
                        <th class="impl">Seedling Production</th>
                        <th class="impl">Procurement </th>
                        <th class="impl">Site Preparation</th>
                        <th class="impl">Vegetative enhancement</th>
                        <th class="impl">Establishment of Wattling</th>
                        <th class="impl">Right of Way/Rent/ Wages of Caretaker/</th>
                        <th class="impl">Conduct of consultative meetings</th>
                        <th class="impl">Distribution of reading materials</th>
                        <th class="impl">Installation of signboards/
                            signages </th>
                        <th class="impl">Supervision and Monitoring of implementations</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($records as $r)
                        <tr>
                            <td>{{ $r->region }}</td>
                            <td>{{ $r->batch }}</td>
                            <td>{{ $r->allocation }}</td>
                            <td>{{ $r->nis }}</td>
                            <td>{{ $r->activity }}</td>
                            <td>{{ $r->remarks }}</td>
                            <td>{{ $r->amount }}</td>

                            <td>{{ $r->c1 }}</td>
                            <td>{{ $r->c2 }}</td>
                            <td>{{ $r->c3 }}</td>
                            <td>{{ $r->c4 }}</td>
                            <td>{{ $r->c5 }}</td>
                            <td>{{ $r->c6 }}</td>
                            <td>{{ $r->c7 }}</td>
                            <td>{{ $r->c8 }}</td>
                            <td>{{ $r->c9 }}</td>
                            <td>{{ $r->c10 }}</td>
                            <td>{{ $r->c11 }}</td>
                            <td>{{ $r->c12 }}</td>

                            <td>{{ $r->phy }}</td>
                            <td>{{ $r->fin }}</td>
                            <td>{{ $r->exp }}</td>

                            <td>
                                <button onclick="deleteAccomplishment({{ $r->id }}, this)"
                                    class="status-select">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- MODAL -->
        <div id="statusModal"
            style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.4);
    z-index:999;
">

            <div
                style="
        width:90%;
        max-width:900px;
        background:#fff;
        margin:40px auto;
        border-radius:12px;
        padding:20px;
        box-shadow:0 10px 30px rgba(0,0,0,0.2);
        font-family:'Poppins', sans-serif;
        max-height:90vh;
        overflow:auto;
    ">

                <!-- HEADER -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0; color:#0c4d05;">Add Accomplishment</h3>
                    <button onclick="closeModal()"
                        style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;"
                        onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#a1a1aa'">
                        &times;
                    </button>
                </div>

                <!-- FORM -->
                <div style="display:flex; flex-direction:column; gap:15px;">

                    <!-- PROJECT INFO -->
                    <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                        <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Project Information
                        </p>

                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                            <input id="region" placeholder="Region" class="status-select">
                            <input id="batch" placeholder="Batch" class="status-select">
                            <input id="allocation" placeholder="Allocation" class="status-select">
                            <input id="nis" placeholder="NIS" class="status-select">
                            <input id="activity" placeholder="Activity Type" class="status-select">
                            <input id="remarks" placeholder="Remarks" class="status-select">
                            <input id="amount" placeholder="Amount" class="status-select">
                        </div>
                    </div>

                    <!-- IMPLEMENTATION -->
                    <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                        <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Implementation Stage
                        </p>

                        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                            <input id="c1" placeholder="POW" class="status-select">
                            <input id="c2" placeholder="Nursery" class="status-select">
                            <input id="c3" placeholder="Seedling" class="status-select">
                            <input id="c4" placeholder="Procurement" class="status-select">
                            <input id="c5" placeholder="Site Prep" class="status-select">
                            <input id="c6" placeholder="Vegetative" class="status-select">
                            <input id="c7" placeholder="Wattling" class="status-select">
                            <input id="c8" placeholder="Right of Way" class="status-select">
                            <input id="c9" placeholder="Consultative" class="status-select">
                            <input id="c10" placeholder="Distribution" class="status-select">
                            <input id="c11" placeholder="Signages" class="status-select">
                            <input id="c12" placeholder="Monitoring" class="status-select">
                        </div>
                    </div>

                    <!-- METRICS -->
                    <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                        <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Project Metrics</p>

                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                            <input id="phy" placeholder="PHY %" class="status-select">
                            <input id="fin" placeholder="FIN %" class="status-select">
                            <input id="exp" placeholder="Expenditures" class="status-select">
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="closeModal()" class="status-select">Cancel</button>
                        <button onclick="saveRecord()" class="status-select" style="background:#0c4d05; color:white;">
                            Save Record
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function addRow() {

            const inputs = [
                'region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount',
                'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
                'phy', 'fin', 'exp' // ✅ include manual inputs
            ];

            let vals = inputs.map(id => {
                const el = document.getElementById(id);
                return el ? el.value : '';
            });

            if (!vals[0]) return alert("Region is required");

            const row = `
        <tr>
            ${vals.map((v, i) => {
                return `<td class="${(i >= 7 && i <= 18) ? 'impl' : ''}">${v || '-'}</td>`;
            }).join('')}
            <td style="text-align:right;">
                <button onclick="deleteRow(this)" class="status-select">Delete</button>
            </td>
        </tr>
        `;

            document.getElementById('tableBody').innerHTML += row;

            // ✅ OPTIONAL: clear inputs after adding
            inputs.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        }

        // ✅ NEW DELETE FUNCTION
        function deleteAccomplishment(id, btn) {
            if (!confirm("Are you sure you want to delete this record?")) return;

            fetch(`/rpwsis_team/accomplishments/${id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table visually
                        btn.closest('tr').remove();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while deleting.");
                });
        }

        function exportCSV() {
            let csv = "";

            const headers = document.querySelectorAll("#simpleTable thead tr:last-child th");
            let headerRow = [];

            headers.forEach(th => {
                headerRow.push(th.innerText.trim());
            });

            headerRow.push("PHY %", "FIN %", "Expenditures");

            csv += headerRow.join(",") + "\n";

            const rows = document.querySelectorAll("#simpleTable tbody tr");

            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];

                cols.forEach((td, i) => {
                    if (i !== cols.length - 1) {
                        rowData.push(td.innerText.trim());
                    }
                });

                csv += rowData.join(",") + "\n";
            });

            const blob = new Blob([csv], {
                type: "text/csv"
            });
            const url = URL.createObjectURL(blob);

            const a = document.createElement("a");
            a.href = url;
            a.download = "status_22_columns.csv";
            a.click();

            URL.revokeObjectURL(url);
        }

        // function toggleImplementation() {
        //     document.getElementById('simpleTable').classList.toggle('hide-impl');
        // }

        function openModal() {
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // click outside to close
        window.onclick = function(e) {
            const modal = document.getElementById('statusModal');
            if (e.target === modal) {
                modal.style.display = "none";
            }
        }


        function saveRecord() {

            const fields = [
                'region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount',
                'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
                'phy', 'fin', 'exp'
            ];

            let data = {};

            fields.forEach(id => {
                data[id] = document.getElementById(id).value;
            });

            // ✅ FIX: Match the prefix group in your web.php
            fetch('/rpwsis_team/accomplishments/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(res => {

                    let row = `<tr>
            <td>${res.region || '-'}</td>
            <td>${res.batch || '-'}</td>
            <td>${res.allocation || '-'}</td>
            <td>${res.nis || '-'}</td>
            <td>${res.activity || '-'}</td>
            <td>${res.remarks || '-'}</td>
            <td>${res.amount || '-'}</td>

            ${[1,2,3,4,5,6,7,8,9,10,11,12].map(i => `<td>${res['c'+i] || '-'}</td>`).join('')}

            <td>${res.phy || '-'}</td>
            <td>${res.fin || '-'}</td>
            <td>${res.exp || '-'}</td>

           <td><button onclick="deleteAccomplishment(${res.id}, this)" class="status-select">Delete</button></td>
        </tr>`;

                    document.getElementById('tableBody').innerHTML += row;

                    closeModal();
                });
        }
    </script>

    {{-- //end of status --}}
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
    </script>
@endsection
