@extends('layouts.app')
@section('title', 'Program Completion Report Team Dashboard')

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

        .active-projects-panel {
            border: 1px solid #e4e4e7;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
        }

        .active-projects-table {
            width: 100%;
            min-width: 620px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .active-projects-table thead th {
            padding: 11px 12px;
            font-size: 10px;
            font-weight: 700;
            color: #52525b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            background: #f8fafc;
            border-bottom: 1px solid #e4e4e7;
            text-align: left;
        }

        .active-projects-table tbody td {
            padding: 12px;
            font-size: 11px;
            color: #334155;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
        }

        .active-projects-table tbody tr:last-child td {
            border-bottom: none;
        }

        .active-projects-table tbody tr:hover td {
            background: #fafafa;
        }

        .active-project-title {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
            line-height: 1.35;
        }

        .active-project-date {
            font-size: 10px;
            color: #94a3b8;
        }

        .active-project-action {
            text-align: right;
            white-space: nowrap;
        }

        .active-projects-table th:first-child,
        .active-projects-table td:first-child {
            width: 50%;
        }

        .active-projects-table th:nth-child(2),
        .active-projects-table td:nth-child(2) {
            width: 22%;
        }

        .active-projects-table .status-select {
            width: auto;
            min-width: 118px;
            max-width: 118px;
            padding: 5px 8px;
            font-size: 10px;
        }

        .sleek-table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1500px;
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
        }

        .custom-pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 20px;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            flex-wrap: wrap;
        }

        .custom-pagination svg {
            width: 16px;
            height: 16px;
        }

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
        }

        .custom-pagination .page-item.active {
            background: #4f46e5;
            color: #ffffff;
            border-color: #4f46e5;
        }

        .custom-pagination .page-item.disabled {
            background: #f8fafc;
            color: #cbd5e1;
            cursor: not-allowed;
        }

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
        }

        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 720px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-height: 90vh;
            overflow-y: auto;
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
            margin-bottom: 15px;
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
            background: #0c4d05;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .modern-btn-outline {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 40px;
            line-height: 1;
        }

        .btn-edit-icon {
            background: #e0e7ff;
            color: #4f46e5;
            border: none;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.12);
            flex-shrink: 0;
            white-space: nowrap;
            transition: 0.2s;
        }

        .btn-edit-icon:hover {
            background: #c7d2fe;
            color: #3730a3;
            transform: translateY(-1px);
        }
    </style>

    <h1 class="header-title">Project Completion Report Team Dashboard</h1>

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
                    'editable' => auth()->check() && in_array(auth()->user()->role, ['pcr_team', 'admin']),
                    'updateRouteName' => 'pcr.resolutions.update_status',
                ])
            </div>

            @include('partials.team-analytics-card', ['analytics' => $analytics ?? []])
        </div>

        <div class="side-column">
            @include('partials.event-manager-readonly', ['events' => $events ?? collect(), 'categories' => $categories ?? collect()])
        </div>
    </div>

    @php
        $canManagePcr = auth()->check() && in_array(auth()->user()->role, ['pcr_team', 'admin']);
    @endphp

    <div class="ui-card" id="pcrStatusSection">
        <div class="section-title">
            Project Completion Report Status Monitoring
            <div style="display: flex; gap: 10px;">
                @if ($canManagePcr)
                    <button onclick="openPcrAddModal()" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        + Add Data
                    </button>
                    @include('partials.excel-import-form', ['routeName' => 'pcr.status.import', 'target' => '#pcrStatusSection'])
                @endif
                <a href="{{ route('pcr.status.export', request()->query()) }}" onclick="handlePcrExport(event, this.href)" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </a>
            </div>
        </div>

        @include('partials.table-toolbar', [
            'asyncTarget' => '#pcrStatusSection',
            'searchName' => 'pcr_search',
            'searchPlaceholder' => 'Search fund source or allocation...',
            'filters' => [
                [
                    'name' => 'pcr_fund_source',
                    'label' => 'Fund Source',
                    'options' => ['' => 'All fund sources'] + collect($pcrFundSources ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                ],
            ],
            'resetKeys' => ['pcr_search', 'pcr_fund_source', 'pcr_page'],
        ])

        <div class="table-responsive">
            <table class="sleek-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 100px;">Fund Source</th>
                        <th rowspan="2" style="width: 120px;">No. of Contracts</th>
                        <th rowspan="2" style="width: 140px;">Allocation</th>
                        <th rowspan="2" style="width: 130px;">No. of PCR Prepared</th>
                        <th rowspan="2" style="width: 170px;">No. of PCR Submitted to Regional Office</th>
                        <th rowspan="2" style="width: 150px;">Accomplishment (Prepared/No. of Contracts)</th>
                        <th colspan="3" style="width: 360px; text-align: center;">Remarks</th>
                        @if ($canManagePcr)
                            <th rowspan="2" style="width: 140px;">Actions</th>
                        @endif
                    </tr>
                    <tr>
                        <th style="width: 120px;">For Signing of IA, Chief, DM, RM</th>
                        <th style="width: 120px;">For Submission to RO1</th>
                        <th style="width: 120px;">Not Yet Prepared / Pending Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pcrStatusReports ?? [] as $report)
                        <tr>
                            <td>{{ $report->fund_source }}</td>
                            <td>{{ $report->no_of_contracts }}</td>
                            <td>&#8369;{{ number_format($report->allocation, 2) }}</td>
                            <td>{{ $report->no_of_pcr_prepared }}</td>
                            <td>{{ $report->no_of_pcr_submitted_to_regional_office }}</td>
                            <td>{{ number_format($report->accomplishment_percentage, 2) }}%</td>
                            <td>{{ $report->for_signing_of_ia_chief_dm_rm }}</td>
                            <td>{{ $report->for_submission_to_ro1 }}</td>
                            <td>{{ $report->not_yet_prepared_pending_details }}</td>
                            @if ($canManagePcr)
                                <td style="text-align: center; white-space: nowrap;">
                                    <button type="button" class="btn-edit-icon" onclick="openPcrEditModal({{ $report->id }}, '{{ $report->fund_source }}', {{ $report->no_of_contracts }}, {{ $report->allocation }}, {{ $report->no_of_pcr_prepared }}, {{ $report->no_of_pcr_submitted_to_regional_office }}, {{ $report->accomplishment_percentage }}, {{ $report->for_signing_of_ia_chief_dm_rm }}, {{ $report->for_submission_to_ro1 }}, {{ $report->not_yet_prepared_pending_details }})"
                                        title="Edit Data" style="margin-right: 5px;">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                        Edit
                                    </button>
                                    <button type="button" onclick="openPcrDeleteModal({{ $report->id }})" class="btn-delete" title="Delete Data">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManagePcr ? 10 : 9 }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No PCR status data found.</td>
                        </tr>
                    @endforelse

                    @if(isset($pcrStatusReports) && $pcrStatusReports->count())
                        <tr style="font-weight: 700; background: #f8fafc; border-top: 2px solid #0c4d05;">
                            <td style="font-weight: 800; color: #0c4d05;">Total</td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('no_of_contracts') }}</td>
                            <td style="font-weight: 800; color: #0c4d05;">&#8369;{{ number_format($pcrStatusReports->sum('allocation'), 2) }}</td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('no_of_pcr_prepared') }}</td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('no_of_pcr_submitted_to_regional_office') }}</td>
                            <td></td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('for_signing_of_ia_chief_dm_rm') }}</td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('for_submission_to_ro1') }}</td>
                            <td style="font-weight: 800; color: #0c4d05;">{{ $pcrStatusReports->sum('not_yet_prepared_pending_details') }}</td>
                            @if ($canManagePcr)
                                <td></td>
                            @endif
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(isset($pcrStatusReports) && $pcrStatusReports->hasPages())
            <div class="custom-pagination">
                @if ($pcrStatusReports->onFirstPage())
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></span>
                @else
                    <a href="{{ $pcrStatusReports->withQueryString()->previousPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#pcrStatusSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg></a>
                @endif
                @foreach ($pcrStatusReports->withQueryString()->links()->elements as $element)
                    @if (is_string($element))
                        <span class="page-item disabled">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $pcrStatusReports->currentPage())
                                <span class="page-item active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-item" data-async-pagination="true" data-async-target="#pcrStatusSection">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($pcrStatusReports->hasMorePages())
                    <a href="{{ $pcrStatusReports->withQueryString()->nextPageUrl() }}" class="page-item" data-async-pagination="true" data-async-target="#pcrStatusSection"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></a>
                @else
                    <span class="page-item disabled"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></span>
                @endif
            </div>
        @endif
    </div>

    @if ($canManagePcr)
        <div class="modal-overlay" id="pcrAddModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add PCR Status Data</h3>
                <form action="{{ route('pcr.status.store') }}" method="POST" data-async-target="#pcrStatusSection" data-async-reset="true" data-async-close="#pcrAddModal" data-async-success-modal="#pcrSuccessModal">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">Fund Source</label><input type="text" name="fund_source" required class="modern-input" placeholder="e.g. 2024"></div>
                        <div><label class="modern-label">No. of Contracts</label><input type="number" name="no_of_contracts" required class="modern-input" min="0"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">Allocation</label><input type="number" name="allocation" required class="modern-input" min="0" step="0.01"></div>
                        <div><label class="modern-label">No. of PCR Prepared</label><input type="number" name="no_of_pcr_prepared" required class="modern-input" min="0"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">No. of PCR Submitted to Regional Office</label><input type="number" name="no_of_pcr_submitted_to_regional_office" required class="modern-input" min="0"></div>
                        <div><label class="modern-label">Accomplishment (%)</label><input type="number" name="accomplishment_percentage" required class="modern-input" min="0" max="100" step="0.01"></div>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                        <label class="modern-label" style="margin-bottom: 10px; display: block;">Remarks</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div><label class="modern-label">For Signing of IA, Chief, DM, RM</label><input type="number" name="for_signing_of_ia_chief_dm_rm" required class="modern-input" min="0"></div>
                            <div><label class="modern-label">For Submission to RO1</label><input type="number" name="for_submission_to_ro1" required class="modern-input" min="0"></div>
                            <div><label class="modern-label">Not Yet Prepared / Pending Details</label><input type="number" name="not_yet_prepared_pending_details" required class="modern-input" min="0"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" onclick="closePcrAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                        <button type="submit" class="modern-btn" style="flex: 1;">Save Data</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="pcrEditModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Edit PCR Status Data</h3>
                <form action="{{ route('pcr.status.update') }}" method="POST" data-async-target="#pcrStatusSection" data-async-close="#pcrEditModal" data-async-success-modal="#pcrSuccessModal">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="pcr-edit-id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">Fund Source</label><input type="text" name="fund_source" id="pcr-edit-fund_source" required class="modern-input"></div>
                        <div><label class="modern-label">No. of Contracts</label><input type="number" name="no_of_contracts" id="pcr-edit-no_of_contracts" required class="modern-input" min="0"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">Allocation</label><input type="number" name="allocation" id="pcr-edit-allocation" required class="modern-input" min="0" step="0.01"></div>
                        <div><label class="modern-label">No. of PCR Prepared</label><input type="number" name="no_of_pcr_prepared" id="pcr-edit-no_of_pcr_prepared" required class="modern-input" min="0"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label class="modern-label">No. of PCR Submitted to Regional Office</label><input type="number" name="no_of_pcr_submitted_to_regional_office" id="pcr-edit-no_of_pcr_submitted_to_regional_office" required class="modern-input" min="0"></div>
                        <div><label class="modern-label">Accomplishment (%)</label><input type="number" name="accomplishment_percentage" id="pcr-edit-accomplishment_percentage" required class="modern-input" min="0" max="100" step="0.01"></div>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                        <label class="modern-label" style="margin-bottom: 10px; display: block;">Remarks</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div><label class="modern-label">For Signing of IA, Chief, DM, RM</label><input type="number" name="for_signing_of_ia_chief_dm_rm" id="pcr-edit-for_signing" required class="modern-input" min="0"></div>
                            <div><label class="modern-label">For Submission to RO1</label><input type="number" name="for_submission_to_ro1" id="pcr-edit-for_submission" required class="modern-input" min="0"></div>
                            <div><label class="modern-label">Not Yet Prepared / Pending Details</label><input type="number" name="not_yet_prepared_pending_details" id="pcr-edit-pending" required class="modern-input" min="0"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" onclick="closePcrEditModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                        <button type="submit" class="modern-btn" style="flex: 1;">Update Data</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="pcrDeleteModal">
            <div class="modal-box">
                <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Delete PCR Status Data</h3>
                <p style="font-size: 14px; color: #475569; margin-bottom: 25px;">Are you sure you want to delete this record? This action cannot be undone.</p>
                <form id="pcrDeleteForm" method="POST" data-async-target="#pcrStatusSection" data-async-close="#pcrDeleteModal" data-async-success="silent">
                    @csrf
                    @method('DELETE')
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="closePcrDeleteModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                        <button type="submit" class="modern-btn" style="flex: 1; background: #ef4444;">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay {{ session('pcr_status_success') ? 'active' : '' }}" id="pcrSuccessModal">
            <div class="modal-box">
                <h3 data-success-title style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Success</h3>
                <p data-success-message style="font-size: 14px; color: #475569; margin-bottom: 25px;">{{ session('pcr_status_success', 'Saved successfully.') }}</p>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closePcrSuccessModal()" class="modern-btn" style="flex: 1;">OK</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        @include('partials.team-analytics-script', ['analytics' => $analytics ?? []])

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

        function openPcrAddModal() {
            document.getElementById('pcrAddModal').classList.add('active');
        }

        function closePcrAddModal() {
            document.getElementById('pcrAddModal').classList.remove('active');
        }

        function openPcrEditModal(id, fundSource, noOfContracts, allocation, noOfPcrPrepared, noOfPcrSubmitted, accomplishmentPercentage, forSigning, forSubmission, pending) {
            document.getElementById('pcr-edit-id').value = id;
            document.getElementById('pcr-edit-fund_source').value = fundSource;
            document.getElementById('pcr-edit-no_of_contracts').value = noOfContracts;
            document.getElementById('pcr-edit-allocation').value = allocation;
            document.getElementById('pcr-edit-no_of_pcr_prepared').value = noOfPcrPrepared;
            document.getElementById('pcr-edit-no_of_pcr_submitted_to_regional_office').value = noOfPcrSubmitted;
            document.getElementById('pcr-edit-accomplishment_percentage').value = accomplishmentPercentage;
            document.getElementById('pcr-edit-for_signing').value = forSigning;
            document.getElementById('pcr-edit-for_submission').value = forSubmission;
            document.getElementById('pcr-edit-pending').value = pending;
            document.getElementById('pcrEditModal').classList.add('active');
        }

        function closePcrEditModal() {
            document.getElementById('pcrEditModal').classList.remove('active');
        }

        function openPcrDeleteModal(id) {
            document.getElementById('pcrDeleteForm').action = `/pcr_team/status/delete/${id}`;
            document.getElementById('pcrDeleteModal').classList.add('active');
        }

        function closePcrDeleteModal() {
            document.getElementById('pcrDeleteModal').classList.remove('active');
        }

        function closePcrSuccessModal() {
            document.getElementById('pcrSuccessModal').classList.remove('active');
        }

        async function handlePcrExport(event, url) {
            event.preventDefault();

            const suggestedName = `PCR STATUS As Of ${new Date().toLocaleDateString('en-US', {
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
    </script>
@endsection
