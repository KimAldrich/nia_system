@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --team-accent: #0c4d05;
            --team-accent-dark: #126e08;
            --team-soft: #f0fdf4;
            --team-border: #0c4d05;
        }

        * { box-sizing: border-box; }

        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: var(--team-accent);
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            color: var(--team-accent);
        }

        .header-desc {
            color: #a1a1aa;
            font-size: 13px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
            scrollbar-width: thin;
        }

        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }

        .sleek-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sleek-table th {
            text-align: left;
            padding: 12px 15px;
            color: #94a3b8;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f1f5f9;
            background: var(--team-soft);
            white-space: normal;
            vertical-align: middle;
            line-height: 1.4;
        }

        .sleek-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .sleek-table tr:hover td {
            background-color: #f8fafc;
            transition: 0.2s;
        }

        .sleek-table tr:last-child td {
            border-bottom: none;
        }

        .file-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .file-meta {
            font-size: 11px;
            font-weight: 500;
            color: #94a3b8;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 78px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--team-soft);
            color: var(--team-accent);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: var(--team-accent-dark);
            color: #ffffff;
            border: 1px solid var(--team-accent-dark);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            min-width: 104px;
            white-space: nowrap;
        }

        .btn-download:hover {
            background: var(--team-accent);
            border-color: var(--team-accent);
        }

        .empty-state {
            text-align: center;
            color: #a1a1aa;
            padding: 30px 0;
        }
    </style>

    <h1 class="header-title">{{ $pageTitle }}</h1>
    <p class="header-desc">Browse official {{ $teamLabel ?? 'team' }} documents. Files are view-only on the guest side.</p>

    <div class="ui-card">
        <div class="table-responsive">
            <table class="sleek-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 34%;">Document</th>
                        <th style="width: 15%;">File Type</th>
                        <th style="width: 23%;">Original File Name</th>
                        <th style="width: 14%;">Date Uploaded</th>
                        <th style="width: 16%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        @php
                            $extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                            $typeLabel = match (true) {
                                $extension === 'pdf' => 'PDF',
                                in_array($extension, ['xls', 'xlsx']) => 'Excel',
                                in_array($extension, ['doc', 'docx']) => 'Word',
                                default => 'File',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="file-title">{{ $file->title }}</div>
                                <div class="file-meta">{{ $teamLabel ?? 'Team' }} document</div>
                            </td>
                            <td><span class="type-badge">{{ $typeLabel }}</span></td>
                            <td>{{ $file->original_name }}</td>
                            <td>{{ $file->created_at->format('M d, Y') }}</td>
                            <td style="text-align: right;">
                                <a href="{{ $file->file_url }}" target="_blank" class="btn-download">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No documents have been uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
