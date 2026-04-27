<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/2020-nia-logo.png') }}">
    <title>@yield('title', 'System Dashboard')</title>
    <style>
       :root {
            --primary: #0b5e2c;
            --primary-dark: #084721;
            --sidebar-bg: #110d9e; /* #045115; 0d9e2c 110d9e */
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --sidebar-active: rgba(255, 255, 255, 0.2);
            --sidebar-text: #ffffff;
            --sidebar-icon: #ffffff;
            --tree-line: rgba(255, 255, 255, 0.3);
            /* NEW: Slightly lighter background to make white cards pop */
            --bg-color: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #334155;
            --border-color: #e2e8f0;
        }

        /* GLOBAL SCROLLBAR HIDING */
        * { scrollbar-width: none; -ms-overflow-style: none; }
        *::-webkit-scrollbar { display: none; }

        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg-color); color: var(--text-main); display: flex; height: 100vh; width: 100vw; overflow: hidden; box-sizing: border-box; }

        /* SIDEBAR LOGIC (Kept exactly as you had it) */
        .sidebar { position: relative; width: 310px; min-width: 310px; background-color: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; z-index: 1000; box-shadow: 2px 0 15px rgba(0,0,0,0.15); transition: margin-left 0.3s ease-in-out, transform 0.3s ease-in-out; }
        .sidebar.collapsed { margin-left: -310px; }
        .menu-toggle-btn { position: absolute; top: 20px; right: -16px; width: 32px; height: 32px; background-color: var(--sidebar-bg); color: #ffffff; border: 3px solid var(--bg-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 1001; box-shadow: 2px 0 5px rgba(0,0,0,0.1); transition: right 0.3s ease-in-out, transform 0.2s; }
        .menu-toggle-btn:hover { transform: scale(1.05); }
        .sidebar.collapsed .menu-toggle-btn { right: -50px; border-color: transparent; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        .sidebar-toggle-icon { position: relative; width: 16px; height: 12px; display: inline-flex; align-items: center; justify-content: center; }
        .sidebar-toggle-icon span { position: absolute; left: 0; width: 100%; height: 2px; border-radius: 999px; background: currentColor; transform-origin: center; transition: transform 0.28s ease, opacity 0.2s ease, top 0.28s ease; }
        .sidebar-toggle-icon span:nth-child(1) { top: 0; }
        .sidebar-toggle-icon span:nth-child(2) { top: 5px; }
        .sidebar-toggle-icon span:nth-child(3) { top: 10px; }
        .sidebar-toggle-btn.is-active .sidebar-toggle-icon span:nth-child(1) { top: 5px; transform: rotate(45deg); }
        .sidebar-toggle-btn.is-active .sidebar-toggle-icon span:nth-child(2) { opacity: 0; transform: scaleX(0.2); }
        .sidebar-toggle-btn.is-active .sidebar-toggle-icon span:nth-child(3) { top: 5px; transform: rotate(-45deg); }
        .sidebar-header { padding: 10px; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .sidebar-logo { width: 100px; height: auto; margin-bottom: 8px; }
        .sidebar-title { font-size: 13px; font-weight: 700; color: #ffffff; letter-spacing: 1px; line-height: 1.3; }
        .nav-links { flex: 1; padding: 5px 15px 15px 15px; overflow-y: auto; }
        .nav-label { margin-top: 10px; margin-bottom: 4px; padding: 0 12px; font-size: 10px; color: rgba(255,255,255,0.5); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        .menu-item { display: flex; align-items: center; padding: 8px 12px; border-radius: 6px; color: var(--sidebar-text); text-decoration: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; margin-bottom: 2px; }
        .menu-item:hover { background-color: var(--sidebar-hover); }
        .menu-item.active { background-color: var(--sidebar-active); color: white; font-weight: 600; }
        .menu-item svg { width: 16px; height: 16px; margin-right: 12px; stroke: var(--sidebar-icon); }
        .menu-item.active svg { stroke: white; }
        .menu-item .chevron { margin-left: auto; transition: transform 0.3s; }
        .menu-item.open .chevron { transform: rotate(180deg); }
        .sub-menu { display: none; padding-left: 32px; margin-top: -2px; margin-bottom: 4px; }
        .sub-menu.open { display: block; }
        .sub-item { position: relative; display: block; padding: 5px 12px; color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 12px; border-radius: 4px; margin-top: 1px; }
        .sub-item:hover, .sub-item.active { background-color: var(--sidebar-hover); color: white; }
        .sub-item::before { content: ''; position: absolute; left: -11px; top: -12px; bottom: 50%; width: 12px; border-left: 1px solid var(--tree-line); border-bottom: 1px solid var(--tree-line); border-bottom-left-radius: 8px; }
        .sub-item:not(:last-child)::after { content: ''; position: absolute; left: -11px; top: 50%; bottom: -12px; border-left: 1px solid var(--tree-line); }
        .sidebar-bottom { padding: 12px 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); background-color: rgba(0, 0, 0, 0.05); }
        .sidebar-user-card { display: flex; align-items: center; gap: 10px; padding: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 6px; margin-bottom: 12px; }
        .sidebar-avatar { width: 28px; height: 28px; min-width: 28px; border-radius: 50%; background: #ffffff; color: var(--sidebar-bg); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
        .sidebar-user-info { display: flex; flex-direction: column; overflow: hidden; }
        .sidebar-user-name { font-size: 12px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 9px; font-weight: 600; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .logout-btn { width: 100%; padding: 6px; background-color: transparent; color: #ef4444; border: 1px solid #ef4444; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11px; transition: all 0.3s; }
        .logout-btn:hover { background-color: #ef4444; color: white; }
        .copyright-text { margin-top: 10px; font-size: 8px; color: rgba(255, 255, 255, 0.4); line-height: 1.4; text-align: center; }

        /* =========================================
           MAIN CONTENT AREA
           ========================================= */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; transition: all 0.3s ease-in-out; max-width: calc(100vw - 310px); }
        .sidebar.collapsed ~ .main-wrapper { max-width: 100vw; }
        .content { padding: 30px; overflow-y: auto; overflow-x: hidden; flex: 1; width: 100%; box-sizing: border-box; }

        /* 🌟 NEW SOFT UI CARD STYLES (Matches Image) 🌟 */
        .card, .ui-card {
            background: var(--card-bg);
            border-radius: 16px; /* Smoother, rounder corners */
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); /* Soft, blurred shadow */
            margin-bottom: 20px;
            border: none; /* Removed the hard border! */
            max-width: 100%;
            width: 100%;
            min-width: 0;
            display: block;
            box-sizing: border-box;
            overflow: hidden;
        }
        .content .ui-card,
        .content .card {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            display: block;
            box-sizing: border-box;
            overflow: hidden;
        }
        .content .ui-card > *,
        .content .card > * {
            min-width: 0;
            max-width: 100%;
        }
        .content .ui-card img,
        .content .ui-card svg,
        .content .ui-card canvas,
        .content .card img,
        .content .card svg,
        .content .card canvas {
            max-width: 100%;
        }
        .content .dashboard-grid,
        .content .dashboard-main-grid,
        .content .kpi-grid {
            width: 100%;
            min-width: 0;
            align-items: start;
        }
        .content .dashboard-grid {
            grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
        }
        .content .main-column,
        .content .side-column {
            width: 100%;
            min-width: 0;
        }
        .content .section-title {
            flex-wrap: wrap;
            gap: 10px;
        }
        .content .ui-card table,
        .content .card table {
            width: 100%;
            max-width: 100%;
        }
        .content .ui-card [style*="grid-template-columns: 1fr 1fr"],
        .content .card [style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        }

        .page-title { margin-top: 0; color: #1e293b; font-size: 22px; margin-bottom: 15px; font-weight: 700; }
        .section-title { font-size: 16px; color: #64748b; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;}
        .table-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
            margin-bottom: 18px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .table-toolbar__search,
        .table-toolbar__field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 180px;
            flex: 1 1 180px;
        }
        .table-toolbar__label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .table-toolbar__input,
        .table-toolbar__select {
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #dbe3ee;
            background: #ffffff;
            color: #1e293b;
            font-size: 13px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .table-toolbar__input:focus,
        .table-toolbar__select:focus {
            outline: none;
            border-color: #110d9e;
            box-shadow: 0 0 0 4px rgba(17, 13, 158, 0.08);
        }
        .table-toolbar__actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }
        .table-toolbar__button {
            min-height: 44px;
            padding: 0 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .table-toolbar__button--primary {
            background: #110d9e;
            color: #ffffff;
        }
        .table-toolbar__button--primary:hover {
            background: #0c0a78;
        }
        .table-toolbar__button--ghost {
            background: #ffffff;
            color: #475569;
            border-color: #cbd5e1;
        }
        .table-toolbar__button--ghost:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        /* 🌟 NEW KPI METRIC CARDS 🌟 */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .kpi-card { position: relative; background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column; }
        .kpi-title { font-size: 14px; color: #a0aec0; font-weight: 500; }
        .kpi-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 8px 0; }
        .kpi-icon { position: absolute; top: 24px; right: 24px; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .kpi-icon.blue { background: #e0e7ff; color: #4318FF; }
        .kpi-icon.green { background: #dcfce7; color: #10b981; }
        .kpi-icon.orange { background: #ffedd5; color: #f59e0b; }
        .kpi-trend { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 5px; margin-top: auto;}
        .trend-up { color: #10b981; background: #dcfce7; padding: 2px 6px; border-radius: 4px;}
        .trend-down { color: #ef4444; background: #fee2e2; padding: 2px 6px; border-radius: 4px;}
        .trend-text { color: #a0aec0; font-weight: 500; }

        /* 🌟 GRID LAYOUT FOR CHARTS & CALENDAR 🌟 */
        .dashboard-main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start; }

        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #f1f5f9; word-break: break-word; font-size: 13px; color: #334155; }
        th { background-color: transparent; color: #a0aec0; font-weight: 600; text-transform: capitalize; font-size: 12px; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }

        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-pending { background: #ffedd5; color: #ea580c; }
        .badge-progress { background: #e0e7ff; color: #4f46e5; }
        .badge-completed { background: #dcfce7; color: #16a34a; }

        /* Mobile Styles */
        .mobile-header { display: none; align-items: center; justify-content: space-between; padding: 12px 15px; background: #ffffff; border-bottom: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.02); z-index: 900; }
        .mobile-menu-btn { background: var(--sidebar-bg); border: none; color: #ffffff; cursor: pointer; padding: 6px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .mobile-title { font-weight: 700; color: var(--primary-dark); font-size: 14px; letter-spacing: 0.5px; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.3s ease-in-out; }
        .app-alert-stack { display: grid; gap: 12px; margin-bottom: 20px; }
        .app-alert { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border-radius: 12px; border: 1px solid transparent; font-size: 13px; font-weight: 500; line-height: 1.5; }
        .app-alert-success { background: #ecfdf5; color: #166534; border-color: #bbf7d0; }
        .app-alert-error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .app-alert-info { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .app-alert-icon { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; background: currentColor; color: #ffffff; }
        .app-alert-title { font-weight: 700; margin-bottom: 4px; }
        .app-alert-list { margin: 0; padding-left: 18px; }
        .app-alert-list li + li { margin-top: 4px; }
        .app-global-loader {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(3px);
            z-index: 5000;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .app-global-loader.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .app-global-loader__card {
            min-width: 220px;
            max-width: min(90vw, 320px);
            padding: 20px 22px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .app-global-loader__spinner {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 3px solid #dbeafe;
            border-top-color: #110d9e;
            animation: appGlobalSpin 0.85s linear infinite;
            flex-shrink: 0;
        }
        .app-global-loader__text {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .app-confirm-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.38);
            backdrop-filter: blur(4px);
            z-index: 5100;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .app-confirm-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .app-confirm-modal__dialog {
            width: min(100%, 440px);
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
            padding: 26px 24px 22px;
        }
        .app-confirm-modal__title {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        .app-confirm-modal__message {
            margin: 0 0 24px;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        .app-confirm-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .app-confirm-modal__button {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .app-confirm-modal__button--cancel {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .app-confirm-modal__button--cancel:hover {
            background: #f8fafc;
        }
        .app-confirm-modal__button--confirm {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }
        .app-confirm-modal__button--confirm:hover {
            background: #dc2626;
            border-color: #dc2626;
        }
        .is-loading {
            opacity: 0.65;
            pointer-events: none;
        }
        @keyframes appGlobalSpin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 1150px) {
            .dashboard-main-grid { grid-template-columns: 1fr; }
            .content .dashboard-grid { grid-template-columns: minmax(0, 1fr); }
        }

        @media (max-width: 900px) {
            .main-wrapper { max-width: 100vw; }
            .mobile-header { display: flex; }
            .content { padding: 20px 15px; }
            .content .ui-card,
            .content .card {
                padding: 20px;
            }
            .table-toolbar {
                align-items: stretch;
            }
            .table-toolbar__search,
            .table-toolbar__field,
            .table-toolbar__actions {
                min-width: 100%;
                flex: 1 1 100%;
            }
            .table-toolbar__actions {
                margin-left: 0;
            }
            .table-toolbar__button {
                flex: 1 1 0;
            }
            .content .ui-card [style*="grid-template-columns: 1fr 1fr"],
            .content .card [style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .menu-toggle-btn { display: none; }
            .sidebar { position: fixed; top: 0; bottom: 0; left: 0; margin-left: 0 !important; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { opacity: 1; visibility: visible; }
        }
    </style>
</head>

<body>
    <div id="appGlobalLoader" class="app-global-loader" aria-hidden="true" role="status" aria-live="polite">
        <div class="app-global-loader__card">
            <div class="app-global-loader__spinner" aria-hidden="true"></div>
            <div id="appGlobalLoaderText" class="app-global-loader__text">Loading, please wait...</div>
        </div>
    </div>

    <div id="appConfirmModal" class="app-confirm-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="appConfirmModalTitle">
        <div class="app-confirm-modal__dialog">
            <h3 id="appConfirmModalTitle" class="app-confirm-modal__title">Confirm delete</h3>
            <p id="appConfirmModalMessage" class="app-confirm-modal__message">Are you sure you want to continue?</p>
            <div class="app-confirm-modal__actions">
                <button type="button" id="appConfirmModalCancel" class="app-confirm-modal__button app-confirm-modal__button--cancel">Cancel</button>
                <button type="button" id="appConfirmModalApprove" class="app-confirm-modal__button app-confirm-modal__button--confirm">Delete</button>
            </div>
        </div>
    </div>

    @php
        if(session('is_guest')) {
            $userName = 'Guest User';
            $currentUserTeam = 'Public Visitor';
        } else {
            $roleLabels = [
                'admin' => 'Administrator',
                'fs_team' => 'FS Member',
                'rpwsis_team' => 'Social And Environmental Team Member',
                'cm_team' => 'Contract Management Team Member',
                'row_team' => 'Right Of Way Team Member',
                'pcr_team' => 'Program Completion Report Team Member',
                'pao_team' => 'Programming Team Member',
            ];
            $currentUser = auth()->user();
            $userName = $currentUser->name ?? 'User';
            $currentUserTeam = $roleLabels[$currentUser->role ?? ''] ?? 'User';
        }
    @endphp

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <button class="menu-toggle-btn sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar" aria-label="Toggle Sidebar" aria-expanded="true">
            <span class="sidebar-toggle-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <div class="sidebar-header">
            <img src="{{ asset('images/nia-logo.png') }}" alt="NIA Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="sidebar-title">PANGASINAN IMO</div>
        </div>

        <div class="nav-links">

            @if(auth()->check() && auth()->user()->role == 'admin')
                <div class="nav-label">Admin Controls</div>
                <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('admin.users') }}" class="menu-item {{ request()->routeIs('admin.users') ? 'active' : '' }}" style="margin-bottom: 8px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>User Management</span>
                </a>
            @endif

            @if(session('is_guest'))
                <div class="nav-label">Guest Portal</div>
                <a href="{{ route('guest.dashboard') }}" class="menu-item {{ request()->routeIs('guest.dashboard') ? 'active' : '' }}" style="margin-bottom: 8px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Master Dashboard</span>
                </a>
            @endif

            <div class="nav-label">Departments</div>

            @php
                $teams = [
                    'fs-team' => 'Feasibility Study Team',
                    'rpwsis_team' => 'Social And Environmental Team',
                    'cm_team' => 'Contract Management Team',
                    'row_team' => 'Right Of Way Team',
                    'pcr_team' => 'Program Completion Report Team',
                    'pao_team' => 'Programming Team'
                ];
                $activeTeam = request()->segment(1);
                if(session('is_guest') && request()->segment(2) == 'team') {
                    $activeTeam = request()->segment(3);
                }
            @endphp

            @foreach($teams as $slug => $name)
                <div class="menu-item {{ $activeTeam == $slug ? 'active open' : '' }}" onclick="toggleMenu('menu-{{ $slug }}', this)">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span style="white-space: nowrap;">{{ $name }}</span>
                    <svg class="chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                </div>

<div class="sub-menu {{ $activeTeam == $slug ? 'open' : '' }}" id="menu-{{ $slug }}">
    @if(session('is_guest'))
        <a href="{{ route('guest.team.dashboard', $slug) }}" class="sub-item {{ request()->is('guest/' . $slug . '/dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('guest.team.downloadables', $slug) }}" class="sub-item {{ request()->is('guest/' . $slug . '/downloadables') ? 'active' : '' }}">Downloadables</a>
        <a href="{{ route('guest.team.resolutions', $slug) }}" class="sub-item {{ request()->is('guest/' . $slug . '/resolutions') ? 'active' : '' }}">IA Resolutions</a>
    @else
        <a href="/{{ $slug }}/dashboard" class="sub-item {{ request()->is($slug . '/dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="/{{ $slug }}/downloadables" class="sub-item {{ request()->is($slug . '/downloadables') ? 'active' : '' }}">Downloadables</a>
        <a href="/{{ $slug }}/ia-resolutions" class="sub-item {{ request()->is($slug . '/ia-resolutions') ? 'active' : '' }}">IA Resolutions</a>
    @endif
</div>
            @endforeach

            <div class="nav-label" style="margin-top: 15px;">Shared Hubs</div>

            @if(!session('is_guest'))
                <a href="{{ route('administrative.index') }}" class="menu-item {{ request()->routeIs('administrative.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Administrative</span>
                </a>
            @endif

            <a href="{{ route('map') }}" class="menu-item {{ request()->routeIs('map') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.553-.832L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-1.553-.832L15 9m0 8V9m0 0L9 7"></path></svg>
                <span>Interactive Map</span>
            </a>
        </div>

        <div class="sidebar-bottom">
            <div class="sidebar-user-card">
                <div class="sidebar-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $userName }}</div>
                    <div class="sidebar-user-role">{{ $currentUserTeam }}</div>
                </div>
            </div>

            @if(session('is_guest'))
                <form action="{{ route('guest.logout') }}" method="POST">
            @else
                <form action="{{ route('logout') }}" method="POST">
            @endif
                @csrf
                <button type="submit" class="logout-btn">Log Out</button>
            </form>

            <div class="copyright-text">
                Copyright ©2026 All rights<br>
                reserved | This website is made<br>
                by NIA PIMO
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <div class="mobile-header">
            <button class="mobile-menu-btn sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Sidebar" aria-expanded="false">
                <span class="sidebar-toggle-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <div class="mobile-title">NIA PIMO Portal</div>
            <div style="width: 24px;"></div>
        </div>

        <div class="content">
            <div id="appLiveAlerts"></div>
            @include('partials.alerts')
            @yield('content')
        </div>
    </div>

    <script>
        function setFieldValidityState(field) {
            if (!(field instanceof HTMLElement) || typeof field.checkValidity !== 'function' || !field.willValidate) {
                return;
            }

            const isInvalid = !field.checkValidity();
            field.classList.toggle('is-invalid', isInvalid);
            field.setAttribute('aria-invalid', isInvalid ? 'true' : 'false');
        }

        function validateFormBeforeSubmit(form) {
            if (!(form instanceof HTMLFormElement) || typeof form.checkValidity !== 'function') {
                return true;
            }

            Array.from(form.elements).forEach(setFieldValidityState);

            if (form.checkValidity()) {
                return true;
            }

            const firstInvalidField = form.querySelector(':invalid');
            if (typeof form.reportValidity === 'function') {
                form.reportValidity();
            }

            if (firstInvalidField instanceof HTMLElement) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            showLiveAlert('Please review the highlighted fields and try again.', 'error');
            return false;
        }

        function showLiveAlert(message, type = 'success') {
            const host = document.getElementById('appLiveAlerts');
            if (!host || !message) return;

            const alert = document.createElement('div');
            alert.className = `app-alert-stack`;
            alert.innerHTML = `
                <div class="app-alert app-alert-${type}" role="status" aria-live="polite">
                    <span class="app-alert-icon" aria-hidden="true">${type === 'error' ? '!' : type === 'info' ? 'i' : '✓'}</span>
                    <div>
                        <div class="app-alert-title">${type === 'error' ? 'Something went wrong' : 'Success'}</div>
                        <div>${message}</div>
                    </div>
                </div>
            `;

            host.innerHTML = '';
            host.appendChild(alert);

            window.clearTimeout(showLiveAlert.timeoutId);
            showLiveAlert.timeoutId = window.setTimeout(() => {
                if (host.contains(alert)) {
                    host.removeChild(alert);
                }
            }, 4000);
        }

        function openAsyncSuccessModal(selector, message, title = 'Success') {
            if (!selector) {
                return false;
            }

            const modal = document.querySelector(selector);
            if (!modal) {
                return false;
            }

            const titleNode = modal.querySelector('[data-success-title]');
            const messageNode = modal.querySelector('[data-success-message]');

            if (titleNode) {
                titleNode.textContent = title || 'Success';
            }

            if (messageNode) {
                messageNode.textContent = message || 'Saved successfully.';
            }

            modal.classList.add('active');
            return true;
        }

        const appLoaderState = {
            activeCount: 0,
            hideTimer: null
        };

        function setAppLoaderMessage(message = 'Loading, please wait...') {
            const textNode = document.getElementById('appGlobalLoaderText');
            if (textNode) {
                textNode.textContent = message;
            }
        }

        function showAppLoader(message = 'Loading, please wait...') {
            const loader = document.getElementById('appGlobalLoader');
            if (!loader) {
                return;
            }

            window.clearTimeout(appLoaderState.hideTimer);
            appLoaderState.activeCount += 1;
            setAppLoaderMessage(message);
            loader.classList.add('is-visible');
            loader.setAttribute('aria-hidden', 'false');
        }

        function hideAppLoader() {
            const loader = document.getElementById('appGlobalLoader');
            if (!loader) {
                return;
            }

            appLoaderState.activeCount = Math.max(0, appLoaderState.activeCount - 1);

            if (appLoaderState.activeCount > 0) {
                return;
            }

            appLoaderState.hideTimer = window.setTimeout(() => {
                if (appLoaderState.activeCount === 0) {
                    loader.classList.remove('is-visible');
                    loader.setAttribute('aria-hidden', 'true');
                    setAppLoaderMessage('Loading, please wait...');
                }
            }, 150);
        }

        async function withAppLoader(task, message = 'Loading, please wait...') {
            showAppLoader(message);

            try {
                return await task();
            } finally {
                hideAppLoader();
            }
        }

        const appConfirmState = {
            resolver: null
        };

        function closeAppConfirmModal(confirmed = false) {
            const modal = document.getElementById('appConfirmModal');
            if (modal) {
                modal.classList.remove('is-visible');
                modal.setAttribute('aria-hidden', 'true');
            }

            if (appConfirmState.resolver) {
                appConfirmState.resolver(confirmed);
                appConfirmState.resolver = null;
            }
        }

        function requestAppConfirmation(message, options = {}) {
            const modal = document.getElementById('appConfirmModal');
            const titleNode = document.getElementById('appConfirmModalTitle');
            const messageNode = document.getElementById('appConfirmModalMessage');
            const cancelButton = document.getElementById('appConfirmModalCancel');
            const confirmButton = document.getElementById('appConfirmModalApprove');

            if (!modal || !titleNode || !messageNode || !cancelButton || !confirmButton) {
                return Promise.resolve(window.confirm(message || 'Are you sure you want to continue?'));
            }

            titleNode.textContent = options.title || 'Delete item';
            messageNode.textContent = message || 'Are you sure you want to continue?';
            confirmButton.textContent = options.confirmText || 'Delete';
            cancelButton.textContent = options.cancelText || 'Cancel';

            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');

            return new Promise((resolve) => {
                appConfirmState.resolver = resolve;
                cancelButton.focus();
            });
        }

        document.addEventListener('click', function(event) {
            if (event.target?.id === 'appConfirmModalCancel') {
                closeAppConfirmModal(false);
            }

            if (event.target?.id === 'appConfirmModalApprove') {
                closeAppConfirmModal(true);
            }

            if (event.target?.id === 'appConfirmModal') {
                closeAppConfirmModal(false);
            }
        });

        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('appConfirmModal');
            if (!modal || !modal.classList.contains('is-visible')) {
                return;
            }

            if (event.key === 'Escape') {
                closeAppConfirmModal(false);
            }
        });

        window.showAppLoader = showAppLoader;
        window.hideAppLoader = hideAppLoader;
        window.withAppLoader = withAppLoader;
        window.requestAppConfirmation = requestAppConfirmation;

        async function refreshAsyncTargets(targets) {
            return refreshAsyncTargetsFromUrl(window.location.href, targets, false);
        }

        function focusAsyncPaginationTarget(selector) {
            if (!selector) return;

            const section = document.querySelector(selector);
            if (!section) return;

            const focusTarget =
                section.querySelector('.table-responsive') ||
                section.querySelector('table') ||
                section;

            if (!focusTarget.hasAttribute('tabindex')) {
                focusTarget.setAttribute('tabindex', '-1');
            }

            focusTarget.focus({ preventScroll: true });
            focusTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        async function refreshAsyncTargetsFromUrl(url, targets, updateHistory = true, options = {}) {
            if (!targets || !targets.length) return;

            return withAppLoader(async () => {
                const preserveScroll = options.preserveScroll === true;
                const previousWindowScrollX = window.scrollX;
                const previousWindowScrollY = window.scrollY;
                const refreshUrl = new URL(url, window.location.origin);
                refreshUrl.searchParams.set('_async_refresh', Date.now().toString());

                const response = await fetch(refreshUrl.toString(), {
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (!response.ok) {
                    throw new Error('Unable to refresh the updated section.');
                }

                const html = await response.text();
                const parser = new DOMParser();
                const nextDocument = parser.parseFromString(html, 'text/html');

                targets.forEach((selector) => {
                    const currentNode = document.querySelector(selector);
                    const nextNode = nextDocument.querySelector(selector);

                    if (currentNode && nextNode) {
                        currentNode.replaceWith(nextNode);
                    }
                });

                document.dispatchEvent(new CustomEvent('app:async-refreshed', {
                    detail: {
                        targets,
                        url
                    }
                }));

                if (updateHistory) {
                    window.history.pushState({}, '', url);
                }

                if (preserveScroll) {
                    window.scrollTo(previousWindowScrollX, previousWindowScrollY);
                } else {
                    focusAsyncPaginationTarget(targets[0]);
                }
            }, 'Loading content...');
        }

        async function submitAsyncForm(form, options = {}) {
            const targetSelectors = (options.targets ?? form.dataset.asyncTarget ?? '')
                .split(',')
                .map((selector) => selector.trim())
                .filter(Boolean);
            const resetForm = options.resetForm ?? form.dataset.asyncReset === 'true';
            const closeSelector = options.closeSelector ?? form.dataset.asyncClose;
            const confirmMessage = options.confirmMessage ?? form.dataset.asyncConfirm;
            const successModalSelector = options.successModalSelector ?? form.dataset.asyncSuccessModal;
            const suppressSuccessFeedback = options.suppressSuccessFeedback ?? form.dataset.asyncSuccess === 'silent';
            const successTitle = options.successTitle ?? form.dataset.asyncSuccessTitle ?? 'Success';

            if (!validateFormBeforeSubmit(form)) {
                return false;
            }

            if (confirmMessage) {
                const confirmed = await requestAppConfirmation(confirmMessage, {
                    title: 'Delete item',
                    confirmText: 'Delete'
                });

                if (!confirmed) {
                    return false;
                }
            }

            let submitter = options.submitter ?? document.activeElement;

            try {
                const formData = new FormData(form);
                if (submitter && typeof submitter.disabled !== 'undefined') {
                    submitter.disabled = true;
                }

                form.classList.add('is-loading');
                showAppLoader(form.dataset.asyncLoadingText || 'Processing request...');

                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json') ? await response.json() : {};

                if (!response.ok) {
                    if (payload.errors && Object.keys(payload.errors).length > 0) {
                        throw new Error(Object.values(payload.errors).flat().join(' '));
                    }
                    throw new Error(payload.message || 'Unable to save changes.');
                }

                if (targetSelectors.length > 0) {
                    await refreshAsyncTargetsFromUrl(window.location.href, targetSelectors, false, {
                        preserveScroll: form.dataset.asyncPreserveScroll === 'true'
                    });
                }

                if (resetForm) {
                    form.reset();
                }

                if (closeSelector) {
                    const modal = document.querySelector(closeSelector);
                    if (modal) {
                        modal.classList.remove('active');
                    }
                }

                if (!suppressSuccessFeedback) {
                    const successMessage = payload.message || 'Changes saved successfully.';
                    const openedSuccessModal = openAsyncSuccessModal(successModalSelector, successMessage, successTitle);

                    if (!openedSuccessModal) {
                        showLiveAlert(successMessage, 'success');
                    }
                }
                return false;
            } catch (error) {
                showLiveAlert(error.message || 'Unable to save changes.', 'error');
                return false;
            } finally {
                form.classList.remove('is-loading');
                hideAppLoader();
                if (submitter && typeof submitter.disabled !== 'undefined') {
                    submitter.disabled = false;
                }
            }
        }

        function handleAjaxSubmit(event, targets = '', confirmMessage = null, resetForm = false, closeSelector = null) {
            event.preventDefault();
            const form = event.target.closest('form');
            if (!form) return false;

            submitAsyncForm(form, {
                targets,
                confirmMessage,
                resetForm,
                closeSelector,
                submitter: event.submitter
            });

            return false;
        }

        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.dataset.asyncGet === 'true') {
                event.preventDefault();

                const targets = (form.dataset.asyncTarget || '')
                    .split(',')
                    .map((selector) => selector.trim())
                    .filter(Boolean);

                const formData = new FormData(form);
                const url = new URL(form.action || window.location.href, window.location.origin);
                url.search = '';

                formData.forEach((value, key) => {
                    if (value === null || typeof value === 'undefined') {
                        return;
                    }

                    const stringValue = String(value).trim();
                    if (!stringValue) {
                        return;
                    }

                    url.searchParams.append(key, stringValue);
                });

                refreshAsyncTargetsFromUrl(url.toString(), targets, true, {
                    preserveScroll: form.dataset.asyncPreserveScroll === 'true'
                }).catch((error) => {
                    showLiveAlert(error.message || 'Unable to apply filters.', 'error');
                });
                return;
            }

            if (!form.dataset.asyncTarget && form.dataset.async !== 'true') {
                if (!validateFormBeforeSubmit(form)) {
                    event.preventDefault();
                }
                return;
            }

            event.preventDefault();
            submitAsyncForm(form, {
                submitter: event.submitter
            });
        });

        document.addEventListener('change', function(event) {
            const input = event.target;
            if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement) {
                setFieldValidityState(input);
            }

            if (!(input instanceof HTMLSelectElement) || !input.hasAttribute('data-auto-submit')) {
                return;
            }

            const form = input.closest('form');
            if (!form) return;

            submitAsyncForm(form, { submitter: input });
        });

        document.addEventListener('click', function(event) {
            const link = event.target.closest('a[data-async-pagination]');
            if (!link || link.classList.contains('disabled') || !link.getAttribute('href')) {
                return;
            }

            event.preventDefault();

            const targets = (link.dataset.asyncTarget || '')
                .split(',')
                .map((selector) => selector.trim())
                .filter(Boolean);

            refreshAsyncTargetsFromUrl(link.href, targets, true, {
                preserveScroll: link.dataset.asyncPreserveScroll === 'true'
            }).catch((error) => {
                showLiveAlert(error.message || 'Unable to change page.', 'error');
            });
        });

        document.addEventListener('input', function(event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) {
                return;
            }

            setFieldValidityState(input);
        });

        document.addEventListener('invalid', function(event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                return;
            }

            setFieldValidityState(input);
        }, true);

        document.addEventListener('reset', function(event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const fileInput = form.querySelector('#file-input');
            const fileList = document.getElementById('file-list');
            const submitBtn = document.getElementById('submit-btn');

            if (!fileInput || !fileList || !submitBtn) {
                return;
            }

            window.setTimeout(() => {
                fileList.innerHTML = '<div class="empty-state">No file selected.</div>';
                submitBtn.style.display = 'none';
            }, 0);
        });

        function toggleMenu(menuId, element) {
            const menu = document.getElementById(menuId);
            if(menu) { menu.classList.toggle('open'); element.classList.toggle('open'); }
        }

        function syncSidebarToggleButtons() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            const isDesktop = window.innerWidth > 900;
            const isOpen = isDesktop ? !sidebar.classList.contains('collapsed') : sidebar.classList.contains('open');

            document.querySelectorAll('.sidebar-toggle-btn').forEach((button) => {
                button.classList.toggle('is-active', isOpen);
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (window.innerWidth > 900) {
                sidebar.classList.toggle('collapsed');
            } else {
                sidebar.classList.toggle('open');
                if(sidebar.classList.contains('open')) {
                    overlay.classList.add('show');
                } else {
                    overlay.classList.remove('show');
                }
            }

            syncSidebarToggleButtons();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebarOverlay').classList.remove('show');
            } else {
                document.getElementById('sidebar').classList.remove('collapsed');
            }

            syncSidebarToggleButtons();
        });

        document.addEventListener('DOMContentLoaded', syncSidebarToggleButtons);
    </script>
</body>

</html>
