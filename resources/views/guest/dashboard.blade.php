<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIA Guest Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --primary: #15803d; /* Clean NIA Green */
            --border: #e5e7eb;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Clean White Header */
        .guest-header {
            background-color: var(--card-bg);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .guest-header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-main);
        }

        .logout-btn {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fca5a5;
        }

        /* Main Container */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Clean Pill Navigation */
        .guest-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 30px;
            overflow-x: auto;
            scrollbar-width: none;
            padding-bottom: 5px;
        }

        .guest-nav::-webkit-scrollbar {
            display: none;
        }

        .nav-tab {
            padding: 8px 16px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            border-radius: 20px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .nav-tab:hover {
            color: var(--text-main);
            background: #f3f4f6;
        }

        .nav-tab.active {
            background: var(--text-main);
            color: #ffffff;
        }

        /* Content Panels */
        .team-panel {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .team-panel.active {
            display: block;
        }

        .panel-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-main);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Minimalist File Grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .file-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            background: var(--card-bg);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .file-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        .file-icon {
            font-size: 28px;
            background: #f3f4f6;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .file-info {
            flex: 1;
            min-width: 0; /* Enables text truncation */
        }

        .file-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-info p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            border: 1px dashed var(--border);
            border-radius: 10px;
            color: var(--text-muted);
            background: var(--card-bg);
            font-size: 14px;
        }
    </style>
</head>
<body>

    <header class="guest-header">
        <h1>
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            NIA Document Portal
        </h1>
        
        <form action="{{ route('guest.logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">Log out</button>
        </form>
    </header>

    <div class="container">
        @php
            $teams = [
                'fs' => ['role' => 'fs_team', 'title' => 'Feasibility Study'],
                'rpwsis' => ['role' => 'rpwsis_team', 'title' => 'RP-WSIS'],
                'cm' => ['role' => 'cm_team', 'title' => 'Contract Management'],
                'row' => ['role' => 'row_team', 'title' => 'Right Of Way'],
                'pcr' => ['role' => 'pcr_team', 'title' => 'Program Completion'],
                'pao' => ['role' => 'pao_team', 'title' => 'Programming']
            ];
        @endphp

        <div class="guest-nav">
            <button class="nav-tab active" onclick="switchTab('all', this)">All Documents</button>
            @foreach($teams as $id => $data)
                <button class="nav-tab" onclick="switchTab('{{ $id }}', this)">{{ $data['title'] }}</button>
            @endforeach
        </div>

        <div id="panel-all" class="team-panel active">
            <h3 class="panel-title">All Available Documents</h3>
            
            <div class="file-grid">
                @forelse($downloadables as $file)
                    @php $extension = pathinfo($file->file_path, PATHINFO_EXTENSION); @endphp
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="file-card">
                        <div class="file-icon">
                            @if(in_array(strtolower($extension), ['pdf'])) 📕 
                            @elseif(in_array(strtolower($extension), ['xls', 'xlsx'])) 📊 
                            @elseif(in_array(strtolower($extension), ['doc', 'docx'])) 📝 
                            @else 📁 @endif
                        </div>
                        <div class="file-info">
                            <h4>{{ $file->title }}</h4>
                            <p>{{ $file->original_name }}</p>
                        </div>
                    </a>
                @empty
                    <div class="empty-state">No documents are currently available.</div>
                @endforelse
            </div>
        </div>

        @foreach($teams as $id => $data)
            <div id="panel-{{ $id }}" class="team-panel">
                <h3 class="panel-title">{{ $data['title'] }} Documents</h3>
                
                <div class="file-grid">
                    @php $teamFiles = $downloadables->where('team', $data['role']); @endphp
                    
                    @forelse($teamFiles as $file)
                        @php $extension = pathinfo($file->file_path, PATHINFO_EXTENSION); @endphp
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="file-card">
                            <div class="file-icon">
                                @if(in_array(strtolower($extension), ['pdf'])) 📕 
                                @elseif(in_array(strtolower($extension), ['xls', 'xlsx'])) 📊 
                                @elseif(in_array(strtolower($extension), ['doc', 'docx'])) 📝 
                                @else 📁 @endif
                            </div>
                            <div class="file-info">
                                <h4>{{ $file->title }}</h4>
                                <p>{{ $file->original_name }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">No documents have been uploaded by {{ $data['title'] }} yet.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function switchTab(teamId, clickedButton) {
            document.querySelectorAll('.team-panel').forEach(panel => panel.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(btn => btn.classList.remove('active'));
            
            const activePanel = document.getElementById('panel-' + teamId);
            if(activePanel) activePanel.classList.add('active');
            
            clickedButton.classList.add('active');
        }
    </script>
</body>
</html>