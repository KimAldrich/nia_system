@php
    $resolutions = $resolutions ?? collect();
    $containerId = $containerId ?? 'activeProjectsContainer';
    $editable = $editable ?? false;
    $updateRouteName = $updateRouteName ?? null;
    $paginationStyle = $paginationStyle ?? 'split';
    $isPaginated = $resolutions instanceof \Illuminate\Contracts\Pagination\Paginator;
    $paginationWindow = $isPaginated ? \Illuminate\Pagination\UrlWindow::make($resolutions) : null;
@endphp

<style>
    .active-projects-panel {
        overflow-x: auto;
    }

    .active-projects-table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .active-projects-table thead th {
        padding: 11px 12px;
        font-size: 10px;
        font-weight: 700;
        color: #52525b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #f8fafc;
        border-bottom: 1px solid #e4e4e7;
        text-align: left;
    }

    .active-projects-table tbody td {
        padding: 12px;
        font-size: 11px;
        color: #334155;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .active-projects-table tbody tr:last-child td {
        border-bottom: none;
    }

    .active-projects-table tbody tr:hover td {
        background: #fafafa;
    }

    .active-project-title {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
        line-height: 1.35;
    }

    .active-project-date {
        font-size: 10px;
        color: #94a3b8;
    }

    .active-project-action {
        text-align: right;
        white-space: nowrap;
    }

    .active-projects-table th:first-child,
    .active-projects-table td:first-child {
        width: 50%;
    }

    .active-projects-table th:nth-child(2),
    .active-projects-table td:nth-child(2) {
        width: 22%;
    }

    .active-projects-table .status-select {
        width: auto;
        min-width: 118px;
        max-width: 118px;
        padding: 5px 8px;
        font-size: 10px;
    }

    .active-projects-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 4px 2px;
        flex-wrap: wrap;
    }

    .active-projects-pagination.right {
        justify-content: flex-end;
    }

    .active-projects-pagination-summary {
        font-size: 11px;
        color: #64748b;
    }

    .active-projects-pagination-links {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .active-projects-page-link,
    .active-projects-page-current,
    .active-projects-page-dots {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
    }

    .active-projects-page-link {
        color: #334155;
        background: #ffffff;
        border: 1px solid #dbe4ee;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .active-projects-page-link:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .active-projects-page-link.disabled {
        pointer-events: none;
        opacity: 0.45;
    }

    .active-projects-page-current {
        color: #ffffff;
        background: #4f46e5;
        border: 1px solid #4f46e5;
    }

    .active-projects-page-dots {
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .active-projects-table {
            min-width: 100%;
        }

        .active-projects-table th:first-child,
        .active-projects-table td:first-child,
        .active-projects-table th:nth-child(2),
        .active-projects-table td:nth-child(2) {
            width: auto;
        }

        .active-projects-pagination {
            align-items: stretch;
        }
    }
</style>

<div id="{{ $containerId }}">
    <div class="table-responsive active-projects-panel">
        <table class="active-projects-table">
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>Status</th>
                    @if ($editable)
                        <th style="text-align: right;">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($resolutions as $res)
                    @php
                        $statusTeam = $statusTeam ?? null;
                        $resolutionTeam = $statusTeam ?: $res->team;
                        $completedStatusValue = \App\Models\IaResolution::completedStatusValueForTeam($resolutionTeam);
                        $completedStatusLabel = \App\Models\IaResolution::completedStatusLabelForTeam($resolutionTeam);
                        $pendingStatusLabel = \App\Models\IaResolution::pendingStatusLabelForTeam($resolutionTeam);
                        $statusLabel = \App\Models\IaResolution::displayStatusLabel($res->status, $resolutionTeam);
                    @endphp
                    <tr>
                        <td>
                            <span class="active-project-title">{{ $res->title }}</span>
                            <span class="active-project-date">{{ $res->created_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            @if (\App\Models\IaResolution::isCompletedStatus($res->status))
                                <span class="status-badge badge-dark">{{ $statusLabel }}</span>
                            @elseif($res->status == \App\Models\IaResolution::STATUS_ONGOING)
                                <span class="status-badge badge-light">On-Going</span>
                            @else
                                <span class="status-badge badge-outline">{{ $pendingStatusLabel }}</span>
                            @endif
                        </td>
                        @if ($editable && $updateRouteName)
                            <td class="active-project-action">
                                <form action="{{ route($updateRouteName, $res->id) }}" method="POST"
                                    data-async-target="#{{ $containerId }}"
                                    data-async-reload="true">
                                    @csrf
                                    <select name="status" class="status-select" data-auto-submit>
                                        <option value="{{ \App\Models\IaResolution::STATUS_PENDING }}" {{ $res->status == \App\Models\IaResolution::STATUS_PENDING ? 'selected' : '' }}>
                                            {{ $pendingStatusLabel }}
                                        </option>
                                        <option value="{{ \App\Models\IaResolution::STATUS_ONGOING }}" {{ $res->status == \App\Models\IaResolution::STATUS_ONGOING ? 'selected' : '' }}>
                                            On-Going
                                        </option>
                                        <option value="{{ $completedStatusValue }}" {{ \App\Models\IaResolution::normalizeStatusForTeam($res->status, $resolutionTeam) == $completedStatusValue ? 'selected' : '' }}>
                                            {{ $completedStatusLabel }}
                                        </option>
                                    </select>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $editable ? '3' : '2' }}" style="text-align:center; color:#a1a1aa; padding: 30px 0;">
                            No projects uploaded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($isPaginated && $resolutions->hasPages())
        <div class="active-projects-pagination {{ $paginationStyle === 'right' ? 'right' : '' }}">
            @if ($paginationStyle !== 'right')
                <div class="active-projects-pagination-summary">
                    Showing {{ $resolutions->firstItem() }}-{{ $resolutions->lastItem() }} of {{ $resolutions->total() }} projects
                </div>
            @endif

            <div class="active-projects-pagination-links">
                <a href="{{ $resolutions->previousPageUrl() ?: '#' }}"
                    class="active-projects-page-link {{ $resolutions->onFirstPage() ? 'disabled' : '' }}"
                    data-async-pagination
                    data-async-target="#{{ $containerId }}">
                    Prev
                </a>

                @foreach (($paginationWindow['first'] ?? []) as $page => $url)
                    @if ($page === $resolutions->currentPage())
                        <span class="active-projects-page-current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="active-projects-page-link"
                            data-async-pagination
                            data-async-target="#{{ $containerId }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if (!empty($paginationWindow['slider']))
                    @if (!empty($paginationWindow['first']))
                        <span class="active-projects-page-dots">...</span>
                    @endif

                    @foreach ($paginationWindow['slider'] as $page => $url)
                        @if ($page === $resolutions->currentPage())
                            <span class="active-projects-page-current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="active-projects-page-link"
                                data-async-pagination
                                data-async-target="#{{ $containerId }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if (!empty($paginationWindow['last']))
                        <span class="active-projects-page-dots">...</span>
                    @endif
                @endif

                @foreach (($paginationWindow['last'] ?? []) as $page => $url)
                    @if ($page === $resolutions->currentPage())
                        <span class="active-projects-page-current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="active-projects-page-link"
                            data-async-pagination
                            data-async-target="#{{ $containerId }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                <a href="{{ $resolutions->nextPageUrl() ?: '#' }}"
                    class="active-projects-page-link {{ $resolutions->hasMorePages() ? '' : 'disabled' }}"
                    data-async-pagination
                    data-async-target="#{{ $containerId }}">
                    Next
                </a>
            </div>
        </div>
    @endif
</div>
