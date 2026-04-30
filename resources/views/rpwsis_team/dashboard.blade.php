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
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .main-column,
        .side-column {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
            border: none;
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

        .toolbar-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-btn {
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s;
            text-decoration: none;
            line-height: 1.2;
        }

        .toolbar-btn.add {
            background: #2563eb;
            color: #ffffff;
        }

        .toolbar-btn.add:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .toolbar-btn.export {
            background: #16a34a;
            color: #ffffff;
        }

        .toolbar-btn.export:hover {
            background: #15803d;
            transform: translateY(-1px);
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
            padding: 24px;
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s;
        }

        .modal-box {
            width: 100%;
            max-width: 900px;
            background: #fff;
            margin: 0 auto;
            border-radius: 16px;
            padding: 30px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .rpwsis-modal-box {
            width: 100%;
            max-width: 900px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 16px;
            padding: 30px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .rpwsis-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .rpwsis-modal-title {
            margin: 0;
            font-size: 18px;
            color: #1e293b;
        }

        .rpwsis-modal-close {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #a1a1aa;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            outline: none;
            transition: 0.2s;
        }

        .rpwsis-modal-close:hover {
            color: #ef4444;
        }

        .modal-stack {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-section {
            background: #f9fafb;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e4e4e7;
        }

        .modal-section-title {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #0c4d05;
        }

        .modal-grid {
            display: grid;
            gap: 10px;
        }

        .modal-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .modal-grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .modal-grid.four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .modal-span-2 {
            grid-column: span 2;
        }

        .modal-span-3 {
            grid-column: span 3;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
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
        }

        .modern-input:focus {
            border-color: #0c4d05;
            box-shadow: 0 0 0 3px rgba(12, 77, 5, 0.1);
        }

        textarea.modern-input {
            resize: vertical;
            min-height: 60px;
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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            width: 100%;
            background: #fff;
            table-layout: fixed;
        }

        .custom-table thead th {
            font-size: 11px;
            color: #a0aec0;
            font-weight: 600;
            text-align: center;
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            vertical-align: middle;
            transition: 0.2s;
            line-height: 1.4;
        }

        .custom-table tbody td {
            padding: 15px;
            font-size: 12px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #475569;
            transition: 0.2s;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .custom-table tbody tr:hover td {
            background: #f8fafc;
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
            min-width: 110px;
            text-align: center;
        }

        .btn-delete,
        .table-delete-btn {
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
            min-width: 40px;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
        }

        .btn-delete:hover,
        .table-delete-btn:hover {
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

        .btn-spinner {
            width: 14px;
            height: 14px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: buttonSpin 0.7s linear infinite;
        }

        @keyframes buttonSpin {
            to {
                transform: rotate(360deg);
            }
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

        #simpleTable thead th.impl {
            min-width: 190px;
            max-width: 220px;
        }

        #simpleTable thead th.impl.col-remarks {
            min-width: 240px;
            max-width: 280px;
        }

        #simpleTable tbody td.impl.col-standard {
            min-width: 190px;
            max-width: 220px;
        }

        #simpleTable tbody td.impl.col-remarks {
            min-width: 240px;
            max-width: 280px;
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
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
            padding-bottom: 15px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
            background: #ffffff;
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

        @media (max-width: 1300px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .modal-grid.two,
            .modal-grid.three,
            .modal-grid.four {
                grid-template-columns: 1fr;
            }

            .modal-span-2,
            .modal-span-3 {
                grid-column: span 1;
            }

            .modal-actions {
                flex-direction: column;
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
                @include('partials.active-projects-table', [
                    'resolutions' => $resolutions ?? collect(),
                    'containerId' => 'activeProjectsContainer',
                    'editable' => auth()->check() && in_array(auth()->user()->role, ['rpwsis_team', 'admin']),
                    'updateRouteName' => 'rpwsis.resolutions.update_status',
                ])
            </div>

            @include('partials.team-analytics-card', ['analytics' => $analytics ?? []])
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
            A. ACCOMPLISHMENT OF SOCIAL AND ENVIRONMENTAL
            <div class="toolbar-actions">
                @if ($canManageRpwsis)
                    <button onclick="openModal()" class="toolbar-btn add">+ Add Data</button>
                @endif
                <button onclick="exportExcel()" class="toolbar-btn export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <div class="table-toolbar" data-client-table-toolbar>
            <label class="table-toolbar__search">
                <span class="table-toolbar__label">Search</span>
                <input type="search" id="simpleTableSearch" class="table-toolbar__input" placeholder="Search activity, NIS, batch, remarks...">
            </label>
            <div class="table-toolbar__actions">
                <button type="button" id="simpleTableApplyButton" class="table-toolbar__button table-toolbar__button--primary" onclick="applyClientTableFilters('simpleTable')">Apply</button>
                <button type="button" class="table-toolbar__button table-toolbar__button--ghost" onclick="resetClientTableFilters('simpleTable')">Reset</button>
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
                        <tr data-record="{{ e(json_encode([
                            'id' => $r->id,
                            'region' => $r->region,
                            'batch' => $r->batch,
                            'allocation' => $r->allocation,
                            'nis' => $r->nis,
                            'activity' => $r->activity,
                            'remarks' => $r->remarks,
                            'amount' => $r->amount,
                            'c1' => $r->c1,
                            'c2' => $r->c2,
                            'c3' => $r->c3,
                            'c4' => $r->c4,
                            'c5' => $r->c5,
                            'c6' => $r->c6,
                            'c7' => $r->c7,
                            'c8' => $r->c8,
                            'c9' => $r->c9,
                            'c10' => $r->c10,
                            'c11' => $r->c11,
                            'c12' => $r->c12,
                            'phy' => $r->phy,
                            'fin' => $r->fin,
                            'exp' => $r->exp,
                        ])) }}">
                            @foreach ($statusValues as $index => $value)
                                @php
                                    $colClass = 'col-standard';
                                    $displayValue = $value;
                                    if ($index === 4) {
                                        $colClass = 'col-activity';
                                    }
                                    if ($index === 5 || ($index >= 14 && $index <= 18)) {
                                        $colClass = 'col-remarks';
                                    }
                                    if ($index === 6) {
                                        $colClass = 'col-amount';
                                    }
                                    $displayPrefix = '';
                                    if (in_array($index, [6, 21], true) && $value !== null && $value !== '') {
                                        $displayPrefix = '&#8369;';
                                        $displayValue = number_format((float) $value, 2);
                                    }
                                @endphp
                                <td class="{{ $index >= 7 && $index <= 18 ? 'impl ' : '' }}{{ $colClass }} status-compact-cell"
                                    data-export-value="{{ $value }}">
                                    {!! !empty($displayValue)
                                        ? '<div class="expandable-cell' .
                                            (mb_strlen((string) $displayValue) <= 28 ? ' is-expanded' : '') .
                                            '">' .
                                            '<div class="expandable-preview">' .
                                            $displayPrefix .
                                            e(\Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $displayValue), 28)) .
                                            '</div>' .
                                            '<div class="expandable-full">' .
                                            $displayPrefix .
                                            nl2br(e($displayValue)) .
                                            '</div>' .
                                            (mb_strlen((string) $displayValue) > 28
                                                ? '<button type="button" class="expand-toggle" onclick="toggleSummaryCell(this)">Show more</button>'
                                                : '') .
                                            '</div>'
                                        : '-' !!}
                                </td>
                            @endforeach
                            @if ($canManageRpwsis)
                                <td class="col-action action-cell">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Record"
                                            onclick="openAccomplishmentEditModal(this)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button"
                                            onclick="openDeleteModal('accomplishment', {{ $r->id }}, this)"
                                            class="btn-delete" title="Delete Record">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="statusModal" class="modal-overlay">
                <div class="rpwsis-modal-box">
                    <div class="rpwsis-modal-header">
                        <h3 class="rpwsis-modal-title" id="statusModalTitle">Add Accomplishment</h3>
                        <button onclick="closeModal()" class="rpwsis-modal-close">&times;</button>
                    </div>

                    <div class="modal-stack">
                        <div class="modal-section">
                            <span class="modal-section-title">Project Information</span>
                            <div class="modal-grid three">
                                <input id="region" placeholder="Region" class="modern-input" maxlength="100" required>
                                <input id="batch" placeholder="Batch" class="modern-input" maxlength="100">
                                <input id="allocation" placeholder="Allocation" class="modern-input" maxlength="255">
                                <input id="nis" placeholder="NIS" class="modern-input" maxlength="255">
                                <input id="activity" placeholder="Activity Type" class="modern-input" maxlength="255" required>
                                <input id="remarks" placeholder="Remarks" class="modern-input" maxlength="1000">
                                <input id="amount" placeholder="Amount" class="modern-input" type="number" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Implementation Stage</span>
                            <div class="modal-grid four">
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

                        <div class="modal-section">
                            <span class="modal-section-title">Project Metrics</span>
                            <div class="modal-grid three">
                                <input id="phy" placeholder="PHY %" class="modern-input" type="number" min="0" max="100" step="0.01">
                                <input id="fin" placeholder="FIN %" class="modern-input" type="number" min="0" max="100" step="0.01">
                                <input id="exp" placeholder="Expenditures" class="modern-input" type="number" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button onclick="closeModal()" class="modern-btn modern-btn-outline" type="button">Cancel</button>
                            <button onclick="saveRecord(this)" class="modern-btn" id="statusModalActionBtn" type="button">Save Record</button>
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
            <div class="toolbar-actions" style="margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openSummaryModal()" class="toolbar-btn add">+ Add Data</button>
                @endif
                <button onclick="exportSummaryExcel()" class="toolbar-btn export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <div class="table-toolbar" data-client-table-toolbar>
            <label class="table-toolbar__search">
                <span class="table-toolbar__label">Search</span>
                <input type="search" id="summaryTableSearch" class="table-toolbar__input" placeholder="Search municipality, plantation type, NIS...">
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Province</span>
                <select id="summaryTableProvinceFilter" class="table-toolbar__select">
                    <option value="">All provinces</option>
                </select>
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Municipality</span>
                <select id="summaryTableMunicipalityFilter" class="table-toolbar__select">
                    <option value="">All municipalities</option>
                </select>
            </label>
            <div class="table-toolbar__actions">
                <button type="button" id="summaryTableApplyButton" class="table-toolbar__button table-toolbar__button--primary" onclick="applyClientTableFilters('summaryTable')">Apply</button>
                <button type="button" class="table-toolbar__button table-toolbar__button--ghost" onclick="resetClientTableFilters('summaryTable')">Reset</button>
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
                        <tr data-record="{{ e(json_encode([
                            'id' => $row->id,
                            'sum_region' => $row->region,
                            'sum_province' => $row->province,
                            'sum_municipality' => $row->municipality,
                            'sum_barangay' => $row->barangay,
                            'sum_type' => $row->plantation_type,
                            'sum_year' => $row->year_established,
                            'sum_target_1' => $row->target_area_1,
                            'sum_area_planted' => $row->area_planted,
                            'sum_species' => $row->species_planted,
                            'sum_spacing' => $row->spacing,
                            'sum_maintenance' => $row->maintenance,
                            'sum_target_2' => $row->target_area_2,
                            'sum_actual' => $row->actual_area,
                            'sum_mortality' => $row->mortality_rate,
                            'sum_replanted' => $row->species_replanted,
                            'sum_nis' => $row->nis_name,
                            'sum_remarks' => $row->remarks,
                        ])) }}">
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
                                <td class="col-action action-cell">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Record"
                                            onclick="openSummaryEditModal(this)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal('summary', {{ $row->id }}, this)"
                                            class="btn-delete" title="Delete Record">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="summaryModal" class="modal-overlay">
                <div class="rpwsis-modal-box">
                    <div class="rpwsis-modal-header">
                        <h3 class="rpwsis-modal-title" id="summaryModalTitle">Add Summary Record</h3>
                        <button onclick="closeSummaryModal()" class="rpwsis-modal-close">&times;</button>
                    </div>

                    <div class="modal-stack">
                        <div class="modal-section">
                            <span class="modal-section-title">Location Details</span>
                            <div class="modal-grid four">
                                <input id="sum_region" placeholder="Region" class="modern-input">
                                <input id="sum_province" placeholder="Province" class="modern-input">
                                <input id="sum_municipality" placeholder="Municipality" class="modern-input">
                                <input id="sum_barangay" placeholder="Barangay" class="modern-input">
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Plantation Info</span>
                            <div class="modal-grid three">
                                <input id="sum_type" placeholder="Type of Plantation" class="modern-input">
                                <input id="sum_year" placeholder="Year Established" class="modern-input">
                                <input id="sum_target_1" placeholder="Target Area" class="modern-input">
                                <input id="sum_area_planted" placeholder="Area Planted" class="modern-input">
                                <textarea id="sum_spacing" placeholder="Spacing (one per line)" class="modern-input"></textarea>
                                <input id="sum_maintenance" placeholder="1st Year M&P" class="modern-input">
                                <textarea id="sum_species" placeholder="Species & Number Planted (Use Enter for new lines)" class="modern-input modal-span-3"></textarea>
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Replanting Status & Extras</span>
                            <div class="modal-grid three">
                                <input id="sum_target_2" placeholder="Replanting Target Area" class="modern-input">
                                <input id="sum_actual" placeholder="Replanting Actual Area" class="modern-input">
                                <input id="sum_mortality" placeholder="Mortality Rate" class="modern-input">
                                <input id="sum_nis" placeholder="Name of NIS" class="modern-input">
                                <input id="sum_remarks" placeholder="Remarks" class="modern-input">
                                <div></div>
                                <textarea id="sum_replanted" placeholder="Species Replanted (Use Enter for new lines)" class="modern-input modal-span-3"></textarea>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button onclick="closeSummaryModal()" class="modern-btn modern-btn-outline" type="button">Cancel</button>
                            <button onclick="saveSummaryRecord(this)" class="modern-btn" id="summaryModalActionBtn" type="button">Save Record</button>
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
            <div class="toolbar-actions" style="margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openNurseryModal()" class="toolbar-btn add">+ Add Data</button>
                @endif
                <button onclick="exportNurseryExcel()" class="toolbar-btn export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <div class="table-toolbar" data-client-table-toolbar>
            <label class="table-toolbar__search">
                <span class="table-toolbar__label">Search</span>
                <input type="search" id="nurseryTableSearch" class="table-toolbar__input" placeholder="Search nursery, municipality, barangay, NIS...">
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Municipality</span>
                <select id="nurseryTableMunicipalityFilter" class="table-toolbar__select">
                    <option value="">All municipalities</option>
                </select>
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Type</span>
                <select id="nurseryTableTypeFilter" class="table-toolbar__select">
                    <option value="">All nursery types</option>
                </select>
            </label>
            <div class="table-toolbar__actions">
                <button type="button" id="nurseryTableApplyButton" class="table-toolbar__button table-toolbar__button--primary" onclick="applyClientTableFilters('nurseryTable')">Apply</button>
                <button type="button" class="table-toolbar__button table-toolbar__button--ghost" onclick="resetClientTableFilters('nurseryTable')">Reset</button>
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
                        <tr data-record="{{ e(json_encode([
                            'id' => $row->id,
                            'nur_region' => $row->region,
                            'nur_province' => $row->province,
                            'nur_municipality' => $row->municipality,
                            'nur_barangay' => $row->barangay,
                            'nur_x_coord' => $row->x_coordinates,
                            'nur_y_coord' => $row->y_coordinates,
                            'nur_seedlings' => $row->seedlings_produced,
                            'nur_type' => $row->nursery_type,
                            'nur_nis' => $row->nis_name,
                            'nur_remarks' => $row->remarks,
                        ])) }}">
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
                                <td class="col-action action-cell">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Record"
                                            onclick="openNurseryEditModal(this)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal('nursery', {{ $row->id }}, this)"
                                            class="btn-delete" title="Delete Record">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="nurseryModal" class="modal-overlay">
                <div class="rpwsis-modal-box">
                    <div class="rpwsis-modal-header">
                        <h3 class="rpwsis-modal-title" id="nurseryModalTitle">Add Nursery Record</h3>
                        <button onclick="closeNurseryModal()" class="rpwsis-modal-close">&times;</button>
                    </div>

                    <div class="modal-stack">
                        <div class="modal-section">
                            <span class="modal-section-title">Location Details</span>
                            <div class="modal-grid four">
                                <input id="nur_region" placeholder="Region" class="modern-input">
                                <input id="nur_province" placeholder="Province" class="modern-input">
                                <input id="nur_municipality" placeholder="Municipality" class="modern-input">
                                <input id="nur_barangay" placeholder="Barangay" class="modern-input">
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Nursery Info</span>
                            <div class="modal-grid three">
                                <input id="nur_x_coord" placeholder="X-Coordinates" class="modern-input">
                                <input id="nur_y_coord" placeholder="Y-Coordinates" class="modern-input">
                                <input id="nur_seedlings" placeholder="Number Seedlings Produced" class="modern-input">
                                <input id="nur_type" placeholder="Type of Nursery" class="modern-input">
                                <input id="nur_nis" placeholder="Name of NIS" class="modern-input">
                                <textarea id="nur_remarks" placeholder="Remarks" class="modern-input modal-span-3"></textarea>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button onclick="closeNurseryModal()" class="modern-btn modern-btn-outline" type="button">Cancel</button>
                            <button onclick="saveNurseryRecord(this)" class="modern-btn" id="nurseryModalActionBtn" type="button">Save Record</button>
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
            <div class="toolbar-actions" style="margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openSignagesModal()" class="toolbar-btn add">+ Add Data</button>
                @endif
                <button onclick="exportSignagesExcel()" class="toolbar-btn export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <div class="table-toolbar" data-client-table-toolbar>
            <label class="table-toolbar__search">
                <span class="table-toolbar__label">Search</span>
                <input type="search" id="signagesTableSearch" class="table-toolbar__input" placeholder="Search signages, municipality, barangay, NIS...">
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Municipality</span>
                <select id="signagesTableMunicipalityFilter" class="table-toolbar__select">
                    <option value="">All municipalities</option>
                </select>
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Type</span>
                <select id="signagesTableTypeFilter" class="table-toolbar__select">
                    <option value="">All signage types</option>
                </select>
            </label>
            <div class="table-toolbar__actions">
                <button type="button" id="signagesTableApplyButton" class="table-toolbar__button table-toolbar__button--primary" onclick="applyClientTableFilters('signagesTable')">Apply</button>
                <button type="button" class="table-toolbar__button table-toolbar__button--ghost" onclick="resetClientTableFilters('signagesTable')">Reset</button>
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
                        <tr data-record="{{ e(json_encode([
                            'id' => $row->id,
                            'sig_region' => $row->region,
                            'sig_province' => $row->province,
                            'sig_municipality' => $row->municipality,
                            'sig_barangay' => $row->barangay,
                            'sig_x_coord' => $row->x_coordinates,
                            'sig_y_coord' => $row->y_coordinates,
                            'sig_type' => $row->signage_type,
                            'sig_nis' => $row->nis_name,
                            'sig_remarks' => $row->remarks,
                        ])) }}">
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
                                <td class="col-action action-cell">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Record"
                                            onclick="openSignagesEditModal(this)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal('signages', {{ $row->id }}, this)"
                                            class="btn-delete" title="Delete Record">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="signagesModal" class="modal-overlay">
                <div class="rpwsis-modal-box">
                    <div class="rpwsis-modal-header">
                        <h3 class="rpwsis-modal-title" id="signagesModalTitle">Add Signage Record</h3>
                        <button onclick="closeSignagesModal()" class="rpwsis-modal-close">&times;</button>
                    </div>

                    <div class="modal-stack">
                        <div class="modal-section">
                            <span class="modal-section-title">Location Details</span>
                            <div class="modal-grid four">
                                <input id="sig_region" placeholder="Region" class="modern-input">
                                <input id="sig_province" placeholder="Province" class="modern-input">
                                <input id="sig_municipality" placeholder="Municipality" class="modern-input">
                                <textarea id="sig_barangay" placeholder="Barangay (Use Enter for multiple)" class="modern-input"></textarea>
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Signage Info</span>
                            <div class="modal-grid two">
                                <textarea id="sig_x_coord" placeholder="X-Coordinates (Use Enter for multiple)" class="modern-input"></textarea>
                                <textarea id="sig_y_coord" placeholder="Y-Coordinates (Use Enter for multiple)" class="modern-input"></textarea>
                                <textarea id="sig_type" placeholder="Type of Signages (Use Enter for multiple)" class="modern-input"></textarea>
                                <input id="sig_nis" placeholder="Name of NIS" class="modern-input">
                                <textarea id="sig_remarks" placeholder="Remarks" class="modern-input modal-span-2"></textarea>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button onclick="closeSignagesModal()" class="modern-btn modern-btn-outline" type="button">Cancel</button>
                            <button onclick="saveSignagesRecord(this)" class="modern-btn" id="signagesModalActionBtn" type="button">Save Record</button>
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
            <div class="toolbar-actions" style="margin-top: 12px;">
                @if ($canManageRpwsis)
                    <button onclick="openInfrastructureModal()" class="toolbar-btn add">+ Add Data</button>
                @endif
                <button onclick="exportInfrastructureExcel()" class="toolbar-btn export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <div class="table-toolbar" data-client-table-toolbar>
            <label class="table-toolbar__search">
                <span class="table-toolbar__label">Search</span>
                <input type="search" id="infrastructureTableSearch" class="table-toolbar__input" placeholder="Search infrastructure, municipality, barangay, NIS...">
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Municipality</span>
                <select id="infrastructureTableMunicipalityFilter" class="table-toolbar__select">
                    <option value="">All municipalities</option>
                </select>
            </label>
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">Type</span>
                <select id="infrastructureTableTypeFilter" class="table-toolbar__select">
                    <option value="">All infrastructure types</option>
                </select>
            </label>
            <div class="table-toolbar__actions">
                <button type="button" id="infrastructureTableApplyButton" class="table-toolbar__button table-toolbar__button--primary" onclick="applyClientTableFilters('infrastructureTable')">Apply</button>
                <button type="button" class="table-toolbar__button table-toolbar__button--ghost" onclick="resetClientTableFilters('infrastructureTable')">Reset</button>
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
                        <tr data-record="{{ e(json_encode([
                            'id' => $row->id,
                            'inf_region' => $row->region,
                            'inf_province' => $row->province,
                            'inf_municipality' => $row->municipality,
                            'inf_barangay' => $row->barangay,
                            'inf_x_coord' => $row->x_coordinates,
                            'inf_y_coord' => $row->y_coordinates,
                            'inf_type' => $row->infrastructure_type,
                            'inf_nis' => $row->nis_name,
                            'inf_remarks' => $row->remarks,
                        ])) }}">
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
                                <td class="col-action action-cell">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit-icon" title="Edit Record"
                                            onclick="openInfrastructureEditModal(this)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal('infrastructure', {{ $row->id }}, this)"
                                            class="btn-delete" title="Delete Record">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManageRpwsis)
            <div id="infrastructureModal" class="modal-overlay">
                <div class="rpwsis-modal-box">
                    <div class="rpwsis-modal-header">
                        <h3 class="rpwsis-modal-title" id="infrastructureModalTitle">Add Infrastructure Record</h3>
                        <button onclick="closeInfrastructureModal()" class="rpwsis-modal-close">&times;</button>
                    </div>

                    <div class="modal-stack">
                        <div class="modal-section">
                            <span class="modal-section-title">Location Details</span>
                            <div class="modal-grid four">
                                <input id="inf_region" placeholder="Region" class="modern-input">
                                <input id="inf_province" placeholder="Province" class="modern-input">
                                <input id="inf_municipality" placeholder="Municipality" class="modern-input">
                                <textarea id="inf_barangay" placeholder="Barangay (Use Enter for multiple)" class="modern-input"></textarea>
                            </div>
                        </div>

                        <div class="modal-section">
                            <span class="modal-section-title">Infrastructure Info</span>
                            <div class="modal-grid two">
                                <textarea id="inf_x_coord" placeholder="X-Coordinates (Use Enter for multiple)" class="modern-input"></textarea>
                                <textarea id="inf_y_coord" placeholder="Y-Coordinates (Use Enter for multiple)" class="modern-input"></textarea>
                                <textarea id="inf_type" placeholder="Type of Infrastructure (Use Enter for multiple)" class="modern-input"></textarea>
                                <input id="inf_nis" placeholder="Name of NIS" class="modern-input">
                                <textarea id="inf_remarks" placeholder="Remarks" class="modern-input modal-span-2"></textarea>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button onclick="closeInfrastructureModal()" class="modern-btn modern-btn-outline" type="button">Cancel</button>
                            <button onclick="saveInfrastructureRecord(this)" class="modern-btn" id="infrastructureModalActionBtn" type="button">Save Record</button>
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

        <div class="modal-overlay" id="rpwsisSuccessModal">
            <div class="modal-box">
                <h3 data-success-title style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Success</h3>
                <p data-success-message style="font-size: 14px; color: #475569; margin-bottom: 25px;">Saved successfully.</p>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeRpwsisSuccessModal()" class="modern-btn" style="flex: 1;">OK</button>
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

        function formatMoneyValue(value) {
            const text = String(value ?? '').trim();
            if (!text) return '';

            const number = Number(text.replace(/,/g, ''));
            if (!Number.isFinite(number)) return text;

            return '\u20B1' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(number);
        }

        function renderStatusExpandableCell(value, extraClass = '', exportRawValue = value) {
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
            const exportValue = escapeSummaryHtml(String(exportRawValue ?? '').trim());

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
            if (!button.dataset.originalText && !button.dataset.originalHtml) {
                button.dataset.originalText = button.textContent.trim();
                button.dataset.originalHtml = button.innerHTML;
            }
            button.disabled = isLoading;
            button.style.opacity = isLoading ? '0.7' : '1';
            button.style.cursor = isLoading ? 'not-allowed' : 'pointer';
            const iconButton = button.classList.contains('btn-edit-icon') || button.classList.contains('btn-delete');
            if (iconButton) {
                button.innerHTML = isLoading ? '<span class="btn-spinner"></span>' : button.dataset.originalHtml;
            } else {
                button.textContent = isLoading ? loadingText : button.dataset.originalText;
            }
        }

        async function parseJsonResponse(res, fallbackMessage) {
            const contentType = res.headers.get('content-type') || '';
            const payload = contentType.includes('application/json')
                ? await res.json().catch(() => null)
                : null;

            if (!res.ok || !payload) {
                const validationMessage = payload?.message || Object.values(payload?.errors || {}).flat()[0];
                throw new Error(validationMessage || fallbackMessage);
            }

            if (payload.success === false) {
                throw new Error(payload.message || fallbackMessage);
            }

            return payload;
        }

        function showRpwsisSuccessModal(message, title = 'Success') {
            if (typeof window.openAsyncSuccessModal === 'function') {
                const opened = window.openAsyncSuccessModal('#rpwsisSuccessModal', message, title);
                if (opened) return;
            }

            if (typeof window.showLiveAlert === 'function') {
                window.showLiveAlert(message, 'success');
                return;
            }

            alert(message);
        }

        function closeRpwsisSuccessModal() {
            const modal = document.getElementById('rpwsisSuccessModal');
            if (modal) modal.classList.remove('active');
        }

        const modalConfig = {
            accomplishment: {
                modalId: 'statusModal',
                titleId: 'statusModalTitle',
                actionBtnId: 'statusModalActionBtn',
                addTitle: 'Add Accomplishment',
                editTitle: 'Edit Accomplishment',
                addText: 'Save Data',
                editText: 'Update Data',
                fields: ['region', 'batch', 'allocation', 'nis', 'activity', 'remarks', 'amount', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12', 'phy', 'fin', 'exp'],
            },
            summary: {
                modalId: 'summaryModal',
                titleId: 'summaryModalTitle',
                actionBtnId: 'summaryModalActionBtn',
                addTitle: 'Add Summary Record',
                editTitle: 'Edit Summary Record',
                addText: 'Save Data',
                editText: 'Update Data',
                fields: ['sum_region', 'sum_province', 'sum_municipality', 'sum_barangay', 'sum_type', 'sum_year', 'sum_target_1', 'sum_area_planted', 'sum_species', 'sum_spacing', 'sum_maintenance', 'sum_target_2', 'sum_actual', 'sum_mortality', 'sum_replanted', 'sum_nis', 'sum_remarks'],
            },
            nursery: {
                modalId: 'nurseryModal',
                titleId: 'nurseryModalTitle',
                actionBtnId: 'nurseryModalActionBtn',
                addTitle: 'Add Nursery Record',
                editTitle: 'Edit Nursery Record',
                addText: 'Save Data',
                editText: 'Update Data',
                fields: ['nur_region', 'nur_province', 'nur_municipality', 'nur_barangay', 'nur_x_coord', 'nur_y_coord', 'nur_seedlings', 'nur_type', 'nur_nis', 'nur_remarks'],
            },
            signages: {
                modalId: 'signagesModal',
                titleId: 'signagesModalTitle',
                actionBtnId: 'signagesModalActionBtn',
                addTitle: 'Add Signage Record',
                editTitle: 'Edit Signage Record',
                addText: 'Save Data',
                editText: 'Update Data',
                fields: ['sig_region', 'sig_province', 'sig_municipality', 'sig_barangay', 'sig_x_coord', 'sig_y_coord', 'sig_type', 'sig_nis', 'sig_remarks'],
            },
            infrastructure: {
                modalId: 'infrastructureModal',
                titleId: 'infrastructureModalTitle',
                actionBtnId: 'infrastructureModalActionBtn',
                addTitle: 'Add Infrastructure Record',
                editTitle: 'Edit Infrastructure Record',
                addText: 'Save Data',
                editText: 'Update Data',
                fields: ['inf_region', 'inf_province', 'inf_municipality', 'inf_barangay', 'inf_x_coord', 'inf_y_coord', 'inf_type', 'inf_nis', 'inf_remarks'],
            }
        };

        const currentEditState = {
            accomplishment: null,
            summary: null,
            nursery: null,
            signages: null,
            infrastructure: null,
        };

        function resetModalForm(type) {
            const config = modalConfig[type];
            if (!config) return;
            config.fields.forEach(id => {
                const field = document.getElementById(id);
                if (field) {
                    field.value = '';
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }
            });
        }

        function configureModalForMode(type, mode = 'add') {
            const config = modalConfig[type];
            if (!config) return;
            const title = document.getElementById(config.titleId);
            const actionBtn = document.getElementById(config.actionBtnId);
            if (title) title.textContent = mode === 'edit' ? config.editTitle : config.addTitle;
            if (actionBtn) {
                actionBtn.textContent = mode === 'edit' ? config.editText : config.addText;
                actionBtn.disabled = false;
                actionBtn.style.opacity = '1';
                actionBtn.style.cursor = 'pointer';
                actionBtn.dataset.originalText = mode === 'edit' ? config.editText : config.addText;
                actionBtn.dataset.originalHtml = mode === 'edit' ? config.editText : config.addText;
            }
        }

        function showModal(type) {
            const config = modalConfig[type];
            const modal = config ? document.getElementById(config.modalId) : null;
            if (modal) modal.classList.add('active');
        }

        function hideModal(type) {
            const config = modalConfig[type];
            const modal = config ? document.getElementById(config.modalId) : null;
            if (modal) modal.classList.remove('active');
            currentEditState[type] = null;
            configureModalForMode(type, 'add');
            resetModalForm(type);
        }

        function populateModalFields(type, record) {
            const config = modalConfig[type];
            if (!config) return;
            config.fields.forEach(id => {
                const field = document.getElementById(id);
                if (field) field.value = record?.[id] ?? '';
            });
        }

        function getRowRecord(button) {
            const row = button?.closest('tr') || button?.closest('[data-record]');
            const rawRecord = row?.dataset?.record || row?.getAttribute?.('data-record');
            if (!rawRecord) return null;

            try {
                return {
                    row,
                    record: JSON.parse(rawRecord)
                };
            } catch (error) {
                try {
                    const decoder = document.createElement('textarea');
                    decoder.innerHTML = rawRecord;

                    return {
                        row,
                        record: JSON.parse(decoder.value)
                    };
                } catch (nestedError) {
                    return null;
                }
            }
        }

        function openEditModal(type, button) {
            const payload = getRowRecord(button);
            if (!payload?.record?.id) {
                if (typeof window.showLiveAlert === 'function') {
                    window.showLiveAlert('Unable to open the edit modal for this record.', 'error');
                } else {
                    alert('Unable to open the edit modal for this record.');
                }
                return;
            }
            currentEditState[type] = {
                id: payload.record.id,
                row: payload.row,
            };
            configureModalForMode(type, 'edit');
            resetModalForm(type);
            populateModalFields(type, payload.record);
            showModal(type);
        }

        function openModal() {
            currentEditState.accomplishment = null;
            resetModalForm('accomplishment');
            configureModalForMode('accomplishment', 'add');
            showModal('accomplishment');
        }

        function closeModal() {
            hideModal('accomplishment');
        }

        function openSummaryModal() {
            currentEditState.summary = null;
            resetModalForm('summary');
            configureModalForMode('summary', 'add');
            showModal('summary');
        }

        function closeSummaryModal() {
            hideModal('summary');
        }

        function openNurseryModal() {
            currentEditState.nursery = null;
            resetModalForm('nursery');
            configureModalForMode('nursery', 'add');
            showModal('nursery');
        }

        function closeNurseryModal() {
            hideModal('nursery');
        }

        function openSignagesModal() {
            currentEditState.signages = null;
            resetModalForm('signages');
            configureModalForMode('signages', 'add');
            showModal('signages');
        }

        function closeSignagesModal() {
            hideModal('signages');
        }

        function openInfrastructureModal() {
            currentEditState.infrastructure = null;
            resetModalForm('infrastructure');
            configureModalForMode('infrastructure', 'add');
            showModal('infrastructure');
        }

        function closeInfrastructureModal() {
            hideModal('infrastructure');
        }

        function openAccomplishmentEditModal(button) {
            openEditModal('accomplishment', button);
        }

        function openSummaryEditModal(button) {
            openEditModal('summary', button);
        }

        function openNurseryEditModal(button) {
            openEditModal('nursery', button);
        }

        function openSignagesEditModal(button) {
            openEditModal('signages', button);
        }

        function openInfrastructureEditModal(button) {
            openEditModal('infrastructure', button);
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

        function getDeleteConfirmButton() {
            return document.getElementById('confirmDeleteBtn');
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

        function escapeAttribute(value) {
            return escapeSummaryHtml(value).replace(/"/g, '&quot;');
        }

        function serializeRecord(record) {
            return escapeAttribute(JSON.stringify(record ?? {}));
        }

        function formatText(value) {
            return value ? String(value).replace(/\n/g, '<br>') : '-';
        }

        function renderActionButtons(type, id) {
            return `@if ($canManageRpwsis)
                <td class="col-action action-cell">
                    <div class="action-buttons">
                        <button type="button" class="btn-edit-icon" title="Edit Record" onclick="open${type}EditModal(this)">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                            Edit
                        </button>
                        <button type="button" class="btn-delete" title="Delete Record" onclick="openDeleteModal('${type.toLowerCase()}', ${id}, this)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            @endif`;
        }

        function renderAccomplishmentRow(record) {
            const rowValues = [
                record.region, record.batch, record.allocation, record.nis, record.activity, record.remarks, record.amount,
                record.c1, record.c2, record.c3, record.c4, record.c5, record.c6, record.c7, record.c8, record.c9, record.c10,
                record.c11, record.c12, record.phy, record.fin, record.exp
            ];

            const renderedCells = rowValues.map((value, index) => {
                let colClass = 'col-standard';
                if (index === 4) colClass = 'col-activity';
                if (index === 5 || (index >= 14 && index <= 18)) colClass = 'col-remarks';
                if (index === 6) colClass = 'col-amount';
                const className = `${(index >= 7 && index <= 18) ? 'impl ' : ''}${colClass} status-compact-cell`.trim();
                const displayValue = [6, 21].includes(index) ? formatMoneyValue(value) : value;
                return renderStatusExpandableCell(displayValue, className, value);
            }).join('');

            return `<tr data-record="${serializeRecord(record)}">${renderedCells}${renderActionButtons('Accomplishment', record.id)}</tr>`;
        }

        function renderSummaryRow(record) {
            return `<tr data-record="${serializeRecord({
                id: record.id,
                sum_region: record.region,
                sum_province: record.province,
                sum_municipality: record.municipality,
                sum_barangay: record.barangay,
                sum_type: record.plantation_type,
                sum_year: record.year_established,
                sum_target_1: record.target_area_1,
                sum_area_planted: record.area_planted,
                sum_species: record.species_planted,
                sum_spacing: record.spacing,
                sum_maintenance: record.maintenance,
                sum_target_2: record.target_area_2,
                sum_actual: record.actual_area,
                sum_mortality: record.mortality_rate,
                sum_replanted: record.species_replanted,
                sum_nis: record.nis_name,
                sum_remarks: record.remarks,
            })}">
                <td class="col-standard">${record.region || '-'}</td>
                <td class="col-standard">${record.province || '-'}</td>
                <td class="col-standard">${record.municipality || '-'}</td>
                <td class="col-standard">${record.barangay || '-'}</td>
                <td class="col-medium">${record.plantation_type || '-'}</td>
                <td class="col-standard">${record.year_established || '-'}</td>
                <td class="col-standard">${record.target_area_1 || '-'}</td>
                <td class="col-standard">${record.area_planted || '-'}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(record.species_planted || '')}">${renderExpandableSummaryCell(record.species_planted, 60)}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(record.spacing || '')}">${renderExpandableSummaryCell(record.spacing, 45)}</td>
                <td class="col-medium">${record.maintenance || '-'}</td>
                <td class="col-standard">${record.target_area_2 || '-'}</td>
                <td class="col-standard">${record.actual_area || '-'}</td>
                <td class="col-standard">${record.mortality_rate || '-'}</td>
                <td class="col-expandable" data-export-value="${escapeSummaryHtml(record.species_replanted || '')}">${renderExpandableSummaryCell(record.species_replanted, 60)}</td>
                <td class="col-medium">${record.nis_name || '-'}</td>
                <td class="col-medium">${record.remarks || '-'}</td>
                ${renderActionButtons('Summary', record.id)}
            </tr>`;
        }

        function renderNurseryRow(record) {
            return `<tr data-record="${serializeRecord({
                id: record.id,
                nur_region: record.region,
                nur_province: record.province,
                nur_municipality: record.municipality,
                nur_barangay: record.barangay,
                nur_x_coord: record.x_coordinates,
                nur_y_coord: record.y_coordinates,
                nur_seedlings: record.seedlings_produced,
                nur_type: record.nursery_type,
                nur_nis: record.nis_name,
                nur_remarks: record.remarks,
            })}">
                <td class="col-standard">${record.region || '-'}</td>
                <td class="col-standard">${record.province || '-'}</td>
                <td class="col-standard">${record.municipality || '-'}</td>
                <td class="col-standard">${record.barangay || '-'}</td>
                <td class="col-medium">${record.x_coordinates || '-'}</td>
                <td class="col-medium">${record.y_coordinates || '-'}</td>
                <td class="col-medium">${record.seedlings_produced || '-'}</td>
                <td class="col-medium">${record.nursery_type || '-'}</td>
                <td class="col-medium">${record.nis_name || '-'}</td>
                <td class="col-wide">${formatText(record.remarks)}</td>
                ${renderActionButtons('Nursery', record.id)}
            </tr>`;
        }

        function renderSignagesRow(record) {
            return `<tr data-record="${serializeRecord({
                id: record.id,
                sig_region: record.region,
                sig_province: record.province,
                sig_municipality: record.municipality,
                sig_barangay: record.barangay,
                sig_x_coord: record.x_coordinates,
                sig_y_coord: record.y_coordinates,
                sig_type: record.signage_type,
                sig_nis: record.nis_name,
                sig_remarks: record.remarks,
            })}">
                <td class="col-standard">${record.region || '-'}</td>
                <td class="col-standard">${record.province || '-'}</td>
                <td class="col-standard">${record.municipality || '-'}</td>
                <td class="col-standard">${formatText(record.barangay)}</td>
                <td class="col-medium">${formatText(record.x_coordinates)}</td>
                <td class="col-medium">${formatText(record.y_coordinates)}</td>
                <td class="col-medium">${formatText(record.signage_type)}</td>
                <td class="col-medium">${record.nis_name || '-'}</td>
                <td class="col-wide">${formatText(record.remarks)}</td>
                ${renderActionButtons('Signages', record.id)}
            </tr>`;
        }

        function renderInfrastructureRow(record) {
            return `<tr data-record="${serializeRecord({
                id: record.id,
                inf_region: record.region,
                inf_province: record.province,
                inf_municipality: record.municipality,
                inf_barangay: record.barangay,
                inf_x_coord: record.x_coordinates,
                inf_y_coord: record.y_coordinates,
                inf_type: record.infrastructure_type,
                inf_nis: record.nis_name,
                inf_remarks: record.remarks,
            })}">
                <td class="col-standard">${record.region || '-'}</td>
                <td class="col-standard">${record.province || '-'}</td>
                <td class="col-standard">${record.municipality || '-'}</td>
                <td class="col-standard">${formatText(record.barangay)}</td>
                <td class="col-medium">${formatText(record.x_coordinates)}</td>
                <td class="col-medium">${formatText(record.y_coordinates)}</td>
                <td class="col-medium">${formatText(record.infrastructure_type)}</td>
                <td class="col-medium">${record.nis_name || '-'}</td>
                <td class="col-wide">${formatText(record.remarks)}</td>
                ${renderActionButtons('Infrastructure', record.id)}
            </tr>`;
        }

        function insertOrReplaceRow(type, record) {
            const renderers = {
                accomplishment: {
                    bodyId: 'tableBody',
                    render: renderAccomplishmentRow,
                    tableId: 'simpleTable',
                },
                summary: {
                    bodyId: 'summaryTableBody',
                    render: renderSummaryRow,
                    tableId: 'summaryTable',
                },
                nursery: {
                    bodyId: 'nurseryTableBody',
                    render: renderNurseryRow,
                    tableId: 'nurseryTable',
                },
                signages: {
                    bodyId: 'signagesTableBody',
                    render: renderSignagesRow,
                    tableId: 'signagesTable',
                },
                infrastructure: {
                    bodyId: 'infrastructureTableBody',
                    render: renderInfrastructureRow,
                    tableId: 'infrastructureTable',
                }
            };

            const config = renderers[type];
            if (!config) return;
            const html = config.render(record);
            const editState = currentEditState[type];
            if (editState?.row) {
                editState.row.outerHTML = html;
            } else {
                document.getElementById(config.bodyId).insertAdjacentHTML('beforeend', html);
            }

            refreshClientTableFilters(config.tableId);
        }

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

            const editState = currentEditState.accomplishment;
            const url = editState ? `/rpwsis_team/accomplishments/${editState.id}/update` : '/rpwsis_team/accomplishments/store';
            const method = editState ? 'PUT' : 'POST';
            setButtonLoading(button, true, editState ? 'Updating Data...' : 'Saving Data...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(editState ? 'Updating data...' : 'Saving data...');
            }

            fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => parseJsonResponse(res, `Unable to ${editState ? 'update' : 'save'} the record right now.`))
                .then(payload => {
                    const res = payload.record || payload;
                    insertOrReplaceRow('accomplishment', res);
                    closeModal();
                    showRpwsisSuccessModal(payload.message || (editState ? 'Updated successfully.' : 'Added successfully.'));
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || `Unable to ${editState ? 'update' : 'save'} the record. Please try again.`, 'error');
                    } else {
                        alert(error.message || `Unable to ${editState ? 'update' : 'save'} the record. Please try again.`);
                    }
                })
                .finally(() => {
                    setButtonLoading(button, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
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

            if (!validateRequiredFields([
                'sum_region', 'sum_province', 'sum_municipality', 'sum_barangay', 'sum_type', 'sum_year'
            ])) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            const editState = currentEditState.summary;
            const url = editState ? `/rpwsis_team/summary/${editState.id}/update` : '/rpwsis_team/summary/store';
            const method = editState ? 'PUT' : 'POST';
            setButtonLoading(button, true, editState ? 'Updating Data...' : 'Saving Data...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(editState ? 'Updating data...' : 'Saving data...');
            }

            fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => parseJsonResponse(res, `Unable to ${editState ? 'update' : 'save'} the summary record right now.`))
                .then(payload => {
                    const res = payload.record || payload;
                    insertOrReplaceRow('summary', res);
                    closeSummaryModal();
                    showRpwsisSuccessModal(payload.message || (editState ? 'Updated successfully.' : 'Added successfully.'));
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || `Unable to ${editState ? 'update' : 'save'} the summary record. Please try again.`, 'error');
                    } else {
                        alert(error.message || `Unable to ${editState ? 'update' : 'save'} the summary record. Please try again.`);
                    }
                })
                .finally(() => {
                    setButtonLoading(button, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        // ======================= SAVE NURSERY RECORD (THIRD TABLE) =======================
        function saveNurseryRecord(button = null) {
            const fields = [
                'nur_region', 'nur_province', 'nur_municipality', 'nur_barangay',
                'nur_x_coord', 'nur_y_coord', 'nur_seedlings', 'nur_type', 'nur_nis', 'nur_remarks'
            ];

            if (!validateRequiredFields([
                'nur_region', 'nur_province', 'nur_municipality', 'nur_barangay', 'nur_type'
            ])) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            const editState = currentEditState.nursery;
            const url = editState ? `/rpwsis_team/nursery/${editState.id}/update` : '/rpwsis_team/nursery/store';
            const method = editState ? 'PUT' : 'POST';
            setButtonLoading(button, true, editState ? 'Updating Data...' : 'Saving Data...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(editState ? 'Updating data...' : 'Saving data...');
            }

            fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => parseJsonResponse(res, `Unable to ${editState ? 'update' : 'save'} the nursery record right now.`))
                .then(payload => {
                    const res = payload.record || payload;
                    insertOrReplaceRow('nursery', res);
                    closeNurseryModal();
                    showRpwsisSuccessModal(payload.message || (editState ? 'Updated successfully.' : 'Added successfully.'));
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || `Unable to ${editState ? 'update' : 'save'} the nursery record. Please try again.`, 'error');
                    } else {
                        alert(error.message || `Unable to ${editState ? 'update' : 'save'} the nursery record. Please try again.`);
                    }
                })
                .finally(() => {
                    setButtonLoading(button, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        // ======================= SAVE SIGNAGES RECORD (FOURTH TABLE) =======================
        function saveSignagesRecord(button = null) {
            const fields = [
                'sig_region', 'sig_province', 'sig_municipality', 'sig_barangay',
                'sig_x_coord', 'sig_y_coord', 'sig_type', 'sig_nis', 'sig_remarks'
            ];

            if (!validateRequiredFields([
                'sig_region', 'sig_province', 'sig_municipality', 'sig_barangay', 'sig_type'
            ])) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            const editState = currentEditState.signages;
            const url = editState ? `/rpwsis_team/signages/${editState.id}/update` : '/rpwsis_team/signages/store';
            const method = editState ? 'PUT' : 'POST';
            setButtonLoading(button, true, editState ? 'Updating Data...' : 'Saving Data...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(editState ? 'Updating data...' : 'Saving data...');
            }

            fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => parseJsonResponse(res, `Unable to ${editState ? 'update' : 'save'} the signage record right now.`))
                .then(payload => {
                    const res = payload.record || payload;
                    insertOrReplaceRow('signages', res);
                    closeSignagesModal();
                    showRpwsisSuccessModal(payload.message || (editState ? 'Updated successfully.' : 'Added successfully.'));
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || `Unable to ${editState ? 'update' : 'save'} the signage record. Please try again.`, 'error');
                    } else {
                        alert(error.message || `Unable to ${editState ? 'update' : 'save'} the signage record. Please try again.`);
                    }
                })
                .finally(() => {
                    setButtonLoading(button, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        // ======================= SAVE INFRASTRUCTURE RECORD (FIFTH TABLE) =======================
        function saveInfrastructureRecord(button = null) {
            const fields = [
                'inf_region', 'inf_province', 'inf_municipality', 'inf_barangay',
                'inf_x_coord', 'inf_y_coord', 'inf_type', 'inf_nis', 'inf_remarks'
            ];

            if (!validateRequiredFields([
                'inf_region', 'inf_province', 'inf_municipality', 'inf_barangay', 'inf_type'
            ])) return;

            const data = {};
            fields.forEach(id => {
                const field = document.getElementById(id);
                data[id] = field ? field.value.trim() : '';
            });

            const editState = currentEditState.infrastructure;
            const url = editState ? `/rpwsis_team/infrastructure/${editState.id}/update` : '/rpwsis_team/infrastructure/store';
            const method = editState ? 'PUT' : 'POST';
            setButtonLoading(button, true, editState ? 'Updating Data...' : 'Saving Data...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(editState ? 'Updating data...' : 'Saving data...');
            }

            fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => parseJsonResponse(res, `Unable to ${editState ? 'update' : 'save'} the infrastructure record right now.`))
                .then(payload => {
                    const res = payload.record || payload;
                    insertOrReplaceRow('infrastructure', res);
                    closeInfrastructureModal();
                    showRpwsisSuccessModal(payload.message || (editState ? 'Updated successfully.' : 'Added successfully.'));
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || `Unable to ${editState ? 'update' : 'save'} the infrastructure record. Please try again.`, 'error');
                    } else {
                        alert(error.message || `Unable to ${editState ? 'update' : 'save'} the infrastructure record. Please try again.`);
                    }
                })
                .finally(() => {
                    setButtonLoading(button, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }


        // ======================= DELETING =======================
        function deleteAccomplishment(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('accomplishment', id, btn);
                return;
            }
            const confirmButton = getDeleteConfirmButton();
            setButtonLoading(btn, true, 'Deleting...');
            setButtonLoading(confirmButton, true, 'Deleting...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader('Deleting record...');
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
                    if (!res.ok || !payload.success) throw new Error(payload.message ||
                        'Failed to delete the record.');
                    return payload;
                })
                .then(data => {
                    btn.closest('tr').remove();
                    refreshClientTableFilters('simpleTable');
                    closeDeleteModal();
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert(error.message || 'An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    setButtonLoading(btn, false);
                    setButtonLoading(confirmButton, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        function deleteSummaryRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('summary', id, btn);
                return;
            }
            const confirmButton = getDeleteConfirmButton();
            setButtonLoading(btn, true, 'Deleting...');
            setButtonLoading(confirmButton, true, 'Deleting...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader('Deleting record...');
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
                        refreshClientTableFilters('summaryTable');
                        closeDeleteModal();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert('An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    setButtonLoading(btn, false);
                    setButtonLoading(confirmButton, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        function deleteNurseryRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('nursery', id, btn);
                return;
            }
            const confirmButton = getDeleteConfirmButton();
            setButtonLoading(btn, true, 'Deleting...');
            setButtonLoading(confirmButton, true, 'Deleting...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader('Deleting record...');
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
                        refreshClientTableFilters('nurseryTable');
                        closeDeleteModal();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert('An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    setButtonLoading(btn, false);
                    setButtonLoading(confirmButton, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        function deleteSignagesRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('signages', id, btn);
                return;
            }
            const confirmButton = getDeleteConfirmButton();
            setButtonLoading(btn, true, 'Deleting...');
            setButtonLoading(confirmButton, true, 'Deleting...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader('Deleting record...');
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
                        refreshClientTableFilters('signagesTable');
                        closeDeleteModal();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert('An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    setButtonLoading(btn, false);
                    setButtonLoading(confirmButton, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
                });
        }

        function deleteInfrastructureRecord(id, btn, skipPrompt = false) {
            if (!skipPrompt) {
                openDeleteModal('infrastructure', id, btn);
                return;
            }
            const confirmButton = getDeleteConfirmButton();
            setButtonLoading(btn, true, 'Deleting...');
            setButtonLoading(confirmButton, true, 'Deleting...');
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader('Deleting record...');
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
                        refreshClientTableFilters('infrastructureTable');
                        closeDeleteModal();
                    } else {
                        alert("Failed to delete the record.");
                    }
                })
                .catch(error => {
                    if (typeof window.showLiveAlert === 'function') {
                        window.showLiveAlert(error.message || 'An error occurred while deleting.', 'error');
                    } else {
                        alert('An error occurred while deleting.');
                    }
                })
                .finally(() => {
                    setButtonLoading(btn, false);
                    setButtonLoading(confirmButton, false);
                    if (typeof window.hideAppLoader === 'function') {
                        window.hideAppLoader();
                    }
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
            XLSX.writeFile(wb, getRpwsisExportFilename('Status Accomplishment', [
                { label: 'Search', inputId: 'simpleTableSearch' },
                { label: 'Region', selectId: 'simpleTableRegionFilter' },
                { label: 'Batch', selectId: 'simpleTableBatchFilter' },
            ]));
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
            XLSX.writeFile(wb, getRpwsisExportFilename('Summary of Accomplishment', [
                { label: 'Search', inputId: 'summaryTableSearch' },
                { label: 'Province', selectId: 'summaryTableProvinceFilter' },
                { label: 'Municipality', selectId: 'summaryTableMunicipalityFilter' },
            ]));
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
            XLSX.writeFile(wb, getRpwsisExportFilename('Nursery Establishment', [
                { label: 'Search', inputId: 'nurseryTableSearch' },
                { label: 'Municipality', selectId: 'nurseryTableMunicipalityFilter' },
                { label: 'Type', selectId: 'nurseryTableTypeFilter' },
            ]));
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
            XLSX.writeFile(wb, getRpwsisExportFilename('Informative Signages', [
                { label: 'Search', inputId: 'signagesTableSearch' },
                { label: 'Municipality', selectId: 'signagesTableMunicipalityFilter' },
                { label: 'Type', selectId: 'signagesTableTypeFilter' },
            ]));
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
            XLSX.writeFile(wb, getRpwsisExportFilename('Other Infrastructures', [
                { label: 'Search', inputId: 'infrastructureTableSearch' },
                { label: 'Municipality', selectId: 'infrastructureTableMunicipalityFilter' },
                { label: 'Type', selectId: 'infrastructureTableTypeFilter' },
            ]));
        }

        function getRpwsisExportFilename(baseTitle, filters = []) {
            const dateText = new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const parts = [`${baseTitle} as of ${dateText}`];

            filters.forEach((filter) => {
                if (filter.inputId) {
                    const input = document.getElementById(filter.inputId);
                    const value = (input?.value || '').trim();
                    if (value) {
                        parts.push(`${filter.label} ${value}`);
                    }
                }

                if (filter.selectId) {
                    const select = document.getElementById(filter.selectId);
                    const value = (select?.value || '').trim();
                    const text = (select?.selectedOptions?.[0]?.textContent || '').trim();
                    if (value && text) {
                        parts.push(`${filter.label} ${text}`);
                    }
                }
            });

            return `${parts.join(' ').replace(/[\\/:*?"<>|]+/g, '-')}.xlsx`;
        }

        const clientTableFilterConfigs = {};

        function populateClientFilterOptions(selectId, values, placeholder) {
            const select = document.getElementById(selectId);
            if (!select) return;

            const uniqueValues = [...new Set(values.map(value => String(value || '').trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b));
            select.innerHTML = `<option value="">${placeholder}</option>`;
            uniqueValues.forEach((value) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                select.appendChild(option);
            });
        }

        function refreshClientTableFilters(tableId) {
            const config = clientTableFilterConfigs[tableId];
            if (!config) return;

            const table = document.getElementById(tableId);
            if (!table || !table.tBodies.length) return;

            const rows = Array.from(table.tBodies[0].rows).filter(row => !row.dataset.emptyState);

            (config.filters || []).forEach((filter) => {
                const select = document.getElementById(filter.selectId);
                const previousValue = select ? select.value : '';

                populateClientFilterOptions(
                    filter.selectId,
                    rows.map(row => row.cells[filter.columnIndex]?.textContent || ''),
                    filter.placeholder
                );

                if (select) {
                    const hasPreviousValue = Array.from(select.options).some(option => option.value === previousValue);
                    select.value = hasPreviousValue ? previousValue : '';
                }
            });

            applyClientTableFilters(tableId);
        }

        function applyClientTableFilters(tableId) {
            const config = clientTableFilterConfigs[tableId];
            if (!config) return;

            const table = document.getElementById(tableId);
            if (!table) return;

            const tbody = table.tBodies[0];
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.dataset.emptyState);
            const searchValue = config.searchInputId ? String(document.getElementById(config.searchInputId)?.value || '').trim().toLowerCase() : '';
            const filterValues = (config.filters || []).map((filter) => ({
                columnIndex: filter.columnIndex,
                value: String(document.getElementById(filter.selectId)?.value || '').trim().toLowerCase(),
            }));

            let visibleCount = 0;

            rows.forEach((row) => {
                const cells = Array.from(row.cells);
                const rowText = cells.map(cell => cell.textContent.replace(/\s+/g, ' ').trim().toLowerCase()).join(' ');
                const matchesSearch = !searchValue || rowText.includes(searchValue);
                const matchesFilters = filterValues.every((filter) => {
                    if (!filter.value) return true;
                    const cell = cells[filter.columnIndex];
                    return cell && cell.textContent.replace(/\s+/g, ' ').trim().toLowerCase() === filter.value;
                });

                const isVisible = matchesSearch && matchesFilters;
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount += 1;
            });

            let emptyStateRow = tbody.querySelector('tr[data-empty-state="true"]');
            if (!emptyStateRow) {
                emptyStateRow = document.createElement('tr');
                emptyStateRow.dataset.emptyState = 'true';
                emptyStateRow.innerHTML = `<td colspan="${table.tHead.rows[table.tHead.rows.length - 1].cells.length}" style="text-align:center; padding: 28px 16px; color: #94a3b8;">No rows match the current filters.</td>`;
                tbody.appendChild(emptyStateRow);
            }
            emptyStateRow.style.display = visibleCount === 0 ? '' : 'none';
        }

        function setupClientTableFilters(config) {
            clientTableFilterConfigs[config.tableId] = config;
            const table = document.getElementById(config.tableId);
            if (!table || !table.tBodies.length) return;

            const rows = Array.from(table.tBodies[0].rows);
            (config.filters || []).forEach((filter) => {
                populateClientFilterOptions(
                    filter.selectId,
                    rows.map(row => row.cells[filter.columnIndex]?.textContent || ''),
                    filter.placeholder
                );

                const select = document.getElementById(filter.selectId);
            });

            if (config.searchInputId) {
                const searchInput = document.getElementById(config.searchInputId);
                if (searchInput) {
                    searchInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            applyClientTableFilters(config.tableId);
                        }
                    });
                }
            }

            if (config.applyButtonId) {
                const applyButton = document.getElementById(config.applyButtonId);
                if (applyButton) {
                    applyButton.addEventListener('click', () => applyClientTableFilters(config.tableId));
                }
            }

            refreshClientTableFilters(config.tableId);
        }

        function resetClientTableFilters(tableId) {
            const config = clientTableFilterConfigs[tableId];
            if (!config) return;

            if (config.searchInputId) {
                const searchInput = document.getElementById(config.searchInputId);
                if (searchInput) searchInput.value = '';
            }

            (config.filters || []).forEach((filter) => {
                const select = document.getElementById(filter.selectId);
                if (select) select.value = '';
            });

            applyClientTableFilters(tableId);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupClientTableFilters({
                tableId: 'simpleTable',
                applyButtonId: 'simpleTableApplyButton',
                searchInputId: 'simpleTableSearch',
                filters: [],
            });

            setupClientTableFilters({
                tableId: 'summaryTable',
                applyButtonId: 'summaryTableApplyButton',
                searchInputId: 'summaryTableSearch',
                filters: [
                    { selectId: 'summaryTableProvinceFilter', columnIndex: 1, placeholder: 'All provinces' },
                    { selectId: 'summaryTableMunicipalityFilter', columnIndex: 2, placeholder: 'All municipalities' },
                ],
            });

            setupClientTableFilters({
                tableId: 'nurseryTable',
                applyButtonId: 'nurseryTableApplyButton',
                searchInputId: 'nurseryTableSearch',
                filters: [
                    { selectId: 'nurseryTableMunicipalityFilter', columnIndex: 2, placeholder: 'All municipalities' },
                    { selectId: 'nurseryTableTypeFilter', columnIndex: 7, placeholder: 'All nursery types' },
                ],
            });

            setupClientTableFilters({
                tableId: 'signagesTable',
                applyButtonId: 'signagesTableApplyButton',
                searchInputId: 'signagesTableSearch',
                filters: [
                    { selectId: 'signagesTableMunicipalityFilter', columnIndex: 2, placeholder: 'All municipalities' },
                    { selectId: 'signagesTableTypeFilter', columnIndex: 6, placeholder: 'All signage types' },
                ],
            });

            setupClientTableFilters({
                tableId: 'infrastructureTable',
                applyButtonId: 'infrastructureTableApplyButton',
                searchInputId: 'infrastructureTableSearch',
                filters: [
                    { selectId: 'infrastructureTableMunicipalityFilter', columnIndex: 2, placeholder: 'All municipalities' },
                    { selectId: 'infrastructureTableTypeFilter', columnIndex: 6, placeholder: 'All infrastructure types' },
                ],
            });
        });
    </script>

    {{-- CHART LOGIC --}}
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
    </script>
@endsection
