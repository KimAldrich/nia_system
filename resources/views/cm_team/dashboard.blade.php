@extends('layouts.app')
@section('title', 'Contract Management Team Dashboard')

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

        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; display: inline-block; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; max-width: 100%; white-space: normal; word-wrap: break-word; text-align: center; }
        .badge-dark { background: #0c4d05; color: #fff; }
        .badge-light { background: #fda611; color: #ffffff; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }

        .btn-delete { background: #fee2e2; color: #ef4444; border: none; padding: 10px 18px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 40px; line-height: 1; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1); }
        .btn-delete:hover { background: #fecaca; color: #b91c1c; transform: translateY(-1px);}
        .btn-edit-icon { background: #e0e7ff; color: #4f46e5; border: none; min-width: 40px; height: 40px; padding: 0 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; line-height: 1; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.12); flex-shrink: 0; white-space: nowrap; }
        .btn-edit-icon:hover { background: #c7d2fe; color: #3730a3; transform: translateY(-1px); }
        .action-cell { text-align: center; white-space: nowrap !important; word-wrap: normal !important; overflow-wrap: normal !important; word-break: normal !important; }
        .action-buttons { display: flex; align-items: center; justify-content: center; flex-wrap: nowrap; gap: 5px; min-width: max-content; }
        .action-buttons form { display: inline-flex; margin: 0; }

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
                
                @include('partials.active-projects-table', [
                    'resolutions' => $resolutions ?? collect(),
                    'containerId' => 'activeProjectsContainer',
                    'editable' => auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']),
                    'updateRouteName' => 'cm.resolutions.update_status',
                ])
            </div>

            @include('partials.team-analytics-card', ['analytics' => $analytics ?? []])
        </div>

        <div class="side-column">
            @include('partials.event-manager-readonly', ['events' => $events ?? collect(), 'categories' => $categories ?? collect()])
        </div>
    </div>

<div class="ui-card" id="procurementSection" style="margin-top: 24px;">
    <div class="section-title">
        Procurement Status Monitoring
        
        <div style="display: flex; gap: 15px; align-items: center;">
            @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                <button onclick="openProcAddModal()" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                    + Add Data
                </button>
            @endif
            
<a href="{{ route('cm.procurement.export', request()->query()) }}" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Export Excel
</a>
        </div>
    </div>

    @include('partials.table-toolbar', [
        'asyncTarget' => '#procurementSection',
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
    
    <div class="table-responsive" id="procurementTableContainer">
        <table class="sleek-table" id="procTable" style="min-width: 2000px;">
            <thead>
                <tr>
                    <th style="width: 3%;">No.</th>
                    <th style="width: 14%;">Project Name</th>
                    <th style="width: 8%;">Municipality</th>
                    <th style="width: 10%;">Allocation / ABC</th>
                    <th style="width: 9%;">Bidding Info</th>
                    <th style="width: 9%;">Award Info</th>
                    <th style="width: 9%;">Contract Agreement</th>
                    <th style="width: 9%;">Notice to Proceed</th>
                    <th style="width: 9%;">Contract Info</th>
                    <th style="width: 8%;">Contractor</th>
                    <th style="width: 8%;">Remarks</th>
                    <th style="width: 10%;">Description</th>
                    @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                        <th style="text-align: center; width: 6%;">Action</th>
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
                        
                        <td style="line-height: 1.8; font-size: 11px;">
                            <strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->ca_date ?: '-' }}</span><br>
                            @if($project->ca_file)
                                <a href="{{ asset('storage/' . $project->ca_file) }}" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">View File</a>
                            @else
                                <span style="color: #a1a1aa;">No File</span>
                            @endif
                        </td>
                        
                        <td style="line-height: 1.8; font-size: 11px;">
                            <strong style="color:#1e293b;">Date:</strong> <span style="color:#64748b">{{ $project->ntp_date ?: '-' }}</span><br>
                            @if($project->ntp_file)
                                <a href="{{ asset('storage/' . $project->ntp_file) }}" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">View File</a>
                            @else
                                <span style="color: #a1a1aa;">No File</span>
                            @endif
                        </td>

                        <td style="line-height: 1.8;"><strong style="color:#1e293b; font-size: 11px;">No:</strong> {{ $project->contract_no ?: '-' }}<br><span style="color:#ea580c; font-weight:700;">Amt:</span> {{ $project->contract_amount ?: '-' }}</td>
                        <td>{{ $project->name_of_contractor ?: '-' }}</td>
                        <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">{{ $project->remarks }}</div></td>
                        <td class="col-desc"><div class="text-clamp" onclick="this.classList.toggle('expanded')" title="Click to expand">{{ $project->project_description }}</div></td>
                        @if (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin']))
                            <td class="action-cell">
                                <div class="action-buttons">
                                <button
                                    type="button"
                                    class="btn-edit-icon"
                                    title="Edit Project"
                                    onclick="openProcEditModal({{ $project->id }}, '{{ addslashes($project->name_of_project) }}', '{{ $project->category }}', '{{ $project->municipality }}', '{{ $project->allocation }}', '{{ $project->abc }}', '{{ $project->bid_out }}', '{{ $project->for_bidding }}', '{{ $project->date_of_bidding }}', '{{ $project->awarded }}', '{{ $project->date_of_award }}', '{{ $project->ca_date }}', '{{ $project->ntp_date }}', '{{ $project->contract_no }}', '{{ $project->contract_amount }}', '{{ addslashes($project->name_of_contractor) }}', '{{ addslashes($project->remarks) }}', '{{ addslashes($project->project_description) }}')">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536a2 2 0 01-.878.513L8 16l.951-3.658A2 2 0 019.464 11.46z"></path></svg>
                                    Edit
                                </button>
                                <form action="{{ route('cm.procurement.destroy', $project->id) }}" method="POST" data-async-success="silent" onsubmit="return handleAjaxSubmit(event, '#procurementSection', 'Are you sure you want to delete this project?')">
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
                    <tr><td colspan="{{ (auth()->check() && in_array(auth()->user()->role, ['cm_team', 'admin'])) ? '13' : '12' }}" style="text-align:center; padding: 30px 0; color: #a0aec0;">No Procurement records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($procurementProjects) && $procurementProjects->hasPages())
        <div class="custom-pagination">
                    <a href="{{ $procurementProjects->appends(request()->query())->previousPageUrl() }}" class="page-item {{ $procurementProjects->onFirstPage() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#procurementSection">&lt;</a>
            
            @php
                $pStart = max($procurementProjects->currentPage() - 2, 1);
                $pEnd = min($pStart + 4, $procurementProjects->lastPage());
                if ($pEnd - $pStart < 4) { $pStart = max($pEnd - 4, 1); }
            @endphp
            
            @for ($page = $pStart; $page <= $pEnd; $page++)
                <a href="{{ $procurementProjects->appends(request()->query())->url($page) }}" class="page-item {{ $page == $procurementProjects->currentPage() ? 'active' : '' }}" data-async-pagination="true" data-async-target="#procurementSection">{{ $page }}</a>
            @endfor
            
            <a href="{{ $procurementProjects->appends(request()->query())->nextPageUrl() }}" class="page-item {{ !$procurementProjects->hasMorePages() ? 'disabled' : '' }}" data-async-pagination="true" data-async-target="#procurementSection">&gt;</a>
        </div>
    @endif
</div>

    <div class="modal-overlay" id="addProcModal">
        <div class="modal-box" style="max-width: 600px;">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Add Procurement Data</h3>
            
            <form action="{{ route('cm.procurement.store') }}" method="POST" enctype="multipart/form-data" data-async-success-modal="#cmSuccessModal" onsubmit="return handleAjaxSubmit(event, '#procurementSection', null, true, '#addProcModal')">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Category</label>
                        <input type="text" name="category" list="catList" class="modern-input" placeholder="Select or type new category..." required maxlength="255">
                        <datalist id="catList">
                            @foreach($procCategories ?? [] as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div><label class="modern-label">Project No.</label><input type="text" name="proj_no" class="modern-input" placeholder="e.g. 1" required maxlength="50"></div>
                </div>
                
                <div><label class="modern-label">Name of Project</label><input type="text" name="name_of_project" required class="modern-input" maxlength="1000"></div>
                
                <div>
                    <label class="modern-label">Municipality / City</label>
                    <input type="text" name="municipality" list="pangasinanMunis" class="modern-input" placeholder="Select or type municipality..." required maxlength="100">
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
                    <div><label class="modern-label">Allocation</label><input type="number" name="allocation" class="modern-input" placeholder="0.00" min="0" step="0.01"></div>
                    <div><label class="modern-label">ABC</label><input type="number" name="abc" class="modern-input" placeholder="0.00" min="0" step="0.01"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Bid-Out</label><input type="number" name="bid_out" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">For Bidding</label><input type="number" name="for_bidding" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">Date of Bidding</label><input type="date" name="date_of_bidding" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Awarded</label><input type="number" name="awarded" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">Date of Award</label><input type="date" name="date_of_award" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract Agreement Date</label><input type="date" name="ca_date" class="modern-input"></div>
                    <div><label class="modern-label">Upload CA File (PDF, etc.)</label><input type="file" name="ca_file" class="modern-input" style="padding: 7px; background: #fff;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Notice to Proceed Date</label><input type="date" name="ntp_date" class="modern-input"></div>
                    <div><label class="modern-label">Upload NTP File (PDF, etc.)</label><input type="file" name="ntp_file" class="modern-input" style="padding: 7px; background: #fff;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract No.</label><input type="text" name="contract_no" class="modern-input" maxlength="100"></div>
                    <div><label class="modern-label">Contract Amount</label><input type="number" name="contract_amount" class="modern-input" min="0" step="0.01"></div>
                </div>

                <div><label class="modern-label">Contractor Name</label><input type="text" name="name_of_contractor" class="modern-input" maxlength="255"></div>
                
                <div><label class="modern-label">Remarks</label><input type="text" name="remarks" class="modern-input" maxlength="1000"></div>
                <div><label class="modern-label">Project Description</label><textarea name="project_description" rows="2" class="modern-input" style="resize: none;" maxlength="2000"></textarea></div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeProcAddModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;" id="saveProcBtn">Save Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editProcModal">
        <div class="modal-box" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h3 style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px;">Edit Procurement Data</h3>
            
            <form action="{{ route('cm.procurement.update') }}" method="POST" enctype="multipart/form-data" data-async-success-modal="#cmSuccessModal" onsubmit="return handleAjaxSubmit(event, '#procurementSection', null, true, '#editProcModal')">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-proc-id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="modern-label">Category</label>
                        <input type="text" id="edit-proc-category" name="category" list="catList" class="modern-input" placeholder="Select or type new category..." required maxlength="255">
                        <datalist id="catList">
                            @foreach($procCategories ?? [] as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div><label class="modern-label">Project No.</label><input type="text" id="edit-proc-proj_no" name="proj_no" class="modern-input" placeholder="e.g. 1" required maxlength="50" disabled></div>
                </div>
                
                <div><label class="modern-label">Name of Project</label><input type="text" id="edit-proc-name" name="name_of_project" required class="modern-input" maxlength="1000"></div>
                
                <div>
                    <label class="modern-label">Municipality / City</label>
                    <input type="text" id="edit-proc-municipality" name="municipality" list="pangasinanMunis" class="modern-input" placeholder="Select or type municipality..." required maxlength="100">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Allocation</label><input type="number" id="edit-proc-allocation" name="allocation" class="modern-input" placeholder="0.00" min="0" step="0.01"></div>
                    <div><label class="modern-label">ABC</label><input type="number" id="edit-proc-abc" name="abc" class="modern-input" placeholder="0.00" min="0" step="0.01"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Bid-Out</label><input type="number" id="edit-proc-bid_out" name="bid_out" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">For Bidding</label><input type="number" id="edit-proc-for_bidding" name="for_bidding" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">Date of Bidding</label><input type="date" id="edit-proc-date_bidding" name="date_of_bidding" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Awarded</label><input type="number" id="edit-proc-awarded" name="awarded" class="modern-input" min="0" step="1"></div>
                    <div><label class="modern-label">Date of Award</label><input type="date" id="edit-proc-date_award" name="date_of_award" class="modern-input"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract Agreement Date</label><input type="date" id="edit-proc-ca_date" name="ca_date" class="modern-input"></div>
                    <div><label class="modern-label">Replace CA File (Optional)</label><input type="file" name="ca_file" class="modern-input" style="padding: 7px; background: #fff;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Notice to Proceed Date</label><input type="date" id="edit-proc-ntp_date" name="ntp_date" class="modern-input"></div>
                    <div><label class="modern-label">Replace NTP File (Optional)</label><input type="file" name="ntp_file" class="modern-input" style="padding: 7px; background: #fff;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label class="modern-label">Contract No.</label><input type="text" id="edit-proc-contract_no" name="contract_no" class="modern-input" maxlength="100"></div>
                    <div><label class="modern-label">Contract Amount</label><input type="number" id="edit-proc-contract_amount" name="contract_amount" class="modern-input" min="0" step="0.01"></div>
                </div>

                <div><label class="modern-label">Contractor Name</label><input type="text" id="edit-proc-contractor" name="name_of_contractor" class="modern-input" maxlength="255"></div>
                
                <div><label class="modern-label">Remarks</label><input type="text" id="edit-proc-remarks" name="remarks" class="modern-input" maxlength="1000"></div>
                <div><label class="modern-label">Project Description</label><textarea id="edit-proc-description" name="project_description" rows="2" class="modern-input" style="resize: none;" maxlength="2000"></textarea></div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="closeProcEditModal()" class="modern-btn modern-btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" class="modern-btn" style="flex: 1;">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="cmSuccessModal">
        <div class="modal-box">
            <h3 data-success-title style="margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px;">Success</h3>
            <p data-success-message style="font-size: 14px; color: #475569; margin-bottom: 25px;">Saved successfully.</p>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeCmSuccessModal()" class="modern-btn" style="flex: 1;">OK</button>
            </div>
        </div>
    </div>

<script>
        @include('partials.team-analytics-script', ['analytics' => $analytics ?? []])

        let activeMonth = new Date().getMonth() + 1;
        document.addEventListener('DOMContentLoaded', function() { updateCalendarView(); });

        function changeMonth(direction) { activeMonth += direction; if (activeMonth < 1) activeMonth = 1; if (activeMonth > 12) activeMonth = 12; updateCalendarView(); }
        function updateCalendarView() { document.querySelectorAll('.month-block').forEach(block => { block.classList.remove('active'); }); const current = document.getElementById('month-' + activeMonth); if (current) current.classList.add('active'); document.getElementById('prevMonthBtn').disabled = (activeMonth === 1); document.getElementById('nextMonthBtn').disabled = (activeMonth === 12); }
        function openProcAddModal() { document.getElementById('addProcModal').classList.add('active'); }
        function closeProcAddModal() { document.getElementById('addProcModal').classList.remove('active'); }
        
        function openProcEditModal(id, name, category, municipality, allocation, abc, bid_out, for_bidding, date_bidding, awarded, date_award, ca_date, ntp_date, contract_no, contract_amount, contractor, remarks, description) {
            document.getElementById('edit-proc-id').value = id;
            document.getElementById('edit-proc-name').value = name;
            document.getElementById('edit-proc-category').value = category;
            document.getElementById('edit-proc-municipality').value = municipality;
            document.getElementById('edit-proc-allocation').value = allocation;
            document.getElementById('edit-proc-abc').value = abc;
            document.getElementById('edit-proc-bid_out').value = bid_out;
            document.getElementById('edit-proc-for_bidding').value = for_bidding;
            document.getElementById('edit-proc-date_bidding').value = date_bidding;
            document.getElementById('edit-proc-awarded').value = awarded;
            document.getElementById('edit-proc-date_award').value = date_award;
            
            document.getElementById('edit-proc-ca_date').value = ca_date;
            document.getElementById('edit-proc-ntp_date').value = ntp_date;
            
            document.getElementById('edit-proc-contract_no').value = contract_no;
            document.getElementById('edit-proc-contract_amount').value = contract_amount;
            document.getElementById('edit-proc-contractor').value = contractor;
            document.getElementById('edit-proc-remarks').value = remarks;
            document.getElementById('edit-proc-description').value = description;
            document.getElementById('editProcModal').classList.add('active');
        }
        
        function closeProcEditModal() { document.getElementById('editProcModal').classList.remove('active'); }
        function closeCmSuccessModal() { document.getElementById('cmSuccessModal').classList.remove('active'); window.location.reload(); }

        // 🌟 RESTORED AJAX SUBMIT FUNCTION 🌟
        async function handleAjaxSubmit(event, targetContainerId, confirmMessage = null, isModal = false, modalId = null) {
            event.preventDefault(); 
            if (confirmMessage && !confirm(confirmMessage)) return false; 

            const form = event.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            let originalBtnText = "";

            if (submitBtn) {
                originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = 'Processing...';
                submitBtn.disabled = true;
            } else {
                form.style.opacity = '0.5';
            }

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                // Catch Laravel Validation Errors (e.g. file too big)
                if (response.status === 422) {
                    const data = await response.json();
                    let errorMessages = [];
                    for (const key in data.errors) {
                        errorMessages.push(data.errors[key].join('\n'));
                    }
                    alert("Validation Error:\n" + errorMessages.join('\n'));
                    return false;
                }

                if (!response.ok) throw new Error('Network response was not ok');

                // Check if Controller returned a JSON success response
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json();
                    
                    if (modalId) {
                        document.querySelector(modalId).classList.remove('active');
                    }

                    // Show success modal if configured, otherwise reload immediately
                    const successModalId = form.getAttribute('data-async-success-modal');
                    if (successModalId) {
                        const successModal = document.querySelector(successModalId);
                        if (successModal) {
                            successModal.classList.add('active');
                            const msgEl = successModal.querySelector('[data-success-message]');
                            if (msgEl) msgEl.innerText = data.message || "Saved successfully.";
                            form.reset();
                            return false;
                        }
                    }
                    
                    window.location.reload(); // Reload to show new data
                    return false;
                }

                // Fallback: If Controller returns HTML view
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newContent = doc.querySelector(targetContainerId);
                const targetContainer = document.querySelector(targetContainerId);
                
                if (newContent && targetContainer) {
                    targetContainer.innerHTML = newContent.innerHTML;
                }

                if (modalId) {
                    document.querySelector(modalId).classList.remove('active');
                    form.reset(); 
                }

            } catch (error) {
                console.error("Error:", error);
                alert("An error occurred while saving data. Check console for details.");
            } finally {
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                } else {
                    form.style.opacity = '1';
                }
            }
            return false;
        }

        // 🌟 SMART EXCEL EXPORTER THAT FORMATS PERFECTLY & GRABS ALL DATA 🌟
        const rawExportData = @json($procExportData ?? []);

        function exportToExcel() {
            if (rawExportData.length === 0) {
                alert("No data available to export.");
                return;
            }

            const formatDateForTitle = (date) => date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            const formatSheetDate = (value) => {
                if (!value) return '';
                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) return value;
                return parsed.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            };

            const urlParams = new URLSearchParams(window.location.search);
            const today = new Date();
            const selectedCategory = urlParams.get('proc_category');
            const currentYear = today.getFullYear();
            const titleDate = formatDateForTitle(today);

            const groupedRows = rawExportData.reduce((groups, row) => {
                const key = row.category || 'Uncategorized';
                if (!groups[key]) groups[key] = [];
                groups[key].push(row);
                return groups;
            }, {});

            const orderedCategories = Object.keys(groupedRows).sort((a, b) => a.localeCompare(b));
            const sheetData = [
                ['STATUS OF PROCUREMENT AND CONTRACT - PANGASINAN IRRIGATION MANAGEMENT OFFICE'],
                [`CY ${currentYear} PROJECTS`],
                [`as of ${titleDate}`],
                [
                    'No. of Proj.', 'Name of Project', 'Municipality', 'Allocation and ABC', '', 'BID-OUT', 'For Bidding', 'Date of Bidding', 'AWARDED', 'Date of Award', 'Contract Agreement', 'Notice to Proceed', 'Contract No.', 'Contract Amount', 'Name of Contractor', 'Remarks', 'Project Description'
                ],
                [
                    '', '', '', `FY ${currentYear} (Allocation)`, 'Approved Budget of the Contract', '', '', '', '', '', '', '', '', '', '', '', ''
                ],
                new Array(17).fill(''),
                ['PANGASINAN IMO']
            ];

            orderedCategories.forEach((category) => {
                sheetData.push([category]);
                groupedRows[category].forEach((row) => {
                    sheetData.push([
                        row.proj_no || '', row.name_of_project || '', row.municipality || '', row.allocation || '', row.abc || '', row.bid_out || '', row.for_bidding || '', formatSheetDate(row.date_of_bidding), row.awarded || '', formatSheetDate(row.date_of_award), formatSheetDate(row.ca_date), formatSheetDate(row.ntp_date), row.contract_no || '', row.contract_amount || '', row.name_of_contractor || '', row.remarks || '', row.project_description || ''
                    ]);
                });
            });

            const worksheet = XLSX.utils.aoa_to_sheet(sheetData);

            worksheet['!cols'] = [
                { wch: 12 }, { wch: 42 }, { wch: 20 }, { wch: 18 }, { wch: 24 }, { wch: 10 }, { wch: 12 }, { wch: 18 }, { wch: 10 }, { wch: 18 }, { wch: 18 }, { wch: 18 }, { wch: 20 }, { wch: 18 }, { wch: 28 }, { wch: 22 }, { wch: 55 }
            ];

            worksheet['!merges'] = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 16 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: 16 } },
                { s: { r: 2, c: 0 }, e: { r: 2, c: 16 } },
                { s: { r: 3, c: 3 }, e: { r: 3, c: 4 } },
                { s: { r: 3, c: 0 }, e: { r: 5, c: 0 } },
                { s: { r: 3, c: 1 }, e: { r: 5, c: 1 } },
                { s: { r: 3, c: 2 }, e: { r: 5, c: 2 } },
                { s: { r: 4, c: 3 }, e: { r: 5, c: 3 } },
                { s: { r: 4, c: 4 }, e: { r: 5, c: 4 } },
                { s: { r: 3, c: 5 }, e: { r: 5, c: 5 } },
                { s: { r: 3, c: 6 }, e: { r: 5, c: 6 } },
                { s: { r: 3, c: 7 }, e: { r: 5, c: 7 } },
                { s: { r: 3, c: 8 }, e: { r: 5, c: 8 } },
                { s: { r: 3, c: 9 }, e: { r: 5, c: 9 } },
                { s: { r: 3, c: 10 }, e: { r: 5, c: 10 } },
                { s: { r: 3, c: 11 }, e: { r: 5, c: 11 } },
                { s: { r: 3, c: 12 }, e: { r: 5, c: 12 } }, 
                { s: { r: 3, c: 13 }, e: { r: 5, c: 13 } },
                { s: { r: 3, c: 14 }, e: { r: 5, c: 14 } },
                { s: { r: 3, c: 15 }, e: { r: 5, c: 15 } },
                { s: { r: 3, c: 16 }, e: { r: 5, c: 16 } },
                { s: { r: 6, c: 0 }, e: { r: 6, c: 16 } }
            ];

            let currentRowIndex = 7;
            orderedCategories.forEach((category) => {
                const categoryStartIndex = currentRowIndex;
                worksheet['!merges'].push({ s: { r: categoryStartIndex, c: 0 }, e: { r: categoryStartIndex, c: 16 } });
                currentRowIndex += groupedRows[category].length + 1;
            });

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");

            let filename = `Procurement Status as of ${titleDate}`;
            if (selectedCategory && selectedCategory !== 'All Projects') {
                filename += "_" + selectedCategory.replace(/[^a-z0-9]/gi, '_');
            }
            filename += ".xlsx";

            XLSX.writeFile(workbook, filename);
        }
    </script>
@endsection