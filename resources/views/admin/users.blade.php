@extends('layouts.app')
@section('title', 'User Management')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #18181b;
            --primary-hover: #3f3f46;
            --bg-main: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
        }

        /* FIXED WIDTH & CENTERING */
        .content-wrapper {
            background-color: var(--bg-main);
            font-family: 'Inter', sans-serif;
            padding: 40px 20px;
            min-height: 100vh;
            max-width: 1300px;
            margin: 0 auto;
        }

        .header-section { margin-bottom: 32px; }
        .header-title { font-family: 'Poppins', sans-serif; font-size: 30px; font-weight: 700; color: var(--primary); letter-spacing: -0.02em; margin-bottom: 4px; }
        .header-desc { color: var(--text-muted); font-size: 14px; }

        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.75fr) minmax(320px, 0.95fr); gap: 24px; align-items: start; }
        @media (max-width: 1200px) { .dashboard-grid { grid-template-columns: 1fr; } }

        .ui-card { background: #ffffff; border-radius: 20px; padding: 28px; border: 1px solid var(--border-color); box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06); overflow: hidden; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .section-title { font-size: 18px; font-weight: 600; color: var(--primary); margin: 0; }
        .table-card { display: grid; gap: 18px; }
        .table-toolbar { display: grid; gap: 16px; }
        .filter-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; }
        .filter-form > div { flex: 1 1 155px; min-width: 155px; }
        .filter-form > div:first-child { flex: 1.6 1 240px; min-width: 240px; }
        .filter-label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
        .filter-input, .filter-select { min-height: 44px; padding: 10px 14px; border-radius: 12px; border: 1px solid #dbe3ee; background: #f8fafc; color: var(--text-main); font-size: 13px; width: 100%; min-width: 0; }
        .filter-input:focus, .filter-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(24, 24, 27, 0.08); background: #ffffff; }
        .btn-filter { flex: 0 0 auto; align-self: end; min-width: 88px; min-height: 44px; padding: 0 16px; border: none; border-radius: 12px; background: #18181b; color: #ffffff; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-filter:hover { background: #09090b; }
        .btn-reset { flex: 0 0 auto; align-self: end; min-width: 88px; display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 16px; border-radius: 12px; border: 1px solid #dbe3ee; color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: 700; background: #ffffff; }
        .btn-reset:hover { background: #f8fafc; border-color: #cbd5e1; }
        .results-meta { display: flex; justify-content: space-between; align-items: center; gap: 12px; color: var(--text-muted); font-size: 12px; }

        /* Form Styling */
        .form-card-header { margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid #eef2f7; }
        .form-card-header .section-title { margin-bottom: 8px; }
        .form-card-subtitle { color: var(--text-muted); font-size: 13px; line-height: 1.6; margin: 0; }

        .register-form { display: grid; gap: 18px; }
        .form-group { margin-bottom: 0; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em; }
        .form-input { width: 100%; min-height: 52px; padding: 14px 16px; border-radius: 14px; border: 1px solid #dbe3ee; font-size: 14px; background: #f8fafc; transition: all 0.2s ease; outline: none; color: var(--text-main); box-sizing: border-box; }
        .form-input:hover { border-color: #cbd5e1; background: #ffffff; }
        .form-input:focus { border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(24, 24, 27, 0.08); }
        .form-helper { margin-top: 8px; font-size: 12px; color: var(--text-muted); line-height: 1.5; }

        .btn-dark { background: linear-gradient(135deg, #18181b 0%, #27272a 100%); color: white; padding: 15px 18px; border-radius: 14px; font-size: 15px; font-weight: 700; width: 100%; border: none; cursor: pointer; transition: 0.2s; margin-top: 6px; box-shadow: 0 12px 24px rgba(24, 24, 27, 0.14); }
        .btn-dark:hover { background: linear-gradient(135deg, #09090b 0%, #18181b 100%); transform: translateY(-1px); }

        /* Table Styling */
        .table-container { overflow: hidden; width: 100%; max-width: 100%; border: 1px solid #eef2f7; border-radius: 18px; background: #fbfdff; }
        .sleek-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .sleek-table th { text-align: left; padding: 14px 12px; color: #64748b; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.07em; border-bottom: 1px solid #e9eef5; background: #f8fafc; white-space: normal; line-height: 1.4; overflow-wrap: anywhere; box-sizing: border-box; }
        .sleek-table td { padding: 14px 12px; border-bottom: 1px solid #eef2f7; font-size: 13px; color: var(--text-main); vertical-align: middle; background: #ffffff; overflow-wrap: anywhere; word-break: normal; box-sizing: border-box; }
        .sleek-table tbody tr:hover td { background-color: #fcfdff; }
        .sleek-table tbody tr:last-child td { border-bottom: none; }

        .user-cell { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .user-avatar { width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #18181b 0%, #334155 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12); }
        .user-meta { min-width: 0; max-width: 100%; }
        .user-name { font-weight: 700; color: var(--primary); margin-bottom: 2px; line-height: 1.3; font-size: 13px; overflow-wrap: anywhere; }
        .user-email { font-size: 12px; color: var(--text-muted); word-break: break-word; overflow-wrap: anywhere; line-height: 1.45; }

        .role-badge { display: inline-flex; align-items: center; justify-content: center; min-height: 30px; padding: 6px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; line-height: 1.35; max-width: 100%; white-space: normal; text-align: center; overflow-wrap: anywhere; }
        .role-badge.admin { background: #e0e7ff; color: #4338ca; }

        .status-pill { display: inline-flex; align-items: center; justify-content: center; min-height: 30px; padding: 6px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; background: #ecfdf5; color: #065f46; white-space: normal; text-align: center; max-width: 100%; overflow-wrap: anywhere; }
        .status-pill.inactive { background: #fff1f2; color: #9f1239; }

        .actions-cell { width: 1%; text-align: right; }
        .action-stack { display: flex; flex-direction: column; gap: 8px; justify-content: center; align-items: stretch; width: 100%; min-width: 0; max-width: 100%; margin-left: auto; }

        .status-select { width: 100%; min-width: 0; height: 40px; padding: 0 26px 0 10px; border-radius: 12px; border: 1px solid #dbe3ee; font-size: 12px; font-weight: 600; background: #ffffff; color: var(--text-main); cursor: pointer; box-sizing: border-box; }
        .status-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(24, 24, 27, 0.08); }

        .btn-danger, .btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; min-width: 0; max-width: 100%; min-height: 40px; padding: 8px 10px; background: #ffffff; border-radius: 12px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: normal; line-height: 1.2; text-align: center; box-sizing: border-box; overflow-wrap: anywhere; }
        .btn-danger { color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover:not(:disabled) { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
        .btn-secondary { color: #0f172a; border: 1px solid #cbd5e1; background: #f8fafc; }
        .btn-secondary:hover:not(:disabled) { background: #ffffff; border-color: #94a3b8; }
        .btn-danger:disabled, .btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }

        /* PASSWORD MODAL SPECIFIC CSS */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); position: relative;}
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .password-input-wrap { position: relative; width: 100%; }
        .password-input { width: 100%; height: 44px; padding: 0 16px; border-radius: 12px; border: 1px solid #dbe3ee; font-size: 13px; font-weight: 600; background: #f8fafc; color: var(--text-main); box-sizing: border-box; outline: none; }
        .password-input:focus { border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(24, 24, 27, 0.08); }
        .password-toggle { position: absolute; top: 50%; right: 8px; transform: translateY(-50%); border: none; background: transparent; color: #475569; font-size: 11px; font-weight: 700; cursor: pointer; padding: 6px 10px; border-radius: 8px; }
        .password-toggle:hover { background: #e2e8f0; }

        .password-reveal { display: none; padding: 12px 14px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: 13px; line-height: 1.45; word-break: break-word; margin-bottom: 20px;}
        .password-reveal.is-visible { display: block; }
        .password-reveal strong { display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8;}

        .muted-note { color: var(--text-muted); font-size: 11px; font-weight: 600; margin-top: 6px; }
        .empty-state { padding: 32px 20px; text-align: center; color: var(--text-muted); font-size: 13px; }
        .pagination-bar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-top: 18px; flex-wrap: wrap; }
        .pagination-summary { color: var(--text-muted); font-size: 12px; }
        .pagination-links { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .page-link, .page-current, .page-dots { min-width: 38px; height: 38px; padding: 0 12px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; text-decoration: none; }
        .page-link { border: 1px solid #dbe3ee; color: var(--text-main); background: #ffffff; }
        .page-link:hover { background: #f8fafc; border-color: #cbd5e1; }
        .page-link.disabled { pointer-events: none; opacity: 0.45; }
        .page-current { background: #18181b; color: #ffffff; }
        .page-dots { color: var(--text-muted); min-width: auto; padding: 0 4px; }

        @media (max-width: 768px) {
            .content-wrapper { padding: 24px 14px; }
            .ui-card { padding: 20px; }
            .action-stack { max-width: 100%; }
            .filter-form > div,
            .filter-form > div:first-child,
            .btn-filter,
            .btn-reset { flex-basis: 100%; width: 100%; min-width: 0; }
            .results-meta, .pagination-bar { align-items: flex-start; }
        }

        .alert-box { display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #18181b; color: #fff; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
        .ajax-feedback { display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 600; text-align: center; }
        .ajax-feedback.success { display: block; background: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0; }
        .ajax-feedback.error { display: block; background: #fff1f2; color: #9f1239; border: 1px solid #fecaca; }
        .is-loading { opacity: 0.5; pointer-events: none; }
    </style>

    <div class="content-wrapper">
        <div class="header-section">
            <h1 class="header-title">User Management</h1>
            <p class="header-desc">Manage agency staff, team roles, and system access permissions.</p>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div id="account-feedback" class="ajax-feedback"></div>

        <div class="dashboard-grid" id="userManagementGrid">
            <div class="ui-card table-card">
                <div class="section-header">
                    <h3 class="section-title">Team Directory</h3>
                    <span class="role-badge">{{ $users->total() }} Users Total</span>
                </div>

                <div class="table-toolbar">
                    <form action="{{ route('admin.users') }}" method="GET" class="filter-form js-user-filters">
                        <div>
                            <label class="filter-label" for="searchFilter">Search</label>
                            <input
                                type="search"
                                name="search"
                                id="searchFilter"
                                class="filter-input"
                                value="{{ $search ?? '' }}"
                                placeholder="Name, email, or role"
                            >
                        </div>

                        <div>
                            <label class="filter-label" for="roleFilter">Role</label>
                            <select name="role" id="roleFilter" class="filter-select">
                                <option value="">All Roles</option>
                                <option value="admin" {{ ($role ?? '') === 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="fs_team" {{ ($role ?? '') === 'fs_team' ? 'selected' : '' }}>Feasibility Study Team</option>
                                <option value="rpwsis_team" {{ ($role ?? '') === 'rpwsis_team' ? 'selected' : '' }}>Social and Environmental Team</option>
                                <option value="cm_team" {{ ($role ?? '') === 'cm_team' ? 'selected' : '' }}>Contract Management Team</option>
                                <option value="row_team" {{ ($role ?? '') === 'row_team' ? 'selected' : '' }}>Right Of Way Team</option>
                                <option value="pcr_team" {{ ($role ?? '') === 'pcr_team' ? 'selected' : '' }}>Program Completion Report Team</option>
                                <option value="pao_team" {{ ($role ?? '') === 'pao_team' ? 'selected' : '' }}>Programming Team</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="statusFilter">Status</label>
                            <select name="status" id="statusFilter" class="filter-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="sortFilter">Sort By</label>
                            <select name="sort" id="sortFilter" class="filter-select">
                                <option value="created_at" {{ ($sort ?? 'created_at') === 'created_at' ? 'selected' : '' }}>Joined Date</option>
                                <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                                <option value="email" {{ ($sort ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="role" {{ ($sort ?? '') === 'role' ? 'selected' : '' }}>Role</option>
                                <option value="is_active" {{ ($sort ?? '') === 'is_active' ? 'selected' : '' }}>Status</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="directionFilter">Direction</label>
                            <select name="direction" id="directionFilter" class="filter-select">
                                <option value="asc" {{ ($direction ?? '') === 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ ($direction ?? 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-filter">Apply</button>
                        <a href="{{ route('admin.users') }}" class="btn-reset" data-async-pagination="true" data-async-target="#userManagementGrid">Reset</a>
                    </form>

                        <div class="results-meta">
                        @if($users->count())
                            <span>Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }} users</span>
                        @else
                            <span>No users match the current search or filters</span>
                        @endif
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="sleek-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">User Details</th>
                                <th style="width: 20%;">Role / Team</th>
                                <th style="width: 13%;">Status</th>
                                <th style="width: 13%;">Joined</th>
                                <th style="width: 24%; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php $isCurrentUser = auth()->id() === $user->id; @endphp
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                            <div class="user-meta">
                                                <div class="user-name">{{ $user->name }}</div>
                                                <div class="user-email">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge {{ $user->role == 'admin' ? 'admin' : '' }}">
                                            @php
                                                $roleNames = [
                                                    'admin' => 'Administrator',
                                                    'fs_team' => 'Feasibility Study',
                                                    'rpwsis_team' => 'Social and Environmental Team',
                                                    'cm_team' => 'Contract Management',
                                                    'row_team' => 'Right Of Way',
                                                    'pcr_team' => 'Completion Report',
                                                    'pao_team' => 'Programming'
                                                ];
                                                echo $roleNames[$user->role] ?? $user->role;
                                            @endphp
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-pill js-status-pill {{ $user->is_active ? '' : 'inactive' }}">
                                            {{ $user->is_active ? 'Active' : 'Deactivated' }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-stack">
                                            <form action="{{ route('admin.users.status', $user) }}" method="POST" class="js-status-form" style="margin:0;">
                                                @csrf
                                                @method('PATCH')
                                                <select name="is_active" class="status-select js-status-select"
                                                    data-current-value="{{ $user->is_active ? '1' : '0' }}"
                                                    {{ $isCurrentUser ? 'disabled' : '' }}>
                                                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Activate</option>
                                                    <option value="0" {{ ! $user->is_active ? 'selected' : '' }}>Deactivate</option>
                                                </select>
                                            </form>

                                            <button type="button" class="btn-secondary" onclick="openPasswordModal('{{ route('admin.users.password', $user) }}', '{{ addslashes($user->name) }}')">
                                                Change Password
                                            </button>

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="margin:0;"
                                                data-async-target="#userManagementGrid" data-async-confirm="Permanently delete this user account?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-danger" {{ $isCurrentUser ? 'disabled' : '' }}>
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                        @if($isCurrentUser)
                                            <div class="muted-note" style="text-align: right;">Current account</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">No user accounts were found for the current search and filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    @php($paginationWindow = \Illuminate\Pagination\UrlWindow::make($users))
                    <div class="pagination-bar">
                        <div class="pagination-summary">
                            Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                        </div>

                        <div class="pagination-links">
                            <a href="{{ $users->previousPageUrl() ?: '#' }}"
                                class="page-link {{ $users->onFirstPage() ? 'disabled' : '' }}"
                                data-async-pagination="true"
                                data-async-target="#userManagementGrid">
                                Prev
                            </a>

                            @foreach(($paginationWindow['first'] ?? []) as $page => $url)
                                @if($page === $users->currentPage())
                                    <span class="page-current">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="page-link"
                                        data-async-pagination="true"
                                        data-async-target="#userManagementGrid">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            @if(!empty($paginationWindow['slider']))
                                @if(!empty($paginationWindow['first']))
                                    <span class="page-dots">...</span>
                                @endif

                                @foreach($paginationWindow['slider'] as $page => $url)
                                    @if($page === $users->currentPage())
                                        <span class="page-current">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}"
                                            class="page-link"
                                            data-async-pagination="true"
                                            data-async-target="#userManagementGrid">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                @if(!empty($paginationWindow['last']))
                                    <span class="page-dots">...</span>
                                @endif
                            @endif

                            @foreach(($paginationWindow['last'] ?? []) as $page => $url)
                                @if($page === $users->currentPage())
                                    <span class="page-current">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="page-link"
                                        data-async-pagination="true"
                                        data-async-target="#userManagementGrid">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            <a href="{{ $users->nextPageUrl() ?: '#' }}"
                                class="page-link {{ $users->hasMorePages() ? '' : 'disabled' }}"
                                data-async-pagination="true"
                                data-async-target="#userManagementGrid">
                                Next
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="ui-card">
                <div class="form-card-header">
                    <h3 class="section-title">Register User</h3>
                    <p class="form-card-subtitle">Create a clean agency account profile with the correct team assignment and initial access credentials.</p>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" data-async-target="#userManagementGrid" class="register-form">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="Enter full name" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" required placeholder="name@agency.gov" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Initial Password</label>
                        <div class="password-input-wrap">
                            <input type="password" name="password" id="registerPasswordInput" class="form-input" required placeholder="Create a temporary password" minlength="8" maxlength="255">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('registerPasswordInput', this)">Show</button>
                        </div>
                        <div class="form-helper">Use at least 8 characters for the temporary login password.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign Team</label>
                        <select name="role" class="form-input" required>
                            <option value="" disabled selected>Select team access level</option>
                            <option value="admin">Admin (Full Access)</option>
                            <option value="fs_team">Feasibility Study Team</option>
                            <option value="rpwsis_team">Social and Environmental Team</option>
                            <option value="cm_team">Contract Management Team</option>
                            <option value="row_team">Right Of Way Team</option>
                            <option value="pcr_team">Program Completion Report Team</option>
                            <option value="pao_team">Programming Team</option>
                        </select>
                        <div class="form-helper">Choose the department that will control this account's dashboard and permissions.</div>
                    </div>

                    <button type="submit" class="btn-dark">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="passwordModal">
        <div class="modal-box">
            <h3 class="section-title" style="margin-top: 0; border: none; padding: 0; margin-bottom: 5px;">Change Password</h3>
            <p class="form-card-subtitle" style="margin-bottom: 24px;">Setting new credentials for <strong id="modalUserName" style="color: var(--primary);"></strong></p>

            <form id="modalPasswordForm" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">New Password</label>
                    <div class="password-input-wrap">
                        <input type="password" name="password" id="modalPasswordInput" class="password-input" required placeholder="Enter new password" minlength="8" maxlength="255">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('modalPasswordInput', this)">Show</button>
                    </div>
                    <div class="form-helper">Ensure the password is at least 8 characters long.</div>
                </div>

                <div class="password-reveal" id="modalPasswordReveal"></div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closePasswordModal()" class="btn-secondary" style="flex: 1;" id="modalCancelBtn">Cancel</button>
                    <button type="submit" class="btn-dark" style="flex: 1; margin: 0;" id="modalSubmitBtn">Save Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Handlers
        function openPasswordModal(actionUrl, userName) {
            document.getElementById('modalUserName').innerText = userName;
            document.getElementById('modalPasswordForm').action = actionUrl;
            
            // Reset modal state
            document.getElementById('modalPasswordInput').value = '';
            document.getElementById('modalPasswordInput').type = 'password';
            
            let revealBox = document.getElementById('modalPasswordReveal');
            revealBox.classList.remove('is-visible');
            revealBox.innerHTML = '';

            document.getElementById('passwordModal').classList.add('active');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.remove('active');
        }

        // Shared Password Toggle function
        function togglePasswordVisibility(inputId, btnElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btnElement.textContent = 'Hide';
            } else {
                input.type = 'password';
                btnElement.textContent = 'Show';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const feedback = document.getElementById('account-feedback');

            function showFeedback(message, type) {
                if (!feedback) return;
                feedback.textContent = message;
                feedback.className = `ajax-feedback ${type}`;
                window.clearTimeout(showFeedback.timeoutId);
                showFeedback.timeoutId = window.setTimeout(() => {
                    feedback.className = 'ajax-feedback';
                    feedback.textContent = '';
                }, 3000);
            }

            document.addEventListener('submit', function(event) {
                const form = event.target.closest('.js-user-filters');
                if (!form) return;

                event.preventDefault();

                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);

                formData.forEach((value, key) => {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.set(key, String(value));
                    }
                });

                refreshAsyncTargetsFromUrl(url.toString(), ['#userManagementGrid'], true).catch((error) => {
                    showFeedback(error.message || 'Unable to apply filters.', 'error');
                });
            });

            // AJAX Status Update
            document.addEventListener('change', async function(event) {
                const select = event.target.closest('.js-status-select');
                if (!select) return;

                const form = select.closest('.js-status-form');
                const row = select.closest('tr');
                const statusPill = row ? row.querySelector('.js-status-pill') : null;
                const previousValue = select.dataset.currentValue || '1';
                const wasDisabled = select.disabled;
                const formData = new FormData(form);

                select.disabled = true;
                select.classList.add('is-loading');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Unable to update account status.');

                    const isActive = data.is_active === true || data.is_active === 1 || data.is_active === '1';
                    select.value = isActive ? '1' : '0';
                    select.dataset.currentValue = select.value;

                    if (statusPill) {
                        statusPill.textContent = isActive ? 'Active' : 'Deactivated';
                        statusPill.classList.toggle('inactive', !isActive);
                    }
                    showFeedback(data.message || 'Account status updated successfully.', 'success');
                } catch (error) {
                    select.value = previousValue;
                    showFeedback(error.message || 'Unable to update account status.', 'error');
                } finally {
                    select.disabled = wasDisabled;
                    select.classList.remove('is-loading');
                }
            });

            // 🌟 AJAX Modal Password Submission 🌟
            const modalPasswordForm = document.getElementById('modalPasswordForm');
            
            modalPasswordForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const passwordInput = document.getElementById('modalPasswordInput');
                const revealBox = document.getElementById('modalPasswordReveal');
                const submitButton = document.getElementById('modalSubmitBtn');
                const cancelButton = document.getElementById('modalCancelBtn');
                const formData = new FormData(modalPasswordForm);

                if (passwordInput.value.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
                revealBox.classList.remove('is-visible');

                try {
                    const response = await fetch(modalPasswordForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Unable to update password.');

                    // Show the newly set password so the admin can copy it!
                    if (data.plain_password) {
                        revealBox.innerHTML = `<strong>New password saved successfully!</strong><span>${data.plain_password}</span>`;
                        revealBox.classList.add('is-visible');
                    }

                    passwordInput.value = '';
                    passwordInput.type = 'password';
                    
                    // Change the cancel button to "Close" to imply success
                    cancelButton.textContent = "Close";
                    
                    showFeedback(data.message || 'Password updated successfully.', 'success');
                } catch (error) {
                    alert(error.message || 'Unable to update password.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Save Password';
                }
            });
        });
    </script>
@endsection
