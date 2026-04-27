@extends('layouts.app')
@section('title', 'Administrative Hub')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #18181b;
            --primary-hover: #3f3f46;
            --bg-main: #f7f8fa;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
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

        .ui-card { background: #ffffff; border-radius: 20px; padding: 28px; border: 1px solid var(--border-color); box-shadow: 0 16px 40px rgba(15, 23, 42, 0.03); overflow: hidden; }
        
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .section-title { font-size: 18px; font-weight: 600; color: var(--primary); margin: 0; }

        /* Tabs Navigation */
        .tab-container { display: flex; gap: 8px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #eef2f7; }
        .tab-btn { padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s; border: none; font-family: 'Inter', sans-serif; outline: none; }
        .tab-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(24, 24, 27, 0.1); }
        .tab-btn.inactive { background: #f1f5f9; color: var(--text-muted); }
        .tab-btn.inactive:hover { background: #e2e8f0; color: var(--text-main); }

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
        
        .file-input { padding: 12px 16px; background: #f8fafc; border: 2px dashed #cbd5e1; cursor: pointer; color: var(--text-muted); }
        .file-input::file-selector-button { background: #e2e8f0; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; color: #475569; margin-right: 14px; cursor: pointer; transition: 0.2s; font-family: 'Inter', sans-serif; font-size: 12px;}
        .file-input::file-selector-button:hover { background: #cbd5e1; color: #1e293b; }

        .btn-dark { background: linear-gradient(135deg, #18181b 0%, #27272a 100%); color: white; padding: 15px 18px; border-radius: 14px; font-size: 15px; font-weight: 700; width: 100%; border: none; cursor: pointer; transition: 0.2s; margin-top: 6px; box-shadow: 0 12px 24px rgba(24, 24, 27, 0.14); }
        .btn-dark:hover { background: linear-gradient(135deg, #09090b 0%, #18181b 100%); transform: translateY(-1px); }

        /* Table Styling */
        .table-container { overflow: visible; width: 100%; border: 1px solid #eef2f7; border-radius: 18px; background: #fbfdff; }
        .sleek-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 0; table-layout: fixed; }
        .sleek-table th { text-align: left; padding: 14px 15px; color: #64748b; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.07em; border-bottom: 1px solid #e9eef5; background: #f8fafc; line-height: 1.4; }
        .sleek-table td { padding: 15px; border-bottom: 1px solid #eef2f7; font-size: 13px; color: var(--text-main); vertical-align: middle; background: #ffffff; white-space: normal; word-break: break-word;}
        .sleek-table tbody tr:hover td { background-color: #fcfdff; }
        .sleek-table tbody tr:last-child td { border-bottom: none; }

        .role-badge { display: inline-flex; align-items: center; min-height: 30px; padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; line-height: 1.35; border: 1px solid #e2e8f0; }

        /* Buttons in Table */
        .btn-danger, .btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 16px; background: #ffffff; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-danger { color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
        .btn-secondary { color: #0f172a; border: 1px solid #cbd5e1; background: #f8fafc; }
        .btn-secondary:hover { background: #ffffff; border-color: #94a3b8; }

        .alert-box { display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 14px; margin-bottom: 24px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #18181b; color: #fff; box-shadow: 0 10px 25px rgba(24,24,27,0.1); }
        .alert-success svg { color: #4ade80; }
    </style>

    <div class="content-wrapper">
        <div class="header-section">
            <h1 class="header-title">Administrative Documents Hub</h1>
            <p class="header-desc">A shared workspace for all teams to upload and access administrative files.</p>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="dashboard-grid">
            <div class="main-column">
                <div class="ui-card">
                    
                    <div class="tab-container">
                        <button onclick="switchTab('memorandums')" id="btn-memorandums" class="tab-btn active">Memorandum Circulars</button>
                        <button onclick="switchTab('minutes')" id="btn-minutes" class="tab-btn inactive">Minutes of the Meeting</button>
                    </div>

                    <div id="tab-memorandums">
                        <div class="table-container">
                            <table class="sleek-table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Document Title</th>
                                        <th style="width: 20%;">Uploaded By</th>
                                        <th style="width: 15%;">Date</th>
                                        <th style="width: 20%; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($memorandums as $doc)
                                        <tr>
                                            <td>
                                                <strong style="color: var(--primary); font-size: 13px;">{{ $doc->title }}</strong><br>
                                                <span style="font-size: 11px; color: var(--text-muted); line-height: 1.6;">{{ $doc->original_name }}</span>
                                            </td>
                                            <td><span class="role-badge">{{ strtoupper(str_replace('_', ' ', $doc->team_role)) }}</span></td>
                                            <td style="font-size: 12px; color: var(--text-muted);">{{ $doc->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn-secondary">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> 
                                                        View
                                                    </a>
                                                    @if(auth()->user()->role == $doc->team_role || auth()->user()->role == 'admin')
                                                        <form action="{{ route('administrative.destroy', $doc->id) }}" method="POST" style="margin: 0;" data-async-target="#tab-memorandums, #tab-minutes" data-async-confirm="Delete this document?">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn-danger">
                                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> 
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="text-align: center; color: #a1a1aa; padding: 40px 0;">No Memorandums uploaded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-minutes" style="display: none;">
                        <div class="table-container">
                            <table class="sleek-table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Document Title</th>
                                        <th style="width: 20%;">Uploaded By</th>
                                        <th style="width: 15%;">Date</th>
                                        <th style="width: 20%; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($minutes as $doc)
                                        <tr>
                                            <td>
                                                <strong style="color: var(--primary); font-size: 13px;">{{ $doc->title }}</strong><br>
                                                <span style="font-size: 11px; color: var(--text-muted); line-height: 1.6;">{{ $doc->original_name }}</span>
                                            </td>
                                            <td><span class="role-badge">{{ strtoupper(str_replace('_', ' ', $doc->team_role)) }}</span></td>
                                            <td style="font-size: 12px; color: var(--text-muted);">{{ $doc->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn-secondary">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> 
                                                        View
                                                    </a>
                                                    @if(auth()->id() == $doc->user_id || auth()->user()->role == 'admin')
                                                        <form action="{{ route('administrative.destroy', $doc->id) }}" method="POST" style="margin: 0;" data-async-target="#tab-memorandums, #tab-minutes" data-async-confirm="Delete this document?">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn-danger">
                                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> 
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="text-align: center; color: #a1a1aa; padding: 40px 0;">No Minutes uploaded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <div class="side-column">
                <div class="ui-card">
                    <div class="form-card-header">
                        <h3 class="section-title">Upload Document</h3>
                        <p class="form-card-subtitle">Upload official files to the central repository. Files are accessible to all departments.</p>
                    </div>

                    <form action="{{ route('administrative.store') }}" method="POST" enctype="multipart/form-data" data-async-target="#tab-memorandums, #tab-minutes" data-async-reset="true" class="register-form">
                        @csrf
                        
                        <div class="form-group">
                            <label class="form-label">Document Type</label>
                            <select name="document_type" required class="form-input">
                                <option value="memorandum">Memorandum Circular</option>
                                <option value="minutes">Minutes of the Meeting</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Document Title</label>
                            <input type="text" name="title" required placeholder="e.g. Q3 Regional Update" maxlength="255" class="form-input">
                        </div>

                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label">Select File (PDF, Word, Excel)</label>
                            <input type="file" name="file" required class="form-input file-input">
                        </div>

                        <button type="submit" class="btn-dark">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px; vertical-align: text-bottom;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload to Hub
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide both tables
            document.getElementById('tab-memorandums').style.display = 'none';
            document.getElementById('tab-minutes').style.display = 'none';
            
            // Reset button classes
            document.getElementById('btn-memorandums').className = 'tab-btn inactive';
            document.getElementById('btn-minutes').className = 'tab-btn inactive';

            // Show selected table & highlight button
            document.getElementById('tab-' + tabName).style.display = 'block';
            document.getElementById('btn-' + tabName).className = 'tab-btn active';
        }
    </script>
@endsection