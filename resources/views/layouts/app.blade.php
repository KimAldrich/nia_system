<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'System Dashboard')</title>
    <style>
        :root {
            --primary: #0b5e2c;
            --primary-dark: #084721;

            /* New Dark Sidebar Colors */
            --sidebar-bg: #18181b;
            --sidebar-hover: #27272a;
            --sidebar-active: #3f3f46;
            --sidebar-text: #d4d4d8;
            --sidebar-icon: #a1a1aa;
            --tree-line: #52525b;

            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #334155;
            --border-color: #e2e8f0;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Dark Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        .sidebar-header {
            padding: 24px 20px;
            font-size: 20px;
            font-weight: bold;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-links {
            flex: 1;
            padding: 20px 15px;
            overflow-y: auto;
        }

        /* Parent Menu Item */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 5px;
        }

        .menu-item:hover {
            background-color: var(--sidebar-hover);
        }

        .menu-item.active {
            background-color: var(--sidebar-active);
            color: white;
        }

        .menu-item svg {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            stroke: var(--sidebar-icon);
        }

        .menu-item.active svg {
            stroke: white;
        }

        .menu-item .chevron {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .menu-item.open .chevron {
            transform: rotate(180deg);
        }

        /* Tree Sub-Menu */
        .sub-menu {
            display: none;
            padding-left: 36px;
            margin-top: -5px;
            margin-bottom: 15px;
        }

        .sub-menu.open {
            display: block;
        }

        .sub-item {
            position: relative;
            display: block;
            padding: 10px 15px;
            color: #a1a1aa;
            text-decoration: none;
            font-size: 14px;
            border-radius: 8px;
            margin-top: 2px;
        }

        .sub-item:hover,
        .sub-item.active {
            background-color: var(--sidebar-hover);
            color: white;
        }

        /* The Curved Branch Line */
        .sub-item::before {
            content: '';
            position: absolute;
            left: -11px;
            top: -15px;
            bottom: 50%;
            width: 15px;
            border-left: 2px solid var(--tree-line);
            border-bottom: 2px solid var(--tree-line);
            border-bottom-left-radius: 10px;
        }

        /* The Straight Line Continuing Down */
        .sub-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -11px;
            top: 50%;
            bottom: -15px;
            border-left: 2px solid var(--tree-line);
        }

        /* Logout Section */
        .logout-container {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logout-btn {
            width: 100%;
            padding: 12px;
            background-color: transparent;
            color: #ef4444;
            border: 1px solid #ef4444;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: #ef4444;
            color: white;
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            min-height: 70px;
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 12px 30px;
            border-bottom: 1px solid var(--border-color);
            overflow: hidden;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            line-height: 1.2;
            width: min(100%, 280px);
            min-width: 280px;
            text-align: right;
            margin-left: auto;
        }

        .user-meta-label {
            width: 100%;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: right;
        }

        .user-meta-name {
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
            word-break: break-word;
            text-align: right;
        }

        .user-meta-team {
            width: 100%;
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            word-break: break-word;
            white-space: normal;
            text-align: right;
        }

        .content {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }

        .page-title {
            margin-top: 0;
            color: #0f172a;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-progress {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-completed {
            background: #d1fae5;
            color: #059669;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            NIA Portal
        </div>
        <div class="nav-links">
            @if(auth()->check() && auth()->user()->role == 'admin')

                <div style="padding: 0 15px 10px 15px; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">
                    Admin Controls
                </div>

                <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Home</span>
                </a>

                <a href="{{ route('admin.users') }}" class="menu-item {{ request()->routeIs('admin.users') ? 'active' : '' }}" style="margin-bottom: 25px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>User Management</span>
                </a>

            @endif
           @if(auth()->check() && auth()->user()->role == 'guest')

                <div style="padding: 20px 15px 10px 15px; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">
                    Guest Portal
                </div>

                <a href="{{ route('guest.dashboard') }}" class="menu-item active" style="text-decoration: none;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span>Master Dashboard</span>
                </a>

            @else

                @endif

            <div style="padding: 0 15px 10px 15px; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">
                Departments
            </div>

            @php
                // Updated to match your exact database values
                $teams = [
                    'fs-team' => 'Feasibility Study Team',
                    'rpwsis_team' => 'RP-WSIS Team',
                    'cm_team' => 'Contract Management Team',
                    'row_team' => 'Right Of Way Team',
                    'pcr_team' => 'Program Completion Report Team',
                    'pao_team' => 'Programming Team'
                ];
                $activeTeam = request()->segment(1);
            @endphp

            @foreach($teams as $slug => $name)
                <div class="menu-item {{ $activeTeam == $slug ? 'active open' : '' }}"
                    onclick="toggleMenu('menu-{{ $slug }}', this)">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span
                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;">{{ $name }}</span>
                    <svg class="chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sub-menu {{ $activeTeam == $slug ? 'open' : '' }}" id="menu-{{ $slug }}">
                    <a href="/{{ $slug }}/dashboard"
                        class="sub-item {{ request()->is($slug . '/dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="/{{ $slug }}/downloadables"
                        class="sub-item {{ request()->is($slug . '/downloadables') ? 'active' : '' }}">Downloadables</a>
                    <a href="/{{ $slug }}/ia-resolutions"
                        class="sub-item {{ request()->is($slug . '/ia-resolutions') ? 'active' : '' }}">IA Resolutions</a>
                </div>
            @endforeach
            <div style="margin-top: 25px; margin-bottom: 8px; padding: 0 15px; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                Shared Hubs
            </div>
            <a href="{{ route('administrative.index') }}" class="menu-item {{ request()->routeIs('administrative.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Administrative</span>
            </a>
          <div class="menu-item" onclick="window.location.href='/map'">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.553-.832L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-1.553-.832L15 9m0 8V9m0 0L9 7">
                </path>
            </svg>

            <span>Map</span>
        </div>
        </div>

        <div class="logout-container">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">Log Out</button>
            </form>
        </div>
    </div>

    <div class="main-wrapper">
        @php
            $roleLabels = [
                'admin' => 'Administrator',
                'guest' => 'Guest User',
                'fs_team' => 'FS Member',
                'rpwsis_team' => 'RP-WSIS Team Member',
                'cm_team' => 'Contract Management Team Member',
                'row_team' => 'Right Of Way Team Member',
                'pcr_team' => 'Program Completion Report Team Member',
                'pao_team' => 'Programming Team Member',
            ];

            $currentUser = auth()->user();
            $currentUserTeam = $roleLabels[$currentUser->role ?? ''] ?? 'User';
        @endphp

        <div class="topbar">
            <div class="user-meta">
                <div class="user-meta-label">Logged in as</div>
                <div class="user-meta-name">{{ $currentUser->name ?? 'User' }}</div>
                <div class="user-meta-team">{{ $currentUserTeam }}</div>
            </div>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>

    <script>
        function toggleMenu(menuId, element) {
            const menu = document.getElementById(menuId);
            menu.classList.toggle('open');
            element.classList.toggle('open');
        }
    </script>
</body>

</html>
