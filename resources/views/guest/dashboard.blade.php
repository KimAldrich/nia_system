<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIA Guest Portal</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #0b5e2c;
            --border: #e2e8f0;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
        }

        /* Dedicated Guest Header */
        .guest-header {
            background-color: #18181b;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .guest-header h1 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: transparent;
            color: #f87171;
            border: 1px solid #f87171;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: #f87171;
            color: white;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Top Horizontal Navbar */
        .guest-nav {
            display: flex;
            gap: 10px;
            background: var(--card-bg);
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            overflow-x: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .nav-tab {
            padding: 10px 20px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .nav-tab:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        .nav-tab.active {
            background: var(--primary);
            color: #ffffff;
        }

        /* Content Panels */
        .team-panel {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .team-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* File Grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .file-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            background: #fafafa;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .btn-view {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            background: #18181b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 15px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <header class="guest-header">
        <h1>
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            NIA Public Document Portal
        </h1>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">Secure Logout</button>
        </form>
    </header>

    <div class="container">
        {{-- <div style="margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 28px;">Welcome, Guest</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Select a department below to view their official, read-only documents.</p>
        </div> --}}

        @php
            // We define the teams and their exact database roles here to keep the code clean!
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
            <h3 style="margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 10px;">All Available Downloadables</h3>
            
            <div class="file-grid">
                @forelse($downloadables as $file)
                    @php $extension = pathinfo($file->file_path, PATHINFO_EXTENSION); @endphp
                    <div class="file-card">
                        <div>
                            <div style="font-size: 32px; margin-bottom: 10px; text-align: center;">
                                @if(in_array(strtolower($extension), ['pdf'])) 📕 
                                @elseif(in_array(strtolower($extension), ['xls', 'xlsx'])) 📊 
                                @elseif(in_array(strtolower($extension), ['doc', 'docx'])) 📝 
                                @else 📁 @endif
                            </div>
                            <h4 style="margin: 0 0 5px 0; font-size: 14px;">{{ $file->title }}</h4>
                            <p style="font-size: 12px; color: var(--text-muted); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $file->original_name }}</p>
                        </div>
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-view">View Document</a>
                    </div>
                @empty
                    <p style="color: var(--text-muted); grid-column: 1 / -1; text-align: center; padding: 40px;">No documents are currently available in the system.</p>
                @endforelse
            </div>
        </div>

        @foreach($teams as $id => $data)
            <div id="panel-{{ $id }}" class="team-panel">
                <h3 style="margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 10px;">{{ $data['title'] }} Documents</h3>
                
                <div class="file-grid">
                    {{-- Magic happens here: We filter the main list to only show files belonging to this specific team! --}}
                    @php $teamFiles = $downloadables->where('team', $data['role']); @endphp
                    
                    @forelse($teamFiles as $file)
                        @php $extension = pathinfo($file->file_path, PATHINFO_EXTENSION); @endphp
                        <div class="file-card">
                            <div>
                                <div style="font-size: 32px; margin-bottom: 10px; text-align: center;">
                                    @if(in_array(strtolower($extension), ['pdf'])) 📕 
                                    @elseif(in_array(strtolower($extension), ['xls', 'xlsx'])) 📊 
                                    @elseif(in_array(strtolower($extension), ['doc', 'docx'])) 📝 
                                    @else 📁 @endif
                                </div>
                                <h4 style="margin: 0 0 5px 0; font-size: 14px;">{{ $file->title }}</h4>
                                <p style="font-size: 12px; color: var(--text-muted); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $file->original_name }}</p>
                            </div>
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-view">View Document</a>
                        </div>
                    @empty
                        <p style="color: var(--text-muted); grid-column: 1 / -1; text-align: center; padding: 40px;">No documents have been uploaded by the {{ $data['title'] }} yet.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

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