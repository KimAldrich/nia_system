@extends('layouts.app')
@section('title', 'Administrative Hub')

@section('content')
    <h1 class="page-title">Administrative Documents Hub</h1>
    <p style="color: #64748b; margin-top: -15px; margin-bottom: 30px; font-size: 14px;">A shared workspace for all teams to upload and access administrative files.</p>

    @if(session('success'))
        <div style="background: #18181b; color: #ffffff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px; height:18px; color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500;">
            @foreach ($errors->all() as $error) <div>⚠️ {{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
        <div class="main-column">
            <div class="ui-card card">
                
                <div style="display: flex; gap: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px;">
                    <button onclick="switchTab('memorandums')" id="btn-memorandums" style="background: #18181b; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;">Memorandum Circulars</button>
                    <button onclick="switchTab('minutes')" id="btn-minutes" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;">Minutes of the Meeting</button>
                </div>

                <div id="tab-memorandums">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead><tr><th>Document Title</th><th>Uploaded By</th><th>Date</th><th style="text-align: right;">Actions</th></tr></thead>
                        <tbody>
                            @forelse($memorandums as $doc)
                                <tr>
                                    <td><strong>{{ $doc->title }}</strong><br><span style="font-size: 11px; color: #a1a1aa;">{{ $doc->original_name }}</span></td>
                                    <td><span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">{{ strtoupper(str_replace('_', ' ', $doc->team_role)) }}</span></td>
                                    <td style="font-size: 12px; color: #64748b;">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="padding: 6px 12px; background: #18181b; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">View</a>
                                            @if(auth()->user()->role == $doc->team_role || auth()->user()->role == 'admin')
                                                <form action="{{ route('administrative.destroy', $doc->id) }}" method="POST" style="margin: 0;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="padding: 6px 12px; background: #f87171; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align: center; color: #a1a1aa; padding: 30px;">No Memorandums uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="tab-minutes" style="display: none;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead><tr><th>Document Title</th><th>Uploaded By</th><th>Date</th><th style="text-align: right;">Actions</th></tr></thead>
                        <tbody>
                            @forelse($minutes as $doc)
                                <tr>
                                    <td><strong>{{ $doc->title }}</strong><br><span style="font-size: 11px; color: #a1a1aa;">{{ $doc->original_name }}</span></td>
                                    <td><span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">{{ strtoupper(str_replace('_', ' ', $doc->team_role)) }}</span></td>
                                    <td style="font-size: 12px; color: #64748b;">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="padding: 6px 12px; background: #18181b; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">View</a>
                                            @if(auth()->id() == $doc->user_id || auth()->user()->role == 'admin')
                                                <form action="{{ route('administrative.destroy', $doc->id) }}" method="POST" style="margin: 0;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="padding: 6px 12px; background: #f87171; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align: center; color: #a1a1aa; padding: 30px;">No Minutes uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="side-column">
            <div class="ui-card card">
                <h3 style="margin-top: 0; font-size: 16px; color: #0f172a; margin-bottom: 20px;">Upload Document</h3>
                <form action="{{ route('administrative.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Document Type</label>
                        <select name="document_type" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; outline: none;">
                            <option value="memorandum">Memorandum Circular</option>
                            <option value="minutes">Minutes of the Meeting</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Document Title</label>
                        <input type="text" name="title" required placeholder="e.g. Q3 Regional Update" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; outline: none;">
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Select File (PDF, Word, Excel)</label>
                        <input type="file" name="file" required style="width: 100%; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; font-size: 12px;">
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; font-weight: 600;">Upload to Hub</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide both tables
            document.getElementById('tab-memorandums').style.display = 'none';
            document.getElementById('tab-minutes').style.display = 'none';
            
            // Reset button styles
            document.getElementById('btn-memorandums').style.background = '#f1f5f9';
            document.getElementById('btn-memorandums').style.color = '#475569';
            document.getElementById('btn-minutes').style.background = '#f1f5f9';
            document.getElementById('btn-minutes').style.color = '#475569';

            // Show selected table & highlight button
            document.getElementById('tab-' + tabName).style.display = 'block';
            document.getElementById('btn-' + tabName).style.background = '#18181b';
            document.getElementById('btn-' + tabName).style.color = 'white';
        }
    </script>
@endsection