@extends('layouts.app')
@section('title', 'Social And Environmental Team Dashboard')

@section('content')
    @php
        $canManageRpwsis = auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']);
    @endphp

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
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s;
        }

        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modern-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            transition: 0.2s;
            margin-bottom: 15px;
        }

        .modern-input:focus {
            border-color: #0c4d05;
            box-shadow: 0 0 0 3px rgba(12, 77, 5, 0.1);
        }

        .modern-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modern-btn {
            width: 100%;
            padding: 10px;
            background: #0c4d05;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .modern-btn:hover {
            background: #083803;
        }

        .modern-btn-outline {
            background: white;
            border: 1px solid #cbd5e1;
            color: #475569;
        }

        .modern-btn-outline:hover {
            background: #f1f5f9;
            color: #1e293b;
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
            min-width: 2000px;
            width: 100%;
            table-layout: fixed;
            background: #fff;
        }

        .custom-table thead th {
            text-align: left;
            padding: 12px 15px;
            color: #a0aec0;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            white-space: normal;
            vertical-align: middle;
            line-height: 1.4;
        }

        .custom-table tbody td {
            padding: 15px;
            border: 1px solid #f1f5f9;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            vertical-align: middle;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            transition: 0.2s;
        }

        .custom-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* //new april 2021 */

        /* ✅ FIRST TABLE SPECIFIC COLUMN WIDTHS */
        .col-activity {
            min-width: 280px;
            text-align: left !important;
            line-height: 1.5;
            white-space: normal;
        }

        .col-remarks {
            min-width: 180px;
            white-space: normal;
        }

        .col-amount {
            min-width: 120px;
            font-weight: 600;
        }

        /* ✅ NEW FEATURE: SUMMARY TABLE SPECIFIC COLUMN WIDTHS */
        #summaryTable {
            min-width: 2600px;
            /* Wider to accommodate 18 distinct columns */
        }

        #simpleTable {
            min-width: 2200px;
            table-layout: fixed;
        }

        #simpleTable thead th {
            min-width: 120px;
            max-width: 140px;
            white-space: normal;
            line-height: 1.4;
            word-break: break-word;
        }

        #simpleTable tbody td {
            max-width: 140px;
            vertical-align: top;
        }

        #simpleTable .status-compact-cell {
            white-space: normal;
            text-align: left;
            color: #64748b;
            line-height: 1.5;
        }

        #summaryTable .col-standard {
            min-width: 120px;
        }

        #summaryTable .col-medium {
            min-width: 160px;
            white-space: normal;
            line-height: 1.4;
        }

        #summaryTable .col-wide {
            min-width: 260px;
            text-align: left !important;
            white-space: normal;
            line-height: 1.6;
        }

        #summaryTable .col-expandable {
            min-width: 190px;
            max-width: 220px;
            white-space: normal;
            text-align: left !important;
        }

        .expandable-cell {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .expandable-preview,
        .expandable-full {
            line-height: 1.5;
            word-break: break-word;
            color: #64748b;
        }

        .expandable-full {
            display: none;
        }

        .expandable-cell.is-expanded .expandable-preview {
            display: none;
        }

        .expandable-cell.is-expanded .expandable-full {
            display: block;
        }

        .expand-toggle {
            border: none;
            background: transparent;
            color: #0c4d05;
            font-size: 11px;
            font-weight: 600;
            padding: 0;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .expand-toggle:hover {
            color: #083a04;
        }

        #summaryTable .col-action {
            min-width: 80px;
            text-align: right;
        }

        /* COLLAPSIBLE COLUMNS */
        .hide-impl th.impl,
        .hide-impl td.impl {
            display: none;
        }

        /* 2. RESPONSIVE TABLE WRAPPER */
        .table-responsive-wrapper {
            width: 100%;
            max-width: 100%;
            display: block;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
            border-radius: 0;
            border: none;
            padding-bottom: 15px;
        }

        /* CUSTOM HORIZONTAL & VERTICAL SCROLLBARS */
        .table-responsive-wrapper::-webkit-scrollbar {
            height: 12px;
            width: 12px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .delete-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .delete-modal-overlay.active {
            display: flex;
            animation: fadeDeleteModalIn 0.2s ease;
        }

        .delete-modal-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        }

        .delete-modal-title {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #1e293b;
        }

        .delete-modal-text {
            font-size: 14px;
            color: #475569;
            margin-bottom: 25px;
        }

        .delete-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .delete-modal-btn {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
            border: none;
        }

        .delete-modal-btn.cancel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
        }

        .delete-modal-btn.cancel:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .delete-modal-btn.confirm {
            background: #ef4444;
            color: #ffffff;
        }

        .delete-modal-btn.confirm:hover {
            background: #dc2626;
        }

        @keyframes fadeDeleteModalIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                <table class="sleek-table" id="activeProjectsContainer">
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
            <div style="display: flex; gap: 10px;">
                @if ($canManageRpwsis)
                    <button onclick="openModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Record
                    </button>
                @endif

                <button onclick="exportCSV()" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
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

                        @if ($canManageRpwsis)
                            <th rowspan="3" style="text-align:right;">Action</th>
                        @endif
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
                        @php
                            $statusValues = [
                                $r->region,
                                $r->batch,
                                $r->allocation,
                                $r->nis,
                                $r->activity,
                                $r->remarks,
                                $r->amount,
                                $r->c1,
                                $r->c2,
                                $r->c3,
                                $r->c4,
                                $r->c5,
                                $r->c6,
                                $r->c7,
                                $r->c8,
                                $r->c9,
                                $r->c10,
                                $r->c11,
                                $r->c12,
                                $r->phy,
                                $r->fin,
                                $r->exp,
                            ];
                        @endphp
                        <tr>
                            @foreach ($statusValues as $index => $value)
                                <td class="{{ $index >= 7 && $index <= 18 ? 'impl ' : '' }}status-compact-cell"
                                    data-export-value="{{ $value }}">
                                    {!! !empty($value)
                                        ? '<div class="expandable-cell' .
                                            (mb_strlen((string) $value) <= 28 ? ' is-expanded' : '') .
                                            '">' .
                                            '<div class="expandable-preview">' .
                                            e(\Illuminate\Support\Str::limit(preg_replace("/\s+/", ' ', (string) $value), 28)) .
                                            '</div>' .
                                            '<div class="expandable-full">' .
                                            nl2br(e($value)) .
                                            '</div>' .
                                            (mb_strlen((string) $value) > 28
                                                ? '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>'
                                                : '') .
                                            '</div>'
                                        : '-' !!}
                                </td>
                            @endforeach
                            @if ($canManageRpwsis)
                                <td>
                                    <button onclick="openDeleteModal('accomplishment', {{ $r->id }}, this)"
                                        class="status-select">Delete</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($canManageRpwsis)
            <!-- MODAL -->
            <div id="statusModal" class="modal-overlay">
                <div class="modal-box" style="max-width: 900px;">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add Accomplishment</h3>
                <div style="display:flex; flex-direction:column; gap:15px;">

                    <!-- PROJECT INFO -->
                    <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                        <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Project Information</label>

                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                            <input id="region" placeholder="Region" class="modern-input" maxlength="100" required>
                            <input id="batch" placeholder="Batch" class="modern-input" maxlength="100">
                            <input id="allocation" placeholder="Allocation" class="modern-input" maxlength="255">
                            <input id="nis" placeholder="NIS" class="modern-input" maxlength="255">
                            <input id="activity" placeholder="Activity Type" class="modern-input" maxlength="255" required>
                            <input id="remarks" placeholder="Remarks" class="modern-input" maxlength="1000">
                            <input id="amount" placeholder="Amount" class="modern-input" type="number" min="0" step="0.01">
                        </div>
                    </div>

                    <!-- IMPLEMENTATION -->
                    <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                        <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Implementation Stage</label>

                        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                            <input id="c1" placeholder="POW" class="modern-input" maxlength="255">
                            <input id="c2" placeholder="Nursery" class="modern-input" maxlength="255">
                            <input id="c3" placeholder="Seedling" class="modern-input" maxlength="255">
                            <input id="c4" placeholder="Procurement" class="modern-input" maxlength="255">
                            <input id="c5" placeholder="Site Prep" class="modern-input" maxlength="255">
                            <input id="c6" placeholder="Vegetative" class="modern-input" maxlength="255">
                            <input id="c7" placeholder="Wattling" class="modern-input" maxlength="255">
                            <input id="c8" placeholder="Right of Way" class="modern-input" maxlength="255">
                            <input id="c9" placeholder="Consultative" class="modern-input" maxlength="255">
                            <input id="c10" placeholder="Distribution" class="modern-input" maxlength="255">
                            <input id="c11" placeholder="Signages" class="modern-input" maxlength="255">
                            <input id="c12" placeholder="Monitoring" class="modern-input" maxlength="255">
                        </div>
                    </div>

                    <!-- METRICS -->
                    <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                        <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Project Metrics</label>

                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                            <input id="phy" placeholder="PHY %" class="modern-input" type="number" min="0" max="100" step="0.01">
                            <input id="fin" placeholder="FIN %" class="modern-input" type="number" min="0" max="100" step="0.01">
                            <input id="exp" placeholder="Expenditures" class="modern-input" type="number" min="0" step="0.01">
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div style="display:flex; gap:10px; margin-top: 10px;">
                        <button onclick="closeModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                        <button onclick="saveRecord(this)" class="modern-btn" style="flex: 1;">
                            Save Record
                        </button>
                    </div>

                </div>
            </div>
            </div>
        @endif
    </div>

    <script>
        function validateRequiredFields(fieldIds) {
            const emptyFields = [];

            fieldIds.forEach(id => {
                const field = document.getElementById(id);
                if (!field) {
                    return;
                }

                const value = String(field.value ?? '').trim();
                if (!value) {
                    emptyFields.push(field);
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.12)';
                } else {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }
            });

            if (emptyFields.length > 0) {
                emptyFields[0].focus();
                alert('Please complete all required fields before saving.');
                return false;
            }

            return true;
        }

        function renderStatusExpandableCell(value, extraClass = '') {
            const text = String(value ?? '').trim();
            const className = extraClass ? ` class="${extraClass}"` : '';

            if (!text) {
                return `<td${className}>-</td>`;
            }

            const normalizedText = text.replace(/\s+/g, ' ').trim();
            const previewLimit = 28;
            const preview = normalizedText.length > previewLimit ?
                `${normalizedText.slice(0, previewLimit).trimEnd()}...` :
                normalizedText;
            const escapedPreview = escapeSummaryHtml(preview);
            const escapedFull = escapeSummaryHtml(text).replace(/\n/g, '<br>');
            const expandedClass = normalizedText.length <= previewLimit ? ' is-expanded' : '';
            const toggleButton = normalizedText.length > previewLimit ?
                '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>' :
                '';
            const exportValue = escapeSummaryHtml(text);

            return `<td${className} data-export-value="${exportValue}">
                <div class="expandable-cell${expandedClass}">
                    <div class="expandable-preview">${escapedPreview}</div>
                    <div class="expandable-full">${escapedFull}</div>
                    ${toggleButton}
                </div>
            </td>`;
        }

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
                const className = `${(i >= 7 && i <= 18) ? 'impl ' : ''}status-compact-cell`.trim();
                return renderStatusExpandableCell(v, className);
            }).join('')}
            @if ($canManageRpwsis)
                <td style="text-align:right;">
                    <button onclick="deleteRow(this)" class="status-select">Delete</button>
                </td>
            @endif
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
        function deleteAccomplishment(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('accomplishment', id, btn);
                return;
            }

            btn.disabled = true;
            btn.classList.add('is-loading');
            if (typeof showAppLoader === 'function') {
                showAppLoader('Deleting record...');
            }

            fetch(`/rpwsis_team/accomplishments/${id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(async res => {
                    const payload = await res.json();

                    if (!res.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete the record.');
                    }

                    return payload;
                })
                .then(data => {
                    btn.closest('tr').remove();
                        closeDeleteModal();

                    // Keep delete silent after a successful refresh.
                })
                .catch(error => {
                    console.error("Error:", error);
                    if (typeof showLiveAlert === 'function') {
                        showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert(error.message || 'An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                    if (typeof hideAppLoader === 'function') {
                        hideAppLoader();
                    }
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
                        rowData.push(td.dataset.exportValue ?? td.innerText.trim());
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
            document.getElementById('statusModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('statusModal').classList.remove('active');
        }

        // click outside to close
        window.onclick = function(e) {
            const modal = document.getElementById('statusModal');
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        }


        function saveRecord() {

            const fields = [
                'region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount',
                'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
                'phy', 'fin', 'exp'
            ];

            if (!validateRequiredFields(fields)) {
                return;
            }

            let data = {};
            const saveButton = document.querySelector('#statusModal button[onclick="saveRecord()"]');

            const requiredFields = ['region', 'activity'];
            for (const id of requiredFields) {
                const input = document.getElementById(id);
                if (!input.checkValidity()) {
                    input.reportValidity();
                    input.focus();
                    return;
                }
            }

            fields.forEach(id => {
                data[id] = document.getElementById(id).value.trim();
            });

            if (saveButton) {
                saveButton.disabled = true;
                saveButton.classList.add('is-loading');
            }

            if (typeof showAppLoader === 'function') {
                showAppLoader('Saving record...');
            }

            fetch('/rpwsis_team/accomplishments/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json();

                    if (!res.ok) {
                        const message = payload.errors
                            ? Object.values(payload.errors).flat().join(' ')
                            : (payload.message || 'Unable to save record.');
                        throw new Error(message);
                    }

                    return payload;
                })
                .then(payload => {
                    const res = payload.record;

                    let row = `<tr>
            ${[
                res.region, res.batch, res.allocation, res.nis, res.activity, res.remarks, res.amount,
                res.c1, res.c2, res.c3, res.c4, res.c5, res.c6, res.c7, res.c8, res.c9, res.c10, res.c11, res.c12,
                res.phy, res.fin, res.exp
            ].map((value, index) => {
                const className = `${(index >= 7 && index <= 18) ? 'impl ' : ''}status-compact-cell`.trim();
                return renderStatusExpandableCell(value, className);
            }).join('')}

            @if ($canManageRpwsis)
                <td><button onclick="deleteAccomplishment(${res.id}, this)" class="status-select">Delete</button></td>
            @endif
        </tr>`;

                    document.getElementById('tableBody').innerHTML += row;
                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });
                    closeModal();
                });
        }
    </script>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // NEW FEATURE: REHABILITATION AND PROTECTION SUMMARY TABLE                           --}}
    {{-- // -------------------------------------------------------------------------------- --}}

    <div class="ui-card" style="margin-top: 2rem;">

        <div class="section-title">
            REHABILITATION AND PROTECTION OF WATER RESOURCES SUPPORTING IRRIGATION SYSTEM (R&P WRSIS)
            <div style="font-size: 14px; font-weight: normal; margin-top: 4px; opacity: 0.9;">
                Summary of Accomplishment
            </div>

            <div style="display: flex; gap: 10px; margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openSummaryModal()" style="background: #0c4d05; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Record
                    </button>
                @endif

                <button onclick="exportSummaryCSV()" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="sleek-table custom-table" id="summaryTable">
                <thead>
                    <tr>
                        <th>Region</th>
                        <th>Province</th>
                        <th>Municipality</th>
                        <th>Barangay</th>
                        <th>Type of Plantation</th>
                        <th>Year Established</th>
                        <th>Target Area</th>
                        <th>Area Planted</th>
                        <th>Species and Number of Seedlings Planted</th>
                        <th class="col-expandable">Spacing</th>
                        <th>1st Year Maintenance and Protection</th>
                        <th>Replanting Target Area</th>
                        <th>Replanting Actual Area</th>
                        <th>Mortality Rate</th>
                        <th class="col-expandable">Species Replanted</th>
                        <th>Name of NIS</th>
                        <th>Remarks</th>
                        @if ($canManageRpwsis)
                            <th style="text-align:right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="summaryTableBody">
                    {{-- Make sure to pass $summaryRecords from your Controller --}}
                    @foreach ($summaryRecords ?? [] as $row)
                        <tr>
                            <td>{{ $row->region }}</td>
                            <td>{{ $row->province }}</td>
                            <td>{{ $row->municipality }}</td>
                            <td>{{ $row->barangay }}</td>
                            <td>{{ $row->plantation_type }}</td>
                            <td>{{ $row->year_established }}</td>
                            <td>{{ $row->target_area_1 }}</td>
                            <td>{{ $row->area_planted }}</td>
                            <td>{!! nl2br(e($row->species_planted)) !!}</td>
                            <td class="col-expandable" data-export-value="{{ $row->spacing }}">
                                {!! !empty($row->spacing)
                                    ? '<div class="expandable-cell' .
                                        (mb_strlen((string) $row->spacing) <= 45 ? ' is-expanded' : '') .
                                        '">' .
                                        '<div class="expandable-preview">' .
                                        e(\Illuminate\Support\Str::limit(preg_replace("/\s+/", ' ', (string) $row->spacing), 45)) .
                                        '</div>' .
                                        '<div class="expandable-full">' .
                                        nl2br(e($row->spacing)) .
                                        '</div>' .
                                        (mb_strlen((string) $row->spacing) > 45
                                            ? '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>'
                                            : '') .
                                        '</div>'
                                    : '-' !!}
                            </td>
                            <td>{{ $row->maintenance }}</td>
                            <td>{{ $row->target_area_2 }}</td>
                            <td>{{ $row->actual_area }}</td>
                            <td>{{ $row->mortality_rate }}</td>
                            <td class="col-expandable" data-export-value="{{ $row->species_replanted }}">
                                {!! !empty($row->species_replanted)
                                    ? '<div class="expandable-cell' .
                                        (mb_strlen((string) $row->species_replanted) <= 60 ? ' is-expanded' : '') .
                                        '">' .
                                        '<div class="expandable-preview">' .
                                        e(\Illuminate\Support\Str::limit(preg_replace("/\s+/", ' ', (string) $row->species_replanted), 60)) .
                                        '</div>' .
                                        '<div class="expandable-full">' .
                                        nl2br(e($row->species_replanted)) .
                                        '</div>' .
                                        (mb_strlen((string) $row->species_replanted) > 60
                                            ? '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>'
                                            : '') .
                                        '</div>'
                                    : '-' !!}
                            </td>
                            <td>{{ $row->nis_name }}</td>
                            <td>{{ $row->remarks }}</td>
                            @if ($canManageRpwsis)
                                <td style="text-align:right;">
                                    <button onclick="openDeleteModal('summary', {{ $row->id }}, this)"
                                        class="status-select">Delete</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="summaryModal" class="modal-overlay">
                <div class="modal-box" style="max-width: 900px;">
                    <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add Summary Record</h3>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                            <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Location Details</label>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="sum_region" placeholder="Region" class="modern-input">
                                <input id="sum_province" placeholder="Province" class="modern-input">
                                <input id="sum_municipality" placeholder="Municipality" class="modern-input">
                                <input id="sum_barangay" placeholder="Barangay" class="modern-input">
                            </div>
                        </div>

                        <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                            <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Plantation Info</label>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="sum_type" placeholder="Type of Plantation" class="modern-input">
                                <input id="sum_year" placeholder="Year Established" class="modern-input">
                                <input id="sum_target_1" placeholder="Target Area" class="modern-input">
                                <input id="sum_area_planted" placeholder="Area Planted" class="modern-input">
                                <input id="sum_spacing" placeholder="Spacing" class="modern-input">
                                <input id="sum_maintenance" placeholder="1st Year M&P" class="modern-input">
                                <textarea id="sum_species" placeholder="Species & Number Planted (Use Enter for new lines)" class="modern-input"
                                    style="grid-column: span 3; height: 60px; resize: none;"></textarea>
                            </div>
                        </div>

                        <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                            <label class="modern-label" style="color: #1e293b; font-size: 12px; margin-bottom: 10px;">Replanting Status & Extras</label>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="sum_target_2" placeholder="Replanting Target Area" class="modern-input">
                                <input id="sum_actual" placeholder="Replanting Actual Area" class="modern-input">
                                <input id="sum_mortality" placeholder="Mortality Rate" class="modern-input">
                                <input id="sum_nis" placeholder="Name of NIS" class="modern-input">
                                <input id="sum_remarks" placeholder="Remarks" class="modern-input">
                                <div style="grid-column: span 1;"></div>
                                <textarea id="sum_replanted" placeholder="Species Replanted (Use Enter for new lines)" class="modern-input"
                                    style="grid-column: span 3; height: 60px; resize: none;"></textarea>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; margin-top: 10px;">
                            <button onclick="closeSummaryModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                            <button onclick="saveSummaryRecord(this)" class="modern-btn" style="flex: 1;">
                                Save Record
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($canManageRpwsis)
            <div class="delete-modal-overlay" id="deleteConfirmModal">
                <div class="delete-modal-box">
                    <h3 class="delete-modal-title" id="deleteModalTitle">Delete Record</h3>
                    <p class="delete-modal-text" id="deleteModalMessage">Are you sure you want to delete this record? This action cannot be undone.</p>
                    <div class="delete-modal-actions">
                        <button type="button" onclick="closeDeleteModal()" class="delete-modal-btn cancel">Cancel</button>
                        <button type="button" id="confirmDeleteBtn" class="delete-modal-btn confirm">Delete</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function escapeSummaryHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [char];
            });
        }

        function renderExpandableSummaryCell(value, previewLength) {
            const text = String(value ?? '').trim();

            if (!text) {
                return '-';
            }

            const normalizedText = text.replace(/\s+/g, ' ').trim();
            const preview = normalizedText.length > previewLength ?
                `${normalizedText.slice(0, previewLength).trimEnd()}...` :
                normalizedText;
            const escapedPreview = escapeSummaryHtml(preview);
            const escapedFull = escapeSummaryHtml(text).replace(/\n/g, '<br>');
            const expandedClass = normalizedText.length <= previewLength ? ' is-expanded' : '';
            const toggleButton = normalizedText.length > previewLength ?
                '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>' :
                '';

            return `
                <div class="expandable-cell${expandedClass}">
                    <div class="expandable-preview">${escapedPreview}</div>
                    <div class="expandable-full">${escapedFull}</div>
                    ${toggleButton}
                </div>
            `;
        }

        function toggleSummaryCell(button) {
            const container = button.closest('.expandable-cell');
            const isExpanded = container.classList.toggle('is-expanded');
            button.textContent = isExpanded ? 'Show less' : 'Show more';
        }

        function openSummaryModal() {
            document.getElementById('summaryModal').classList.add('active');
        }

        function closeSummaryModal() {
            document.getElementById('summaryModal').classList.remove('active');
        }

        function saveSummaryRecord() {
            const fields = [
                'sum_region', 'sum_province', 'sum_municipality', 'sum_barangay',
                'sum_type', 'sum_year', 'sum_target_1', 'sum_area_planted',
                'sum_species', 'sum_spacing', 'sum_maintenance', 'sum_target_2',
                'sum_actual', 'sum_mortality', 'sum_replanted', 'sum_nis', 'sum_remarks'
            ];

            if (!validateRequiredFields(fields)) {
                return;
            }

            let data = {};
            fields.forEach(id => {
                data[id] = document.getElementById(id).value.trim();
            });

            // Ensure this matches a route in your web.php (e.g., Route::post('/rpwsis_team/summary/store', ...))
            fetch('/rpwsis_team/summary/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(res => {
                    // Using replace to convert newlines to <br> for HTML display
                    const formatText = (text) => text ? text.replace(/\n/g, '<br>') : '-';

                    let row = `<tr>
                <td>${res.region || '-'}</td>
                <td>${res.province || '-'}</td>
                <td>${res.municipality || '-'}</td>
                <td>${res.barangay || '-'}</td>
                <td>${res.plantation_type || '-'}</td>
                <td>${res.year_established || '-'}</td>
                <td>${res.target_area_1 || '-'}</td>
                <td>${res.area_planted || '-'}</td>
                <td>${formatText(res.species_planted)}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.spacing || '')}">${renderExpandableSummaryCell(res.spacing, 45)}</td>
                <td>${res.maintenance || '-'}</td>
                <td>${res.target_area_2 || '-'}</td>
                <td>${res.actual_area || '-'}</td>
                <td>${res.mortality_rate || '-'}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.species_replanted || '')}">${renderExpandableSummaryCell(res.species_replanted, 60)}</td>
                <td>${res.nis_name || '-'}</td>
                <td>${res.remarks || '-'}</td>
                @if ($canManageRpwsis)
                    <td style="text-align:right;">
                        <button onclick="deleteSummaryRecord(${res.id}, this)" class="status-select">Delete</button>
                    </td>
                @endif
            </tr>`;

                    document.getElementById('summaryTableBody').innerHTML += row;
                    closeSummaryModal();

                    // Clear inputs
                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        field.value = '';
                        field.style.borderColor = '';
                        field.style.boxShadow = '';
                    });
                })
                .catch(error => {
                    console.error("Error saving summary record:", error);
                    alert(
                        "Ensure you have created the backend Route and Controller method for /rpwsis_team/summary/store"
                    );
                });
        }

        function deleteSummaryRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('summary', id, btn);
                return;
            }

            // Ensure this matches a route in your web.php
            fetch(`/rpwsis_team/summary/${id}/delete`, {
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
                        btn.closest('tr').remove();
                        closeDeleteModal();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while deleting. Have you set up the delete Route?");
                });
        }

        function exportSummaryCSV() {
            let csv = "";

            // Update CSV to match single-row header
            csv +=
                "Region,Province,Municipality,Barangay,Type of Plantation,Year Established,Target Area,Area Planted,Species and Number Planted,Spacing,1st Year Maintenance,Replanting Target Area,Replanting Actual Area,Mortality Rate,Species Replanted,Name of NIS,Remarks\n";

            // Extract Data
            const rows = document.querySelectorAll("#summaryTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];

                cols.forEach((td, i) => {
                    // Ignore the Action button column (last column)
                    if (i !== cols.length - 1) {
                        // Wrap text in quotes to prevent CSV breaking on newlines or commas
                        let text = (td.dataset.exportValue ?? td.innerText.trim()).replace(/"/g, '""');
                        rowData.push(`"${text}"`);
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
            a.download = "summary_of_accomplishment.csv";
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>

    {{-- //end of status --}}
    <script>
        function setButtonLoading(button, isLoading, loadingText = 'Saving...') {
            if (!button) return;

            if (!button.dataset.originalText) {
                button.dataset.originalText = button.textContent.trim();
            }

            button.disabled = isLoading;
            button.style.opacity = isLoading ? '0.7' : '1';
            button.style.cursor = isLoading ? 'not-allowed' : 'pointer';
            button.textContent = isLoading ? loadingText : button.dataset.originalText;
        }

        let pendingDelete = {
            type: null,
            id: null,
            button: null
        };

        function openModal() {
            const modal = document.getElementById('statusModal');
            if (modal) modal.classList.add('active');
        }

        function closeModal() {
            const modal = document.getElementById('statusModal');
            if (modal) modal.classList.remove('active');
        }

        function openSummaryModal() {
            const modal = document.getElementById('summaryModal');
            if (modal) modal.classList.add('active');
        }

        function closeSummaryModal() {
            const modal = document.getElementById('summaryModal');
            if (modal) modal.classList.remove('active');
        }

        function openDeleteModal(type, id, button) {
            const modal = document.getElementById('deleteConfirmModal');
            const title = document.getElementById('deleteModalTitle');
            const message = document.getElementById('deleteModalMessage');

            if (!modal) return;

            pendingDelete = {
                type,
                id,
                button
            };

            const isSummary = type === 'summary';
            if (title) {
                title.textContent = isSummary ? 'Delete Summary Record' : 'Delete Accomplishment Record';
            }
            if (message) {
                message.textContent = isSummary ?
                    'Are you sure you want to delete this summary record? This action cannot be undone.' :
                    'Are you sure you want to delete this record? This action cannot be undone.';
            }

            modal.classList.add('active');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.classList.remove('active');
            }

            pendingDelete = {
                type: null,
                id: null,
                button: null
            };
        }

        function performDelete() {
            if (!pendingDelete.type || !pendingDelete.id || !pendingDelete.button) {
                closeDeleteModal();
                return;
            }

            if (pendingDelete.type === 'summary') {
                deleteSummaryRecord(pendingDelete.id, pendingDelete.button, true);
                return;
            }

            deleteAccomplishment(pendingDelete.id, pendingDelete.button, true);
        }

        document.addEventListener('click', function(e) {
            const statusModal = document.getElementById('statusModal');
            const summaryModal = document.getElementById('summaryModal');
            const deleteModal = document.getElementById('deleteConfirmModal');

            if (statusModal && e.target === statusModal) {
                closeModal();
            }

            if (summaryModal && e.target === summaryModal) {
                closeSummaryModal();
            }

             if (deleteModal && e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', performDelete);
            }
        });

        function saveRecord(button = null) {
            const fields = [
                'region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount',
                'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
                'phy', 'fin', 'exp'
            ];

            if (!validateRequiredFields(fields)) {
                return;
            }

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            setButtonLoading(button, true);

            fetch('/rpwsis_team/accomplishments/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json().catch(() => null);
                    if (!res.ok || !payload) {
                        throw new Error(payload?.message || 'Unable to save the record right now.');
                    }
                    return payload;
                })
                .then(res => {
                    const record = res.record ?? res;
                    const row = `<tr>
            ${[
                record.region, record.batch, record.allocation, record.nis, record.activity, record.remarks, record.amount,
                record.c1, record.c2, record.c3, record.c4, record.c5, record.c6, record.c7, record.c8, record.c9, record.c10, record.c11, record.c12,
                record.phy, record.fin, record.exp
            ].map((value, index) => {
                const className = `${(index >= 7 && index <= 18) ? 'impl ' : ''}status-compact-cell`.trim();
                return renderStatusExpandableCell(value, className);
            }).join('')}
            @if ($canManageRpwsis)
                <td><button onclick="openDeleteModal('accomplishment', ${record.id}, this)" class="status-select">Delete</button></td>
            @endif
        </tr>`;

                    document.getElementById('tableBody').insertAdjacentHTML('beforeend', row);

                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });

                    closeModal();
                })
                .catch(error => {
                    console.error('Error saving accomplishment:', error);
                    alert(error.message || 'Unable to save the record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }

        function saveSummaryRecord(button = null) {
            const fields = [
                'sum_region', 'sum_province', 'sum_municipality', 'sum_barangay',
                'sum_type', 'sum_year', 'sum_target_1', 'sum_area_planted',
                'sum_species', 'sum_spacing', 'sum_maintenance', 'sum_target_2',
                'sum_actual', 'sum_mortality', 'sum_replanted', 'sum_nis', 'sum_remarks'
            ];

            if (!validateRequiredFields(fields)) {
                return;
            }

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            setButtonLoading(button, true);

            fetch('/rpwsis_team/summary/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json().catch(() => null);
                    if (!res.ok || !payload) {
                        throw new Error(payload?.message || 'Unable to save the summary record right now.');
                    }
                    return payload;
                })
                .then(res => {
                    const formatText = (text) => text ? text.replace(/\n/g, '<br>') : '-';
                    const row = `<tr>
                <td>${res.region || '-'}</td>
                <td>${res.province || '-'}</td>
                <td>${res.municipality || '-'}</td>
                <td>${res.barangay || '-'}</td>
                <td>${res.plantation_type || '-'}</td>
                <td>${res.year_established || '-'}</td>
                <td>${res.target_area_1 || '-'}</td>
                <td>${res.area_planted || '-'}</td>
                <td>${formatText(res.species_planted)}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.spacing || '')}">${renderExpandableSummaryCell(res.spacing, 45)}</td>
                <td>${res.maintenance || '-'}</td>
                <td>${res.target_area_2 || '-'}</td>
                <td>${res.actual_area || '-'}</td>
                <td>${res.mortality_rate || '-'}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.species_replanted || '')}">${renderExpandableSummaryCell(res.species_replanted, 60)}</td>
                <td>${res.nis_name || '-'}</td>
                <td>${res.remarks || '-'}</td>
                @if ($canManageRpwsis)
                    <td style="text-align:right;">
                        <button onclick="openDeleteModal('summary', ${res.id}, this)" class="status-select">Delete</button>
                    </td>
                @endif
            </tr>`;

                    document.getElementById('summaryTableBody').insertAdjacentHTML('beforeend', row);

                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });

                    closeSummaryModal();
                })
                .catch(error => {
                    console.error('Error saving summary record:', error);
                    alert(error.message || 'Unable to save the summary record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }
    </script>
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
