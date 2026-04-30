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

        .res-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .res-meta {
            font-size: 11px;
            font-weight: 500;
            color: #94a3b8;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }

        .status-completed { background: #d1fae5; color: #059669; }
        .status-progress { background: #dbeafe; color: #2563eb; }
        .status-pending { background: #fef3c7; color: #d97706; }

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
    <p class="header-desc">Browse official {{ $teamLabel ?? 'team' }} files. Files are view-only on the guest side.</p>

    <div class="ui-card">
        <div class="table-responsive">
            <table class="sleek-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 28%;">Resolution</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 28%;">File Name</th>
                        <th style="width: 12%;">Date Uploaded</th>
                        <th style="width: 12%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resolutions as $res)
                        @php
                            $statusLabel = \App\Models\IaResolution::displayStatusLabel($res->status, $res->team);
                            $files = $res->files;
                            if ($files->isEmpty() && $res->file_path) {
                                $files = collect([
                                    (object) [
                                        'file_path' => $res->file_path,
                                        'original_name' => $res->original_name,
                                        'created_at' => $res->created_at,
                                    ],
                                ]);
                            }
                        @endphp
                        @foreach($files as $file)
                            <tr>
                                <td>
                                    <div class="res-title">{{ $res->title }}</div>
                                    <div class="res-meta">{{ $teamLabel ?? 'Team' }} file</div>
                                </td>
                                <td>
                                    @if (\App\Models\IaResolution::isCompletedStatus($res->status))
                                        <span class="status-badge status-completed">{{ $statusLabel }}</span>
                                    @elseif($res->status == \App\Models\IaResolution::STATUS_ONGOING)
                                        <span class="status-badge status-progress">On-Going</span>
                                    @else
                                        <span class="status-badge status-pending">{{ $statusLabel }}</span>
                                    @endif
                                </td>
                                <td>{{ $file->original_name }}</td>
                                <td>{{ optional($file->created_at)->format('M d, Y') }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-download" download>
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No files available for this team.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
