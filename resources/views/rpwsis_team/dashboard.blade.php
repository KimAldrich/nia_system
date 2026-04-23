@extends('layouts.app')
@section('title', 'Social And Environmental Team Dashboard')

@section('content')
    @php
        $canManageRpwsis = auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']);
    @endphp

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
            width: 100%;
        }

        .status-select:hover {
            border-color: #18181b;
        }

        /* CALENDAR STYLES */
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

        /* MODALS */
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
            padding: 10px 20px;
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

        /* TABLES */
        .custom-table {
            border-collapse: collapse;
            min-width: 2000px;
            background: #fff;
        }

        .custom-table thead th {
            font-size: 11px;
            color: #3f3f46;
            font-weight: 600;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #e4e4e7;
            background: #f8fafc;
            vertical-align: middle;
            transition: 0.2s;
        }

        .custom-table tbody td {
            padding: 12px 10px;
            font-size: 12px;
            text-align: center;
            border: 1px solid #e4e4e7;
            vertical-align: middle;
            color: #18181b;
            transition: 0.2s;
        }

        .custom-table tbody tr:hover td {
            background: #f0f9ff;
        }

        .sleek-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
        }

        .sleek-table th {
            text-align: left;
            padding-bottom: 15px;
            color: #a1a1aa;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #f4f4f5;
        }

        .sleek-table td {
            padding: 15px 0;
            border-bottom: 1px solid #f4f4f5;
            font-size: 13px;
            font-weight: 500;
            vertical-align: middle;
        }

        /* TABLE SPECIFIC COLUMNS */
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

        .col-action {
            min-width: 80px;
            text-align: right;
        }

        #summaryTable {
            min-width: 2600px;
        }

        /* NURSERY TABLE */
        #nurseryTable {
            min-width: 1600px;
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

        .status-compact-cell {
            white-space: normal;
            text-align: left;
        }

        .custom-table .col-standard {
            min-width: 120px;
        }

        .custom-table .col-medium {
            min-width: 160px;
            white-space: normal;
            line-height: 1.4;
        }

        .custom-table .col-wide {
            min-width: 260px;
            text-align: left !important;
            white-space: normal;
            line-height: 1.6;
        }

        .custom-table .col-expandable {
            min-width: 190px;
            max-width: 220px;
            white-space: normal;
            text-align: left !important;
        }

        /* EXPANDABLE TEXT */
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

        .hide-impl th.impl,
        .hide-impl td.impl {
            display: none;
        }

        /* SCROLL BARS */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: scroll;
            max-height: 600px;
            overflow-y: auto;
            border-radius: 10px;
            border: 1px solid #e4e4e7;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .table-responsive-wrapper::-webkit-scrollbar {
            height: 12px;
            width: 12px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 0 0 10px 10px;
            border-top: 1px solid #e4e4e7;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f8fafc;
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
                @include('partials.active-projects-table', [
                    'resolutions' => $resolutions ?? collect(),
                    'containerId' => 'activeProjectsContainer',
                    'editable' => auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']),
                    'updateRouteName' => 'rpwsis.resolutions.update_status',
                ])
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
            @include('partials.event-manager-readonly', ['events' => $events ?? collect(), 'categories' => $categories ?? collect()])
        </div>
    </div>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // A. STATUS ACCOMPLISHMENT TABLE                                                   --}}
    {{-- // -------------------------------------------------------------------------------- --}}
    <div class="ui-card">
        <div class="section-title">
            A. ACCOMPLISHMENT AS OF FEBRUARY 15, 2025 OF R&P WRSIS
            <div style="display:flex; gap:8px;">
                @if ($canManageRpwsis)
                    <button onclick="openModal()" class="status-select"
                        style="background-color: #2563eb; color: white; border-color: #2563eb;">+ Add Record</button>
                @endif
                <button onclick="exportExcel()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a;">Export Excel</button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="simpleTable">
                <thead>
                    <tr>
                        <th rowspan="3" class="col-standard">Region</th>
                        <th rowspan="3" class="col-standard">Batch</th>
                        <th rowspan="3" class="col-standard">Allocation</th>
                        <th rowspan="3" class="col-standard">NIS</th>
                        <th rowspan="3" class="col-activity">Activity Type</th>
                        <th rowspan="3" class="col-remarks">Remarks</th>
                        <th rowspan="3" class="col-amount">Amount</th>

                        <th colspan="12">B. Implementation Stage</th>

                        <th rowspan="3" class="col-standard">PHY %</th>
                        <th rowspan="3" class="col-standard">FIN %</th>
                        <th rowspan="3" class="col-standard">Expenditures</th>

                        @if ($canManageRpwsis)
                            <th rowspan="3" class="col-action" style="text-align:right;">Action</th>
                        @endif
                    </tr>
                    <tr>
                        <th colspan="8">1. Preparation and Establishment</th>
                        <th colspan="3">2. Conduct of IEC</th>
                        <th colspan="1">3. Monitoring and Evaluation</th>
                    </tr>
                    <tr>
                        <th class="impl col-standard">POW Formulation</th>
                        <th class="impl col-standard">Nursery area/Bunk House/STW</th>
                        <th class="impl col-standard">Seedling Production</th>
                        <th class="impl col-standard">Procurement </th>
                        <th class="impl col-standard">Site Preparation</th>
                        <th class="impl col-standard">Vegetative enhancement</th>
                        <th class="impl col-standard">Establishment of Wattling</th>
                        <th class="impl col-remarks">Right of Way/Rent/ Wages of Caretaker/</th>
                        <th class="impl col-remarks">Conduct of consultative meetings</th>
                        <th class="impl col-remarks">Distribution of reading materials</th>
                        <th class="impl col-remarks">Installation of signboards/signages</th>
                        <th class="impl col-remarks">Supervision and Monitoring of implementations</th>
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
                                @php
                                    $colClass = 'col-standard';
                                    if ($index === 4) {
                                        $colClass = 'col-activity';
                                    }
                                    if ($index === 5 || ($index >= 14 && $index <= 18)) {
                                        $colClass = 'col-remarks';
                                    }
                                    if ($index === 6) {
                                        $colClass = 'col-amount';
                                    }
                                @endphp
                                <td class="{{ $index >= 7 && $index <= 18 ? 'impl ' : '' }}{{ $colClass }} status-compact-cell"
                                    data-export-value="{{ $value }}">
                                    {!! !empty($value)
                                        ? '<div class="expandable-cell' .
                                            (mb_strlen((string) $value) <= 28 ? ' is-expanded' : '') .
                                            '">' .
                                            '<div class="expandable-preview">' .
                                            e(\Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $value), 28)) .
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
                                <td class="col-action" style="text-align:right;">
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
            <div id="statusModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;">
                <div
                    style="width:90%; max-width:900px; background:#fff; margin:40px auto; border-radius:12px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-height:90vh; overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h3 style="margin:0; color:#0c4d05;">Add Accomplishment</h3>
                        <button onclick="closeModal()"
                            style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;"
                            onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#a1a1aa'">&times;</button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Project
                                Information</p>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="region" placeholder="Region" class="status-select" maxlength="100"
                                    required>
                                <input id="batch" placeholder="Batch" class="status-select" maxlength="100">
                                <input id="allocation" placeholder="Allocation" class="status-select" maxlength="255">
                                <input id="nis" placeholder="NIS" class="status-select" maxlength="255">
                                <input id="activity" placeholder="Activity Type" class="status-select" maxlength="255"
                                    required>
                                <input id="remarks" placeholder="Remarks" class="status-select" maxlength="1000">
                                <input id="amount" placeholder="Amount" class="status-select" type="number"
                                    min="0" step="0.01">
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Implementation
                                Stage</p>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="c1" placeholder="POW" class="status-select" maxlength="255">
                                <input id="c2" placeholder="Nursery" class="status-select" maxlength="255">
                                <input id="c3" placeholder="Seedling" class="status-select" maxlength="255">
                                <input id="c4" placeholder="Procurement" class="status-select" maxlength="255">
                                <input id="c5" placeholder="Site Prep" class="status-select" maxlength="255">
                                <input id="c6" placeholder="Vegetative" class="status-select" maxlength="255">
                                <input id="c7" placeholder="Wattling" class="status-select" maxlength="255">
                                <input id="c8" placeholder="Right of Way" class="status-select" maxlength="255">
                                <input id="c9" placeholder="Consultative" class="status-select" maxlength="255">
                                <input id="c10" placeholder="Distribution" class="status-select" maxlength="255">
                                <input id="c11" placeholder="Signages" class="status-select" maxlength="255">
                                <input id="c12" placeholder="Monitoring" class="status-select" maxlength="255">
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Project Metrics
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="phy" placeholder="PHY %" class="status-select" type="number"
                                    min="0" max="100" step="0.01">
                                <input id="fin" placeholder="FIN %" class="status-select" type="number"
                                    min="0" max="100" step="0.01">
                                <input id="exp" placeholder="Expenditures" class="status-select" type="number"
                                    min="0" step="0.01">
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button onclick="closeModal()" class="status-select" style="width: auto;">Cancel</button>
                            <button onclick="saveRecord(this)" class="status-select"
                                style="background:#0c4d05; color:white; width: auto;">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // NEW FEATURE: REHABILITATION AND PROTECTION SUMMARY TABLE                           --}}
    {{-- // -------------------------------------------------------------------------------- --}}

    <div class="ui-card" style="margin-top: 2rem;">
        <div class="section-title">
            REHABILITATION AND PROTECTION OF WATER RESOURCES SUPPORTING IRRIGATION SYSTEM (R&P WRSIS)
            <div style="font-size: 14px; font-weight: normal; margin-top: 4px; opacity: 0.9;">Summary of Accomplishment
            </div>
            <div style="display:flex; gap:8px; margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openSummaryModal()" class="status-select"
                        style="background-color: #2563eb; color: white; border-color: #2563eb; width: auto;">+ Add
                        Record</button>
                @endif
                <button onclick="exportSummaryExcel()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a; width: auto;">Export
                    Excel</button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="summaryTable">
                <thead>
                    <tr>
                        <th class="col-standard">Region</th>
                        <th class="col-standard">Province</th>
                        <th class="col-standard">Municipality</th>
                        <th class="col-standard">Barangay</th>
                        <th class="col-medium">Type of Plantation</th>
                        <th class="col-standard">Year Established</th>
                        <th class="col-standard">Target Area</th>
                        <th class="col-standard">Area Planted</th>
                        <th class="col-wide">Species and Number of Seedlings Planted</th>
                        <th class="col-expandable">Spacing</th>
                        <th class="col-medium">1st Year Maintenance and Protection</th>
                        <th class="col-standard">Replanting Target Area</th>
                        <th class="col-standard">Replanting Actual Area</th>
                        <th class="col-standard">Mortality Rate</th>
                        <th class="col-expandable">Species Replanted</th>
                        <th class="col-medium">Name of NIS</th>
                        <th class="col-medium">Remarks</th>
                        @if ($canManageRpwsis)
                            <th class="col-action" style="text-align:right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="summaryTableBody">
                    @foreach ($summaryRecords ?? [] as $row)
                        <tr>
                            <td class="col-standard">{{ $row->region }}</td>
                            <td class="col-standard">{{ $row->province }}</td>
                            <td class="col-standard">{{ $row->municipality }}</td>
                            <td class="col-standard">{{ $row->barangay }}</td>
                            <td class="col-medium">{{ $row->plantation_type }}</td>
                            <td class="col-standard">{{ $row->year_established }}</td>
                            <td class="col-standard">{{ $row->target_area_1 }}</td>
                            <td class="col-standard">{{ $row->area_planted }}</td>
                            <td class="col-expandable" data-export-value="{{ $row->species_planted }}">
                                {!! !empty($row->species_planted)
                                    ? '<div class="expandable-cell' .
                                        (mb_strlen((string) $row->species_planted) <= 60 ? ' is-expanded' : '') .
                                        '">' .
                                        '<div class="expandable-preview">' .
                                        e(\Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $row->species_planted), 60)) .
                                        '</div>' .
                                        '<div class="expandable-full">' .
                                        nl2br(e($row->species_planted)) .
                                        '</div>' .
                                        (mb_strlen((string) $row->species_planted) > 60
                                            ? '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>'
                                            : '') .
                                        '</div>'
                                    : '-' !!}
                            </td>
                            <td class="col-expandable" data-export-value="{{ $row->spacing }}">
                                {!! !empty($row->spacing)
                                    ? '<div class="expandable-cell' .
                                        (mb_strlen((string) $row->spacing) <= 45 ? ' is-expanded' : '') .
                                        '">' .
                                        '<div class="expandable-preview">' .
                                        e(\Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $row->spacing), 45)) .
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
                            <td class="col-medium">{{ $row->maintenance }}</td>
                            <td class="col-standard">{{ $row->target_area_2 }}</td>
                            <td class="col-standard">{{ $row->actual_area }}</td>
                            <td class="col-standard">{{ $row->mortality_rate }}</td>
                            <td class="col-expandable" data-export-value="{{ $row->species_replanted }}">
                                {!! !empty($row->species_replanted)
                                    ? '<div class="expandable-cell' .
                                        (mb_strlen((string) $row->species_replanted) <= 60 ? ' is-expanded' : '') .
                                        '">' .
                                        '<div class="expandable-preview">' .
                                        e(\Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $row->species_replanted), 60)) .
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
                            <td class="col-medium">{{ $row->nis_name }}</td>
                            <td class="col-medium">{{ $row->remarks }}</td>
                            @if ($canManageRpwsis)
                                <td class="col-action" style="text-align:right;">
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
            <div id="summaryModal"
                style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;">
                <div
                    style="width:90%; max-width:900px; background:#fff; margin:40px auto; border-radius:12px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-height:90vh; overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h3 style="margin:0; color:#0c4d05;">Add Summary Record</h3>
                        <button onclick="closeSummaryModal()"
                            style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;"
                            onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#a1a1aa'">&times;</button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Location Details
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="sum_region" placeholder="Region" class="status-select">
                                <input id="sum_province" placeholder="Province" class="status-select">
                                <input id="sum_municipality" placeholder="Municipality" class="status-select">
                                <input id="sum_barangay" placeholder="Barangay" class="status-select">
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Plantation Info
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="sum_type" placeholder="Type of Plantation" class="status-select">
                                <input id="sum_year" placeholder="Year Established" class="status-select">
                                <input id="sum_target_1" placeholder="Target Area" class="status-select">
                                <input id="sum_area_planted" placeholder="Area Planted" class="status-select">
                                <textarea id="sum_spacing" placeholder="Spacing (one per line)" class="status-select"
                                    style="min-height: 60px; resize: vertical;"></textarea>
                                <input id="sum_maintenance" placeholder="1st Year M&P" class="status-select">
                                <textarea id="sum_species" placeholder="Species & Number Planted (Use Enter for new lines)" class="status-select"
                                    style="grid-column: span 3; height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Replanting
                                Status & Extras</p>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="sum_target_2" placeholder="Replanting Target Area" class="status-select">
                                <input id="sum_actual" placeholder="Replanting Actual Area" class="status-select">
                                <input id="sum_mortality" placeholder="Mortality Rate" class="status-select">
                                <input id="sum_nis" placeholder="Name of NIS" class="status-select">
                                <input id="sum_remarks" placeholder="Remarks" class="status-select">
                                <div style="grid-column: span 1;"></div>
                                <textarea id="sum_replanted" placeholder="Species Replanted (Use Enter for new lines)" class="status-select"
                                    style="grid-column: span 3; height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button onclick="closeSummaryModal()" class="status-select"
                                style="width: auto;">Cancel</button>
                            <button onclick="saveSummaryRecord(this)" class="status-select"
                                style="background:#0c4d05; color:white; width: auto;">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // NEW FEATURE: NURSERY ESTABLISHMENT TABLE                                         --}}
    {{-- // -------------------------------------------------------------------------------- --}}

    <div class="ui-card" style="margin-top: 2rem;">
        <div class="section-title">
            B. Nursery Establishment
            <div style="display:flex; gap:8px; margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openNurseryModal()" class="status-select"
                        style="background-color: #2563eb; color: white; border-color: #2563eb; width: auto;">+ Add
                        Record</button>
                @endif
                <button onclick="exportNurseryExcel()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a; width: auto;">Export
                    Excel</button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="nurseryTable">
                <thead>
                    <tr>
                        <th class="col-standard">Region</th>
                        <th class="col-standard">Province</th>
                        <th class="col-standard">Municipality</th>
                        <th class="col-standard">Barangay</th>
                        <th class="col-medium">X-Coordinates</th>
                        <th class="col-medium">Y-Coordinates</th>
                        <th class="col-medium">Number Seedlings Produced</th>
                        <th class="col-medium">Type of Nursery</th>
                        <th class="col-medium">Name of NIS</th>
                        <th class="col-wide">Remarks</th>
                        @if ($canManageRpwsis)
                            <th class="col-action" style="text-align:right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="nurseryTableBody">
                    @foreach ($nurseryRecords ?? [] as $row)
                        <tr>
                            <td class="col-standard">{{ $row->region }}</td>
                            <td class="col-standard">{{ $row->province }}</td>
                            <td class="col-standard">{{ $row->municipality }}</td>
                            <td class="col-standard">{{ $row->barangay }}</td>
                            <td class="col-medium">{{ $row->x_coordinates }}</td>
                            <td class="col-medium">{{ $row->y_coordinates }}</td>
                            <td class="col-medium">{{ $row->seedlings_produced }}</td>
                            <td class="col-medium">{{ $row->nursery_type }}</td>
                            <td class="col-medium">{{ $row->nis_name }}</td>
                            <td class="col-wide">{!! nl2br(e($row->remarks)) !!}</td>
                            @if ($canManageRpwsis)
                                <td class="col-action" style="text-align:right;">
                                    <button onclick="openDeleteModal('nursery', {{ $row->id }}, this)"
                                        class="status-select">Delete</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="nurseryModal"
                style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;">
                <div
                    style="width:90%; max-width:900px; background:#fff; margin:40px auto; border-radius:12px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-height:90vh; overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h3 style="margin:0; color:#0c4d05;">Add Nursery Record</h3>
                        <button onclick="closeNurseryModal()"
                            style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;"
                            onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#a1a1aa'">&times;</button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Location Details
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="nur_region" placeholder="Region" class="status-select">
                                <input id="nur_province" placeholder="Province" class="status-select">
                                <input id="nur_municipality" placeholder="Municipality" class="status-select">
                                <input id="nur_barangay" placeholder="Barangay" class="status-select">
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Nursery Info</p>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">
                                <input id="nur_x_coord" placeholder="X-Coordinates" class="status-select">
                                <input id="nur_y_coord" placeholder="Y-Coordinates" class="status-select">
                                <input id="nur_seedlings" placeholder="Number Seedlings Produced" class="status-select">
                                <input id="nur_type" placeholder="Type of Nursery" class="status-select">
                                <input id="nur_nis" placeholder="Name of NIS" class="status-select">
                                <textarea id="nur_remarks" placeholder="Remarks" class="status-select" style="grid-column: span 3; height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button onclick="closeNurseryModal()" class="status-select"
                                style="width: auto;">Cancel</button>
                            <button onclick="saveNurseryRecord(this)" class="status-select"
                                style="background:#0c4d05; color:white; width: auto;">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // NEW FEATURE: INFORMATIVE SIGNAGES INSTALLED TABLE                                --}}
    {{-- // -------------------------------------------------------------------------------- --}}

    <div class="ui-card" style="margin-top: 2rem;">
        <div class="section-title">
            C. Informative Signages Installed
            <div style="display:flex; gap:8px; margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openSignagesModal()" class="status-select"
                        style="background-color: #2563eb; color: white; border-color: #2563eb; width: auto;">+ Add
                        Record</button>
                @endif
                <button onclick="exportSignagesExcel()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a; width: auto;">Export
                    Excel</button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="signagesTable" style="min-width: 1600px;">
                <thead>
                    <tr>
                        <th class="col-standard">Region</th>
                        <th class="col-standard">Province</th>
                        <th class="col-standard">Municipality</th>
                        <th class="col-standard">Barangay</th>
                        <th class="col-medium">X-Coordinates</th>
                        <th class="col-medium">Y-Coordinates</th>
                        <th class="col-medium">Type of Signages</th>
                        <th class="col-medium">Name of NIS</th>
                        <th class="col-wide">Remarks</th>
                        @if ($canManageRpwsis)
                            <th class="col-action" style="text-align:right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="signagesTableBody">
                    @foreach ($signageRecords ?? [] as $row)
                        <tr>
                            <td class="col-standard">{{ $row->region }}</td>
                            <td class="col-standard">{{ $row->province }}</td>
                            <td class="col-standard">{{ $row->municipality }}</td>
                            <td class="col-standard">{!! nl2br(e($row->barangay)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->x_coordinates)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->y_coordinates)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->signage_type)) !!}</td>
                            <td class="col-medium">{{ $row->nis_name }}</td>
                            <td class="col-wide">{!! nl2br(e($row->remarks)) !!}</td>
                            @if ($canManageRpwsis)
                                <td class="col-action" style="text-align:right;">
                                    <button onclick="openDeleteModal('signages', {{ $row->id }}, this)"
                                        class="status-select">Delete</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="signagesModal"
                style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;">
                <div
                    style="width:90%; max-width:900px; background:#fff; margin:40px auto; border-radius:12px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-height:90vh; overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h3 style="margin:0; color:#0c4d05;">Add Signage Record</h3>
                        <button onclick="closeSignagesModal()"
                            style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;"
                            onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#a1a1aa'">&times;</button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Location Details
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="sig_region" placeholder="Region" class="status-select">
                                <input id="sig_province" placeholder="Province" class="status-select">
                                <input id="sig_municipality" placeholder="Municipality" class="status-select">
                                <textarea id="sig_barangay" placeholder="Barangay (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Signage Info</p>
                            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:10px;">
                                <textarea id="sig_x_coord" placeholder="X-Coordinates (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <textarea id="sig_y_coord" placeholder="Y-Coordinates (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <textarea id="sig_type" placeholder="Type of Signages (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <input id="sig_nis" placeholder="Name of NIS" class="status-select">
                                <textarea id="sig_remarks" placeholder="Remarks" class="status-select" style="grid-column: span 2; height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button onclick="closeSignagesModal()" class="status-select"
                                style="width: auto;">Cancel</button>
                            <button onclick="saveSignagesRecord(this)" class="status-select"
                                style="background:#0c4d05; color:white; width: auto;">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- // -------------------------------------------------------------------------------- --}}
    {{-- // NEW FEATURE: OTHER INFRASTRUCTURES TABLE                                         --}}
    {{-- // -------------------------------------------------------------------------------- --}}

    <div class="ui-card" style="margin-top: 2rem;">
        <div class="section-title">
            D. Other Infrastructures
            <div style="display:flex; gap:8px; margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openInfrastructureModal()" class="status-select"
                        style="background-color: #2563eb; color: white; border-color: #2563eb; width: auto;">+ Add
                        Record</button>
                @endif
                <button onclick="exportInfrastructureExcel()" class="status-select"
                    style="background-color: #16a34a; color: white; border-color: #16a34a; width: auto;">Export
                    Excel</button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="infrastructureTable" style="min-width: 1600px;">
                <thead>
                    <tr>
                        <th class="col-standard">Region</th>
                        <th class="col-standard">Province</th>
                        <th class="col-standard">Municipality</th>
                        <th class="col-standard">Barangay</th>
                        <th class="col-medium">X-Coordinates</th>
                        <th class="col-medium">Y-Coordinates</th>
                        <th class="col-medium">Type of Infrastructure</th>
                        <th class="col-medium">Name of NIS</th>
                        <th class="col-wide">Remarks</th>
                        @if ($canManageRpwsis)
                            <th class="col-action" style="text-align:right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="infrastructureTableBody">
                    @foreach ($infrastructureRecords ?? [] as $row)
                        <tr>
                            <td class="col-standard">{{ $row->region }}</td>
                            <td class="col-standard">{{ $row->province }}</td>
                            <td class="col-standard">{{ $row->municipality }}</td>
                            <td class="col-standard">{!! nl2br(e($row->barangay)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->x_coordinates)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->y_coordinates)) !!}</td>
                            <td class="col-medium">{!! nl2br(e($row->infrastructure_type)) !!}</td>
                            <td class="col-medium">{{ $row->nis_name }}</td>
                            <td class="col-wide">{!! nl2br(e($row->remarks)) !!}</td>
                            @if ($canManageRpwsis)
                                <td class="col-action" style="text-align:right;">
                                    <button onclick="openDeleteModal('infrastructure', {{ $row->id }}, this)"
                                        class="status-select">Delete</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="infrastructureModal"
                style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;">
                <div
                    style="width:90%; max-width:900px; background:#fff; margin:40px auto; border-radius:12px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-height:90vh; overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h3 style="margin:0; color:#0c4d05;">Add Infrastructure Record</h3>
                        <button onclick="closeInfrastructureModal()"
                            style="background:transparent; border:none; font-size:24px; color:#a1a1aa; cursor:pointer; padding:0; line-height:1; outline:none;">&times;</button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Location Details
                            </p>
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px;">
                                <input id="inf_region" placeholder="Region" class="status-select">
                                <input id="inf_province" placeholder="Province" class="status-select">
                                <input id="inf_municipality" placeholder="Municipality" class="status-select">
                                <textarea id="inf_barangay" placeholder="Barangay (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="background:#f9fafb; padding:15px; border-radius:10px; border:1px solid #e4e4e7;">
                            <p style="font-size:12px; font-weight:600; margin-bottom:10px; color:#0c4d05;">Infrastructure
                                Info</p>
                            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:10px;">
                                <textarea id="inf_x_coord" placeholder="X-Coordinates (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <textarea id="inf_y_coord" placeholder="Y-Coordinates (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <textarea id="inf_type" placeholder="Type of Infrastructure (Use Enter for multiple)" class="status-select"
                                    style="height: 60px;"></textarea>
                                <input id="inf_nis" placeholder="Name of NIS" class="status-select">
                                <textarea id="inf_remarks" placeholder="Remarks" class="status-select" style="grid-column: span 2; height: 60px;"></textarea>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <button onclick="closeInfrastructureModal()" class="status-select"
                                style="width: auto;">Cancel</button>
                            <button onclick="saveInfrastructureRecord(this)" class="status-select"
                                style="background:#0c4d05; color:white; width: auto;">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($canManageRpwsis)
        <div class="delete-modal-overlay" id="deleteConfirmModal">
            <div class="delete-modal-box">
                <h3 class="delete-modal-title" id="deleteModalTitle">Delete Record</h3>
                <p class="delete-modal-text" id="deleteModalMessage">Are you sure you want to delete this record? This
                    action cannot be undone.</p>
                <div class="delete-modal-actions">
                    <button type="button" onclick="closeDeleteModal()" class="delete-modal-btn cancel">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="delete-modal-btn confirm">Delete</button>
                </div>
            </div>
        </div>
    @endif

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
            if (!text) return '-';

            const normalizedText = text.replace(/\s+/g, ' ').trim();
            const preview = normalizedText.length > previewLength ?
                `${normalizedText.slice(0, previewLength).trimEnd()}...` : normalizedText;
            const escapedPreview = escapeSummaryHtml(preview);
            const escapedFull = escapeSummaryHtml(text).replace(/\n/g, '<br>');
            const expandedClass = normalizedText.length <= previewLength ? ' is-expanded' : '';
            const toggleButton = normalizedText.length > previewLength ?
                '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>' : '';

            return `
                <div class="expandable-cell${expandedClass}">
                    <div class="expandable-preview">${escapedPreview}</div>
                    <div class="expandable-full">${escapedFull}</div>
                    ${toggleButton}
                </div>
            `;
        }

        function renderStatusExpandableCell(value, extraClass = '') {
            const text = String(value ?? '').trim();
            const className = extraClass ? ` class="${extraClass}"` : '';
            if (!text) return `<td${className}>-</td>`;

            const normalizedText = text.replace(/\s+/g, ' ').trim();
            const previewLimit = 28;
            const preview = normalizedText.length > previewLimit ? `${normalizedText.slice(0, previewLimit).trimEnd()}...` :
                normalizedText;
            const escapedPreview = escapeSummaryHtml(preview);
            const escapedFull = escapeSummaryHtml(text).replace(/\n/g, '<br>');
            const expandedClass = normalizedText.length <= previewLimit ? ' is-expanded' : '';
            const toggleButton = normalizedText.length > previewLimit ?
                '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>' : '';
            const exportValue = escapeSummaryHtml(text);

            return `<td${className} data-export-value="${exportValue}">
                <div class="expandable-cell${expandedClass}">
                    <div class="expandable-preview">${escapedPreview}</div>
                    <div class="expandable-full">${escapedFull}</div>
                    ${toggleButton}
                </div>
            </td>`;
        }

        function toggleSummaryCell(button) {
            const container = button.closest('.expandable-cell');
            const isExpanded = container.classList.toggle('is-expanded');
            button.textContent = isExpanded ? 'Show less' : 'Show more';
        }

        function validateRequiredFields(fieldIds) {
            const emptyFields = [];
            fieldIds.forEach(id => {
                const field = document.getElementById(id);
                if (!field) return;
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

        function openModal() {
            const modal = document.getElementById('statusModal');
            if (modal) modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('statusModal');
            if (modal) modal.style.display = 'none';
        }

        function openSummaryModal() {
            const modal = document.getElementById('summaryModal');
            if (modal) modal.style.display = 'block';
        }

        function closeSummaryModal() {
            const modal = document.getElementById('summaryModal');
            if (modal) modal.style.display = 'none';
        }

        function openNurseryModal() {
            const modal = document.getElementById('nurseryModal');
            if (modal) modal.style.display = 'block';
        }

        function closeNurseryModal() {
            const modal = document.getElementById('nurseryModal');
            if (modal) modal.style.display = 'none';
        }

        function openSignagesModal() {
            const modal = document.getElementById('signagesModal');
            if (modal) modal.style.display = 'block';
        }

        function closeSignagesModal() {
            const modal = document.getElementById('signagesModal');
            if (modal) modal.style.display = 'none';
        }

        function openInfrastructureModal() {
            const modal = document.getElementById('infrastructureModal');
            if (modal) modal.style.display = 'block';
        }

        function closeInfrastructureModal() {
            const modal = document.getElementById('infrastructureModal');
            if (modal) modal.style.display = 'none';
        }

        let pendingDelete = {
            type: null,
            id: null,
            button: null
        };

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

            if (title) {
                if (type === 'summary') title.textContent = 'Delete Summary Record';
                else if (type === 'nursery') title.textContent = 'Delete Nursery Record';
                else if (type === 'signages') title.textContent = 'Delete Signage Record';
                else if (type === 'infrastructure') title.textContent = 'Delete Infrastructure Record';
                else title.textContent = 'Delete Accomplishment Record';
            }
            if (message) {
                if (type === 'summary') message.textContent =
                    'Are you sure you want to delete this summary record? This action cannot be undone.';
                else if (type === 'nursery') message.textContent =
                    'Are you sure you want to delete this nursery record? This action cannot be undone.';
                else if (type === 'signages') message.textContent =
                    'Are you sure you want to delete this signage record? This action cannot be undone.';
                else if (type === 'infrastructure') message.textContent =
                    'Are you sure you want to delete this infrastructure record? This action cannot be undone.';
                else message.textContent = 'Are you sure you want to delete this record? This action cannot be undone.';
            }
            modal.classList.add('active');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) modal.classList.remove('active');
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
            if (pendingDelete.type === 'nursery') {
                deleteNurseryRecord(pendingDelete.id, pendingDelete.button, true);
                return;
            }
            if (pendingDelete.type === 'signages') {
                deleteSignagesRecord(pendingDelete.id, pendingDelete.button, true);
                return;
            }
            if (pendingDelete.type === 'infrastructure') {
                deleteInfrastructureRecord(pendingDelete.id, pendingDelete.button, true);
                return;
            }
            deleteAccomplishment(pendingDelete.id, pendingDelete.button, true);
        }

        document.addEventListener('click', function(e) {
            const statusModal = document.getElementById('statusModal');
            const summaryModal = document.getElementById('summaryModal');
            const nurseryModal = document.getElementById('nurseryModal');
            const signagesModal = document.getElementById('signagesModal');
            const infrastructureModal = document.getElementById('infrastructureModal');
            const deleteModal = document.getElementById('deleteConfirmModal');

            if (statusModal && e.target === statusModal) closeModal();
            if (summaryModal && e.target === summaryModal) closeSummaryModal();
            if (nurseryModal && e.target === nurseryModal) closeNurseryModal();
            if (signagesModal && e.target === signagesModal) closeSignagesModal();
            if (infrastructureModal && e.target === infrastructureModal) closeInfrastructureModal();
            if (deleteModal && e.target === deleteModal) closeDeleteModal();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) confirmDeleteBtn.addEventListener('click', performDelete);
        });

        // ======================= SAVE ACCOMPLISHMENT (FIRST TABLE) =======================
        function saveRecord(button = null) {
            const fields = [
                'region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount',
                'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
                'phy', 'fin', 'exp'
            ];

            const requiredFields = ['region', 'activity'];
            for (const id of requiredFields) {
                const input = document.getElementById(id);
                if (!input.checkValidity()) {
                    input.reportValidity();
                    input.focus();
                    return;
                }
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
                    if (!res.ok || !payload) throw new Error(payload?.message ||
                        'Unable to save the record right now.');
                    return payload;
                })
                .then(payload => {
                    const res = payload.record || payload;
                    const rowValues = [
                        res.region, res.batch, res.allocation, res.nis, res.activity, res.remarks, res.amount,
                        res.c1, res.c2, res.c3, res.c4, res.c5, res.c6, res.c7, res.c8, res.c9, res.c10, res.c11,
                        res.c12,
                        res.phy, res.fin, res.exp
                    ];

                    const renderedCells = rowValues.map((value, index) => {
                        let colClass = 'col-standard';
                        if (index === 4) colClass = 'col-activity';
                        if (index === 5 || (index >= 14 && index <= 18)) colClass = 'col-remarks';
                        if (index === 6) colClass = 'col-amount';
                        const className =
                            `${(index >= 7 && index <= 18) ? 'impl ' : ''}${colClass} status-compact-cell`
                            .trim();
                        return renderStatusExpandableCell(value, className);
                    }).join('');

                    const actionCell =
                        `@if ($canManageRpwsis)<td class="col-action" style="text-align:right;"><button onclick="openDeleteModal('accomplishment', ${res.id}, this)" class="status-select">Delete</button></td>@endif`;
                    const newRow = `<tr>${renderedCells}${actionCell}</tr>`;

                    document.getElementById('tableBody').insertAdjacentHTML('beforeend', newRow);
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
                    alert(error.message || 'Unable to save the record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }

        // ======================= SAVE SUMMARY RECORD (SECOND TABLE) =======================
        function saveSummaryRecord(button = null) {
            const fields = [
                'sum_region', 'sum_province', 'sum_municipality', 'sum_barangay', 'sum_type', 'sum_year',
                'sum_target_1', 'sum_area_planted',
                'sum_species', 'sum_spacing', 'sum_maintenance', 'sum_target_2', 'sum_actual', 'sum_mortality',
                'sum_replanted', 'sum_nis', 'sum_remarks'
            ];

            if (!validateRequiredFields(fields)) return;

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
                    if (!res.ok || !payload) throw new Error(payload?.message ||
                        'Unable to save the summary record right now.');
                    return payload;
                })
                .then(payload => {
                    const res = payload.record || payload;
                    const formatText = (text) => text ? String(text).replace(/\n/g, '<br>') : '-';

                    let row = `<tr>
                    <td class="col-standard">${res.region || '-'}</td>
                    <td class="col-standard">${res.province || '-'}</td>
                    <td class="col-standard">${res.municipality || '-'}</td>
                    <td class="col-standard">${res.barangay || '-'}</td>
                    <td class="col-medium">${res.plantation_type || '-'}</td>
                    <td class="col-standard">${res.year_established || '-'}</td>
                    <td class="col-standard">${res.target_area_1 || '-'}</td>
                    <td class="col-standard">${res.area_planted || '-'}</td>
                    <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.species_planted || '')}">${renderExpandableSummaryCell(res.species_planted, 60)}</td>
                    <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.spacing || '')}">${renderExpandableSummaryCell(res.spacing, 45)}</td>
                    <td class="col-medium">${res.maintenance || '-'}</td>
                    <td class="col-standard">${res.target_area_2 || '-'}</td>
                    <td class="col-standard">${res.actual_area || '-'}</td>
                    <td class="col-standard">${res.mortality_rate || '-'}</td>
                    <td class="col-expandable" data-export-value="${escapeSummaryHtml(res.species_replanted || '')}">${renderExpandableSummaryCell(res.species_replanted, 60)}</td>
                    <td class="col-medium">${res.nis_name || '-'}</td>
                    <td class="col-medium">${res.remarks || '-'}</td>
                    @if ($canManageRpwsis)
                        <td class="col-action" style="text-align:right;">
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
                    alert(error.message || 'Unable to save the summary record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }

        // ======================= SAVE NURSERY RECORD (THIRD TABLE) =======================
        function saveNurseryRecord(button = null) {
            const fields = [
                'nur_region', 'nur_province', 'nur_municipality', 'nur_barangay',
                'nur_x_coord', 'nur_y_coord', 'nur_seedlings', 'nur_type', 'nur_nis', 'nur_remarks'
            ];

            if (!validateRequiredFields(fields)) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            setButtonLoading(button, true);

            fetch('/rpwsis_team/nursery/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json().catch(() => null);
                    if (!res.ok || !payload) throw new Error(payload?.message ||
                        'Unable to save the nursery record right now.');
                    return payload;
                })
                .then(payload => {
                    const res = payload.record || payload;
                    const formatText = (text) => text ? String(text).replace(/\n/g, '<br>') : '-';

                    let row = `<tr>
                    <td class="col-standard">${res.region || '-'}</td>
                    <td class="col-standard">${res.province || '-'}</td>
                    <td class="col-standard">${res.municipality || '-'}</td>
                    <td class="col-standard">${res.barangay || '-'}</td>
                    <td class="col-medium">${res.x_coordinates || '-'}</td>
                    <td class="col-medium">${res.y_coordinates || '-'}</td>
                    <td class="col-medium">${res.seedlings_produced || '-'}</td>
                    <td class="col-medium">${res.nursery_type || '-'}</td>
                    <td class="col-medium">${res.nis_name || '-'}</td>
                    <td class="col-wide">${formatText(res.remarks)}</td>
                    @if ($canManageRpwsis)
                        <td class="col-action" style="text-align:right;">
                            <button onclick="openDeleteModal('nursery', ${res.id}, this)" class="status-select">Delete</button>
                        </td>
                    @endif
                </tr>`;

                    document.getElementById('nurseryTableBody').insertAdjacentHTML('beforeend', row);
                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });
                    closeNurseryModal();
                })
                .catch(error => {
                    alert(error.message || 'Unable to save the nursery record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }

        // ======================= SAVE SIGNAGES RECORD (FOURTH TABLE) =======================
        function saveSignagesRecord(button = null) {
            const fields = [
                'sig_region', 'sig_province', 'sig_municipality', 'sig_barangay',
                'sig_x_coord', 'sig_y_coord', 'sig_type', 'sig_nis', 'sig_remarks'
            ];

            if (!validateRequiredFields(fields)) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            setButtonLoading(button, true);

            fetch('/rpwsis_team/signages/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json().catch(() => null);
                    if (!res.ok || !payload) throw new Error(payload?.message ||
                        'Unable to save the signage record right now.');
                    return payload;
                })
                .then(payload => {
                    const res = payload.record || payload;
                    const formatText = (text) => text ? String(text).replace(/\n/g, '<br>') : '-';

                    let row = `<tr>
                    <td class="col-standard">${res.region || '-'}</td>
                    <td class="col-standard">${res.province || '-'}</td>
                    <td class="col-standard">${res.municipality || '-'}</td>
                    <td class="col-standard">${formatText(res.barangay)}</td>
                    <td class="col-medium">${formatText(res.x_coordinates)}</td>
                    <td class="col-medium">${formatText(res.y_coordinates)}</td>
                    <td class="col-medium">${formatText(res.signage_type)}</td>
                    <td class="col-medium">${res.nis_name || '-'}</td>
                    <td class="col-wide">${formatText(res.remarks)}</td>
                    @if ($canManageRpwsis)
                        <td class="col-action" style="text-align:right;">
                            <button onclick="openDeleteModal('signages', ${res.id}, this)" class="status-select">Delete</button>
                        </td>
                    @endif
                </tr>`;

                    document.getElementById('signagesTableBody').insertAdjacentHTML('beforeend', row);
                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });
                    closeSignagesModal();
                })
                .catch(error => {
                    alert(error.message || 'Unable to save the signage record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }

        // ======================= SAVE INFRASTRUCTURE RECORD (FIFTH TABLE) =======================
        function saveInfrastructureRecord(button = null) {
            const fields = [
                'inf_region', 'inf_province', 'inf_municipality', 'inf_barangay',
                'inf_x_coord', 'inf_y_coord', 'inf_type', 'inf_nis', 'inf_remarks'
            ];

            if (!validateRequiredFields(fields)) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            setButtonLoading(button, true);

            fetch('/rpwsis_team/infrastructure/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    const payload = await res.json().catch(() => null);
                    if (!res.ok || !payload) throw new Error(payload?.message ||
                        'Unable to save the infrastructure record right now.');
                    return payload;
                })
                .then(payload => {
                    const res = payload.record || payload;
                    const formatText = (text) => text ? String(text).replace(/\n/g, '<br>') : '-';

                    let row = `<tr>
                    <td class="col-standard">${res.region || '-'}</td>
                    <td class="col-standard">${res.province || '-'}</td>
                    <td class="col-standard">${res.municipality || '-'}</td>
                    <td class="col-standard">${formatText(res.barangay)}</td>
                    <td class="col-medium">${formatText(res.x_coordinates)}</td>
                    <td class="col-medium">${formatText(res.y_coordinates)}</td>
                    <td class="col-medium">${formatText(res.infrastructure_type)}</td>
                    <td class="col-medium">${res.nis_name || '-'}</td>
                    <td class="col-wide">${formatText(res.remarks)}</td>
                    @if ($canManageRpwsis)
                        <td class="col-action" style="text-align:right;">
                            <button onclick="openDeleteModal('infrastructure', ${res.id}, this)" class="status-select">Delete</button>
                        </td>
                    @endif
                </tr>`;

                    document.getElementById('infrastructureTableBody').insertAdjacentHTML('beforeend', row);
                    fields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) {
                            field.value = '';
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }
                    });
                    closeInfrastructureModal();
                })
                .catch(error => {
                    alert(error.message || 'Unable to save the infrastructure record. Please try again.');
                })
                .finally(() => {
                    setButtonLoading(button, false);
                });
        }


        // ======================= DELETING =======================
        function deleteAccomplishment(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('accomplishment', id, btn);
                return;
            }
            btn.disabled = true;
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
                    if (!res.ok || !payload.success) throw new Error(payload.message ||
                        'Failed to delete the record.');
                    return payload;
                })
                .then(data => {
                    btn.closest('tr').remove();
                    closeDeleteModal();
                })
                .catch(error => {
                    alert(error.message || 'An error occurred while deleting.');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        }

        function deleteSummaryRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('summary', id, btn);
                return;
            }
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
                    alert("An error occurred while deleting.");
                });
        }

        function deleteNurseryRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('nursery', id, btn);
                return;
            }
            fetch(`/rpwsis_team/nursery/${id}/delete`, {
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
                    alert("An error occurred while deleting.");
                });
        }

        function deleteSignagesRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('signages', id, btn);
                return;
            }
            fetch(`/rpwsis_team/signages/${id}/delete`, {
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
                    alert("An error occurred while deleting.");
                });
        }

        function deleteInfrastructureRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('infrastructure', id, btn);
                return;
            }
            fetch(`/rpwsis_team/infrastructure/${id}/delete`, {
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
                    alert("An error occurred while deleting.");
                });
        }

        // ======================= EXCEL EXPORTS =======================
        function exportExcel() {
            let wb = XLSX.utils.book_new();
            let wsData = [];

            const headers = document.querySelectorAll("#simpleTable thead tr:last-child th");
            let headerRow = [];
            headers.forEach(th => {
                headerRow.push(th.innerText.trim());
            });
            headerRow.push("PHY %", "FIN %", "Expenditures");
            wsData.push(headerRow);

            const rows = document.querySelectorAll("#simpleTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];
                cols.forEach((td, i) => {
                    if (i !== cols.length - 1) {
                        let text = td.dataset.exportValue ?? td.innerText.trim();
                        text = text.replace(/Show more|Show less/gi, '').trim();
                        rowData.push(text);
                    }
                });
                wsData.push(rowData);
            });

            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Accomplishment");
            XLSX.writeFile(wb, "status_accomplishment.xlsx");
        }

        function exportSummaryExcel() {
            let wb = XLSX.utils.book_new();
            let wsData = [];

            let headerRow = [
                "Region", "Province", "Municipality", "Barangay", "Type of Plantation",
                "Year Established", "Target Area", "Area Planted", "Species and Number Planted",
                "Spacing", "1st Year Maintenance", "Replanting Target Area", "Replanting Actual Area",
                "Mortality Rate", "Species Replanted", "Name of NIS", "Remarks"
            ];
            wsData.push(headerRow);

            const rows = document.querySelectorAll("#summaryTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];
                cols.forEach((td, i) => {
                    if (i < headerRow.length) {
                        let text = td.dataset.exportValue ?? td.innerText.trim();
                        text = text.replace(/Show more|Show less/gi, '').trim();
                        rowData.push(text);
                    }
                });
                wsData.push(rowData);
            });

            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Summary");
            XLSX.writeFile(wb, "summary_of_accomplishment.xlsx");
        }

        function exportNurseryExcel() {
            let wb = XLSX.utils.book_new();
            let wsData = [];

            let headerRow = [
                "Region", "Province", "Municipality", "Barangay", "X-Coordinates",
                "Y-Coordinates", "Number Seedlings Produced", "Type of Nursery", "Name of NIS", "Remarks"
            ];
            wsData.push(headerRow);

            const rows = document.querySelectorAll("#nurseryTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];
                cols.forEach((td, i) => {
                    if (i < headerRow.length) {
                        let text = td.innerText.trim();
                        text = text.replace(/Show more|Show less/gi, '').trim();
                        rowData.push(text);
                    }
                });
                wsData.push(rowData);
            });

            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Nursery");
            XLSX.writeFile(wb, "nursery_establishment.xlsx");
        }

        function exportSignagesExcel() {
            let wb = XLSX.utils.book_new();
            let wsData = [];

            let headerRow = [
                "Region", "Province", "Municipality", "Barangay", "X-Coordinates",
                "Y-Coordinates", "Type of Signages", "Name of NIS", "Remarks"
            ];
            wsData.push(headerRow);

            const rows = document.querySelectorAll("#signagesTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];
                cols.forEach((td, i) => {
                    if (i < headerRow.length) {
                        let text = td.innerText.trim();
                        text = text.replace(/Show more|Show less/gi, '').trim();
                        rowData.push(text);
                    }
                });
                wsData.push(rowData);
            });

            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Signages");
            XLSX.writeFile(wb, "informative_signages.xlsx");
        }

        function exportInfrastructureExcel() {
            let wb = XLSX.utils.book_new();
            let wsData = [];

            let headerRow = [
                "Region", "Province", "Municipality", "Barangay", "X-Coordinates",
                "Y-Coordinates", "Type of Infrastructure", "Name of NIS", "Remarks"
            ];
            wsData.push(headerRow);

            const rows = document.querySelectorAll("#infrastructureTable tbody tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td");
                let rowData = [];
                cols.forEach((td, i) => {
                    if (i < headerRow.length) {
                        let text = td.innerText.trim();
                        text = text.replace(/Show more|Show less/gi, '').trim();
                        rowData.push(text);
                    }
                });
                wsData.push(rowData);
            });

            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Infrastructure");
            XLSX.writeFile(wb, "other_infrastructures.xlsx");
        }
    </script>

    {{-- CHART LOGIC --}}
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
