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
                @include('partials.active-projects-table', [
                    'resolutions' => $resolutions ?? collect(),
                    'containerId' => 'activeProjectsContainer',
                    'editable' => false,
                ])
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
            @include('partials.event-manager-readonly', ['events' => $events ?? collect(), 'categories' => $categories ?? collect()])
        </div>
    </div>

    @if(isset($db_team) && $db_team === 'fs_team' && isset($hydroProjects) && isset($fsdeProjects))
        <div class="ui-card" id="guestHydroSection" style="margin-top: 24px;">
            <div class="section-title">Hydro-Georesistivity Status Monitoring</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestHydroSection',
                'searchName' => 'hydro_search',
                'searchPlaceholder' => 'Search project code, system, municipality, status...',
                'filters' => [
                    [
                        'name' => 'hydro_district',
                        'label' => 'District',
                        'options' => ['' => 'All districts'] + collect($hydroDistricts ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'hydro_status',
                        'label' => 'Status',
                        'options' => ['' => 'All statuses'] + collect($hydroStatuses ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['hydro_search', 'hydro_district', 'hydro_status', 'hydro_page'],
            ])
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
                    <a href="{{ $hydroProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $hydroProjects->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestHydroSection">&lt;</a>
                    
                    @php
                        $hStart = max($hydroProjects->currentPage() - 2, 1);
                        $hEnd = min($hStart + 4, $hydroProjects->lastPage());
                        if ($hEnd - $hStart < 4) { $hStart = max($hEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $hStart; $page <= $hEnd; $page++)
                        <a href="{{ $hydroProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $hydroProjects->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestHydroSection">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $hydroProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$hydroProjects->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestHydroSection">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card" id="guestFsdeSection">
            <div class="section-title">Monthly FSDE Status Report</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestFsdeSection',
                'searchName' => 'fsde_search',
                'searchPlaceholder' => 'Search project, consultant, municipality...',
                'filters' => [
                    [
                        'name' => 'fsde_year',
                        'label' => 'Year',
                        'options' => ['' => 'All years'] + collect($fsdeYears ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'fsde_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + collect($fsdeMunicipalities ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['fsde_search', 'fsde_year', 'fsde_municipality', 'fsde_page'],
            ])
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
                    <a href="{{ $fsdeProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $fsdeProjects->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestFsdeSection">&lt;</a>
                    
                    @php
                        $fStart = max($fsdeProjects->currentPage() - 2, 1);
                        $fEnd = min($fStart + 4, $fsdeProjects->lastPage());
                        if ($fEnd - $fStart < 4) { $fStart = max($fEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $fStart; $page <= $fEnd; $page++)
                        <a href="{{ $fsdeProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $fsdeProjects->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestFsdeSection">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $fsdeProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$fsdeProjects->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestFsdeSection">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'pao_team' && isset($powData))
        <div class="ui-card" id="guestPowSection" style="margin-top: 24px;">
            <div class="section-title">Program of Works Status Monitoring</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestPowSection',
                'searchName' => 'pow_search',
                'searchPlaceholder' => 'Search district, allocation, remarks...',
                'filters' => [
                    [
                        'name' => 'pow_district',
                        'label' => 'District',
                        'options' => ['' => 'All districts'] + collect($powDistricts ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['pow_search', 'pow_district', 'pow_page'],
            ])
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
                    <a href="{{ $powData->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $powData->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestPowSection">&lt;</a>

                    @php
                        $pStart = max($powData->currentPage() - 2, 1);
                        $pEnd = min($pStart + 4, $powData->lastPage());
                        if ($pEnd - $pStart < 4) { $pStart = max($pEnd - 4, 1); }
                    @endphp

                    @for ($page = $pStart; $page <= $pEnd; $page++)
                        <a href="{{ $powData->appends(request()->query())->url($page) }}" class="page-item {{ $page == $powData->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestPowSection">{{ $page }}</a>
                    @endfor

                    <a href="{{ $powData->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$powData->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestPowSection">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'pcr_team' && isset($pcrStatusReports))
        <div class="ui-card" id="guestPcrSection" style="margin-top: 24px;">
            <div class="section-title">PCR Status Monitoring</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestPcrSection',
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
                <table class="sleek-table" style="min-width: 1300px;">
                    <thead>
                        <tr>
                            <th rowspan="2">Fund Source</th>
                            <th rowspan="2">No. of Contracts</th>
                            <th rowspan="2">Allocation</th>
                            <th rowspan="2">No. of PCR Prepared</th>
                            <th rowspan="2">No. Submitted to Regional Office</th>
                            <th rowspan="2">Accomplishment</th>
                            <th colspan="3" style="text-align:center;">Remarks</th>
                        </tr>
                        <tr>
                            <th>For Signing of IA, Chief, DM, RM</th>
                            <th>For Submission to RO1</th>
                            <th>Not Yet Prepared / Pending Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pcrStatusReports as $report)
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
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center; padding: 30px 0; color: #a0aec0;">No PCR status data found.</td></tr>
                        @endforelse
                        @if($pcrStatusReports->count())
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
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if($pcrStatusReports->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $pcrStatusReports->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $pcrStatusReports->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestPcrSection">&lt;</a>
                    @php
                        $pcrStart = max($pcrStatusReports->currentPage() - 2, 1);
                        $pcrEnd = min($pcrStart + 4, $pcrStatusReports->lastPage());
                        if ($pcrEnd - $pcrStart < 4) { $pcrStart = max($pcrEnd - 4, 1); }
                    @endphp
                    @for ($page = $pcrStart; $page <= $pcrEnd; $page++)
                        <a href="{{ $pcrStatusReports->appends(request()->query())->url($page) }}" class="page-item {{ $page == $pcrStatusReports->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestPcrSection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $pcrStatusReports->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$pcrStatusReports->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestPcrSection">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'rpwsis_team' && isset($records) && isset($summaryRecords))
        <div class="ui-card" id="guestRpwsisStatusSection" style="margin-top: 24px;">
            <div class="section-title">
                A. ACCOMPLISHMENT OF SOCIAL AND ENVIRONMENTAL
            </div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestRpwsisStatusSection',
                'searchName' => 'rpwsis_status_search',
                'searchPlaceholder' => 'Search region, batch, NIS, activity...',
                'filters' => [
                    [
                        'name' => 'rpwsis_status_region',
                        'label' => 'Region',
                        'options' => ['' => 'All regions'] + collect($rpwsisStatusRegions ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'rpwsis_status_batch',
                        'label' => 'Batch',
                        'options' => ['' => 'All batches'] + collect($rpwsisStatusBatches ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['rpwsis_status_search', 'rpwsis_status_region', 'rpwsis_status_batch', 'rpwsis_status_page'],
            ])
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 2200px;">
                    <thead>
                        <tr>
                            <th rowspan="2">Region</th>
                            <th rowspan="2">Batch</th>
                            <th rowspan="2">Allocation</th>
                            <th rowspan="2">NIS</th>
                            <th rowspan="2">Activity</th>
                            <th rowspan="2">Remarks</th>
                            <th rowspan="2">Amount</th>
                            <th colspan="12" style="text-align:center;">Implementation Stage</th>
                            <th rowspan="2">PHY %</th>
                            <th rowspan="2">FIN %</th>
                            <th rowspan="2">EXP</th>
                        </tr>
                        <tr>
                            <th>POW Formulation</th>
                            <th>Nursery / Bunk House / STW</th>
                            <th>Seedling Production</th>
                            <th>Procurement</th>
                            <th>Site Preparation</th>
                            <th>Vegetative Enhancement</th>
                            <th>Wattling</th>
                            <th>Right of Way / Rent / Wages</th>
                            <th>Consultative Meetings</th>
                            <th>Reading Materials</th>
                            <th>Signboards / Signages</th>
                            <th>Monitoring</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $r)
                            <tr>
                                <td>{{ $r->region }}</td>
                                <td>{{ $r->batch }}</td>
                                <td>{{ $r->allocation }}</td>
                                <td>{{ $r->nis }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->activity }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->remarks }}</div></td>
                                <td>{{ $r->amount }}</td>
                                <td>{{ $r->c1 }}</td>
                                <td>{{ $r->c2 }}</td>
                                <td>{{ $r->c3 }}</td>
                                <td>{{ $r->c4 }}</td>
                                <td>{{ $r->c5 }}</td>
                                <td>{{ $r->c6 }}</td>
                                <td>{{ $r->c7 }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->c8 }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->c9 }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->c10 }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->c11 }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $r->c12 }}</div></td>
                                <td>{{ $r->phy }}</td>
                                <td>{{ $r->fin }}</td>
                                <td>{{ $r->exp }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="22" style="text-align:center; padding: 30px 0; color: #a0aec0;">No accomplishment data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $records->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $records->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisStatusSection">&lt;</a>
                    @php
                        $rsStart = max($records->currentPage() - 2, 1);
                        $rsEnd = min($rsStart + 4, $records->lastPage());
                        if ($rsEnd - $rsStart < 4) { $rsStart = max($rsEnd - 4, 1); }
                    @endphp
                    @for ($page = $rsStart; $page <= $rsEnd; $page++)
                        <a href="{{ $records->appends(request()->query())->url($page) }}" class="page-item {{ $page == $records->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisStatusSection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $records->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$records->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisStatusSection">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card" id="guestRpwsisSummarySection" style="margin-top: 24px;">
            <div class="section-title">
                REHABILITATION AND PROTECTION OF WATER RESOURCES SUPPORTING IRRIGATION SYSTEM (R&P WRSIS)
                <div style="font-size: 14px; font-weight: normal; margin-top: 4px; opacity: 0.9;">Summary of Accomplishment</div>
            </div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestRpwsisSummarySection',
                'searchName' => 'rpwsis_summary_search',
                'searchPlaceholder' => 'Search province, municipality, plantation type, NIS...',
                'filters' => [
                    [
                        'name' => 'rpwsis_summary_province',
                        'label' => 'Province',
                        'options' => ['' => 'All provinces'] + $summaryRecords->getCollection()->pluck('province')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'rpwsis_summary_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + $summaryRecords->getCollection()->pluck('municipality')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['rpwsis_summary_search', 'rpwsis_summary_province', 'rpwsis_summary_municipality', 'rpwsis_summary_page'],
            ])
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1800px;">
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
                            <th>Spacing</th>
                            <th>1st Year Maintenance and Protection</th>
                            <th>Replanting Target Area</th>
                            <th>Replanting Actual Area</th>
                            <th>Mortality Rate</th>
                            <th>Species Replanted</th>
                            <th>Name of NIS</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryRecords as $row)
                            <tr>
                                <td>{{ $row->region }}</td>
                                <td>{{ $row->province }}</td>
                                <td>{{ $row->municipality }}</td>
                                <td>{{ $row->barangay }}</td>
                                <td>{{ $row->plantation_type }}</td>
                                <td>{{ $row->year_established }}</td>
                                <td>{{ $row->target_area_1 }}</td>
                                <td>{{ $row->area_planted }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->species_planted }}</div></td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->spacing }}</div></td>
                                <td>{{ $row->maintenance }}</td>
                                <td>{{ $row->target_area_2 }}</td>
                                <td>{{ $row->actual_area }}</td>
                                <td>{{ $row->mortality_rate }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->species_replanted }}</div></td>
                                <td>{{ $row->nis_name }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->remarks }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="17" style="text-align:center; padding: 30px 0; color: #a0aec0;">No summary records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($summaryRecords->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $summaryRecords->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $summaryRecords->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSummarySection">&lt;</a>
                    @php
                        $sumStart = max($summaryRecords->currentPage() - 2, 1);
                        $sumEnd = min($sumStart + 4, $summaryRecords->lastPage());
                        if ($sumEnd - $sumStart < 4) { $sumStart = max($sumEnd - 4, 1); }
                    @endphp
                    @for ($page = $sumStart; $page <= $sumEnd; $page++)
                        <a href="{{ $summaryRecords->appends(request()->query())->url($page) }}" class="page-item {{ $page == $summaryRecords->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSummarySection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $summaryRecords->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$summaryRecords->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSummarySection">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card" id="guestRpwsisNurserySection" style="margin-top: 24px;">
            <div class="section-title">Nursery Establishment</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestRpwsisNurserySection',
                'searchName' => 'rpwsis_nursery_search',
                'searchPlaceholder' => 'Search municipality, barangay, nursery type, NIS...',
                'filters' => [
                    [
                        'name' => 'rpwsis_nursery_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + $nurseryRecords->getCollection()->pluck('municipality')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'rpwsis_nursery_type',
                        'label' => 'Type',
                        'options' => ['' => 'All nursery types'] + $nurseryRecords->getCollection()->pluck('nursery_type')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['rpwsis_nursery_search', 'rpwsis_nursery_municipality', 'rpwsis_nursery_type', 'rpwsis_nursery_page'],
            ])
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Barangay</th>
                            <th>X-Coordinates</th>
                            <th>Y-Coordinates</th>
                            <th>Number Seedlings Produced</th>
                            <th>Type of Nursery</th>
                            <th>Name of NIS</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nurseryRecords as $row)
                            <tr>
                                <td>{{ $row->region }}</td>
                                <td>{{ $row->province }}</td>
                                <td>{{ $row->municipality }}</td>
                                <td>{{ $row->barangay }}</td>
                                <td>{{ $row->x_coordinates }}</td>
                                <td>{{ $row->y_coordinates }}</td>
                                <td>{{ $row->seedlings_produced }}</td>
                                <td>{{ $row->nursery_type }}</td>
                                <td>{{ $row->nis_name }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->remarks }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align:center; padding: 30px 0; color: #a0aec0;">No nursery records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($nurseryRecords->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $nurseryRecords->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $nurseryRecords->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisNurserySection">&lt;</a>
                    @php
                        $nurStart = max($nurseryRecords->currentPage() - 2, 1);
                        $nurEnd = min($nurStart + 4, $nurseryRecords->lastPage());
                        if ($nurEnd - $nurStart < 4) { $nurStart = max($nurEnd - 4, 1); }
                    @endphp
                    @for ($page = $nurStart; $page <= $nurEnd; $page++)
                        <a href="{{ $nurseryRecords->appends(request()->query())->url($page) }}" class="page-item {{ $page == $nurseryRecords->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisNurserySection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $nurseryRecords->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$nurseryRecords->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisNurserySection">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card" id="guestRpwsisSignagesSection" style="margin-top: 24px;">
            <div class="section-title">Informative Signages Installed</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestRpwsisSignagesSection',
                'searchName' => 'rpwsis_signage_search',
                'searchPlaceholder' => 'Search municipality, barangay, signage type, NIS...',
                'filters' => [
                    [
                        'name' => 'rpwsis_signage_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + $signageRecords->getCollection()->pluck('municipality')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'rpwsis_signage_type',
                        'label' => 'Type',
                        'options' => ['' => 'All signage types'] + $signageRecords->getCollection()->pluck('signage_type')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['rpwsis_signage_search', 'rpwsis_signage_municipality', 'rpwsis_signage_type', 'rpwsis_signage_page'],
            ])
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Barangay</th>
                            <th>X-Coordinates</th>
                            <th>Y-Coordinates</th>
                            <th>Type of Signages</th>
                            <th>Name of NIS</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($signageRecords as $row)
                            <tr>
                                <td>{{ $row->region }}</td>
                                <td>{{ $row->province }}</td>
                                <td>{{ $row->municipality }}</td>
                                <td>{{ $row->barangay }}</td>
                                <td>{!! nl2br(e($row->x_coordinates)) !!}</td>
                                <td>{!! nl2br(e($row->y_coordinates)) !!}</td>
                                <td>{!! nl2br(e($row->signage_type)) !!}</td>
                                <td>{{ $row->nis_name }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->remarks }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center; padding: 30px 0; color: #a0aec0;">No signage records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($signageRecords->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $signageRecords->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $signageRecords->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSignagesSection">&lt;</a>
                    @php
                        $sigStart = max($signageRecords->currentPage() - 2, 1);
                        $sigEnd = min($sigStart + 4, $signageRecords->lastPage());
                        if ($sigEnd - $sigStart < 4) { $sigStart = max($sigEnd - 4, 1); }
                    @endphp
                    @for ($page = $sigStart; $page <= $sigEnd; $page++)
                        <a href="{{ $signageRecords->appends(request()->query())->url($page) }}" class="page-item {{ $page == $signageRecords->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSignagesSection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $signageRecords->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$signageRecords->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisSignagesSection">&gt;</a>
                </div>
            @endif
        </div>

        <div class="ui-card" id="guestRpwsisInfrastructureSection" style="margin-top: 24px;">
            <div class="section-title">Other Infrastructures</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestRpwsisInfrastructureSection',
                'searchName' => 'rpwsis_infrastructure_search',
                'searchPlaceholder' => 'Search municipality, barangay, infrastructure type, NIS...',
                'filters' => [
                    [
                        'name' => 'rpwsis_infrastructure_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + $infrastructureRecords->getCollection()->pluck('municipality')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'rpwsis_infrastructure_type',
                        'label' => 'Type',
                        'options' => ['' => 'All infrastructure types'] + $infrastructureRecords->getCollection()->pluck('infrastructure_type')->filter()->unique()->sort()->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['rpwsis_infrastructure_search', 'rpwsis_infrastructure_municipality', 'rpwsis_infrastructure_type', 'rpwsis_infrastructure_page'],
            ])
            <div class="table-responsive">
                <table class="sleek-table" style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Barangay</th>
                            <th>X-Coordinates</th>
                            <th>Y-Coordinates</th>
                            <th>Type of Infrastructure</th>
                            <th>Name of NIS</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($infrastructureRecords as $row)
                            <tr>
                                <td>{{ $row->region }}</td>
                                <td>{{ $row->province }}</td>
                                <td>{{ $row->municipality }}</td>
                                <td>{{ $row->barangay }}</td>
                                <td>{!! nl2br(e($row->x_coordinates)) !!}</td>
                                <td>{!! nl2br(e($row->y_coordinates)) !!}</td>
                                <td>{!! nl2br(e($row->infrastructure_type)) !!}</td>
                                <td>{{ $row->nis_name }}</td>
                                <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')">{{ $row->remarks }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center; padding: 30px 0; color: #a0aec0;">No infrastructure records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($infrastructureRecords->hasPages())
                <div class="custom-pagination">
                    <a href="{{ $infrastructureRecords->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $infrastructureRecords->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisInfrastructureSection">&lt;</a>
                    @php
                        $infStart = max($infrastructureRecords->currentPage() - 2, 1);
                        $infEnd = min($infStart + 4, $infrastructureRecords->lastPage());
                        if ($infEnd - $infStart < 4) { $infStart = max($infEnd - 4, 1); }
                    @endphp
                    @for ($page = $infStart; $page <= $infEnd; $page++)
                        <a href="{{ $infrastructureRecords->appends(request()->query())->url($page) }}" class="page-item {{ $page == $infrastructureRecords->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisInfrastructureSection">{{ $page }}</a>
                    @endfor
                    <a href="{{ $infrastructureRecords->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$infrastructureRecords->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestRpwsisInfrastructureSection">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    @if(isset($db_team) && $db_team === 'cm_team' && isset($procurementProjects))
        <div class="ui-card" id="guestProcurementSection" style="margin-top: 24px;">
            <div class="section-title">Procurement Status Monitoring</div>
            @include('partials.table-toolbar', [
                'asyncTarget' => '#guestProcurementSection',
                'searchName' => 'proc_search',
                'searchPlaceholder' => 'Search category, project, municipality, contractor...',
                'filters' => [
                    [
                        'name' => 'proc_category',
                        'label' => 'Category',
                        'options' => ['All Projects' => 'All categories'] + collect($procCategories ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                    [
                        'name' => 'proc_municipality',
                        'label' => 'Municipality',
                        'options' => ['' => 'All municipalities'] + collect($procMunicipalities ?? [])->mapWithKeys(fn($value) => [$value => $value])->all(),
                    ],
                ],
                'resetKeys' => ['proc_search', 'proc_category', 'proc_municipality', 'page'],
            ])
            
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
                    <a href="{{ $procurementProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $procurementProjects->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestProcurementSection">&lt;</a>
                    
                    @php
                        $pStart = max($procurementProjects->currentPage() - 2, 1);
                        $pEnd = min($pStart + 4, $procurementProjects->lastPage());
                        if ($pEnd - $pStart < 4) { $pStart = max($pEnd - 4, 1); }
                    @endphp
                    
                    @for ($page = $pStart; $page <= $pEnd; $page++)
                        <a href="{{ $procurementProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $procurementProjects->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#guestProcurementSection">{{ $page }}</a>
                    @endfor
                    
                    <a href="{{ $procurementProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$procurementProjects->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#guestProcurementSection">&gt;</a>
                </div>
            @endif
        </div>
    @endif

    <script>
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
