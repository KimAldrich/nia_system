@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<style>
    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
    }
    .btn-download:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
    }
</style>

<div class="card">
    <h2 class="page-title">{{ $pageTitle }}</h2>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Read-only access. Click the button below to view or download a file.</p>

    <table>
        <thead>
            <tr>
                <th>Document Title</th>
                <th>Uploaded By</th>
                <th>Date Uploaded</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($files as $file)
                <tr>
                    <td>
                        <span style="color: #0b5e2c; font-weight: 600;">
                            {{ $file->title }}
                        </span>
                    </td>
                    <td style="color: #64748b; font-size: 13px;">{{ strtoupper(str_replace('_', ' ', $file->team)) }}</td>
                    <td style="color: #64748b; font-size: 13px;">{{ $file->created_at->format('M d, Y') }}</td>
                    <td style="text-align: right;">
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-download" download>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#a1a1aa; padding: 30px;">No downloadables available for this team.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection