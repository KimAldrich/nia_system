@extends('layouts.app')
@section('title', 'Admin Activity Log')

@section('content')
    <style>
        .audit-shell { display: grid; gap: 20px; }
        .audit-header h1 { margin: 0 0 6px; font-size: 26px; color: #0f172a; }
        .audit-header p { margin: 0; color: #64748b; font-size: 13px; }
        .audit-filter-bar { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: end; }
        .audit-field { display: grid; gap: 6px; }
        .audit-field--search { grid-column: span 1; }
        .audit-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b; }
        .audit-input, .audit-select { height: 44px; min-height: 44px; box-sizing: border-box; border-radius: 12px; border: 1px solid #dbe3ee; background: #fff; padding: 10px 14px; font-size: 13px; color: #0f172a; }
        .audit-input:focus, .audit-select:focus { outline: none; border-color: #110d9e; box-shadow: 0 0 0 4px rgba(17, 13, 158, 0.08); }
        .audit-actions { display: flex; gap: 10px; flex-wrap: wrap; grid-column: 1 / -1; }
        .audit-btn { min-height: 44px; padding: 0 16px; border-radius: 12px; border: 1px solid transparent; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .audit-btn-primary { background: #110d9e; color: #fff; }
        .audit-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
        .audit-btn-ghost { background: #16a34a; color: #ffffff; border: none; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; gap: 8px; }
        .audit-toolbar-meta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .audit-summary { font-size: 12px; color: #64748b; }
        .audit-table-wrap { overflow-x: auto; }
        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b; background: #f8fafc; }
        .audit-table td, .audit-table th { padding: 14px 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .audit-entry { display: grid; gap: 4px; }
        .audit-desc { font-weight: 600; color: #0f172a; }
        .audit-meta { font-size: 12px; color: #64748b; }
        .audit-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 10px; font-size: 11px; font-weight: 700; background: #e0e7ff; color: #3730a3; }
        .audit-link-btn { background: transparent; border: none; color: #1d4ed8; font-size: 12px; font-weight: 700; cursor: pointer; padding: 0; text-align: left; }
        .audit-link-btn:hover { text-decoration: underline; }
        .audit-empty { padding: 32px 20px; text-align: center; color: #64748b; font-size: 13px; }
        .audit-pagination { margin-top: 16px; }
        .audit-pagination nav > div:first-child { display: none; }
        .audit-pagination svg { width: 16px; height: 16px; }
        .audit-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity 0.2s ease, visibility 0.2s ease; z-index: 5200; }
        .audit-modal.is-visible { opacity: 1; visibility: visible; pointer-events: auto; }
        .audit-modal__dialog { width: min(100%, 760px); max-height: 88vh; overflow: auto; background: #ffffff; border-radius: 20px; box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22); padding: 24px; }
        .audit-modal__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .audit-modal__title { margin: 0 0 4px; font-size: 20px; color: #0f172a; }
        .audit-modal__subtitle { margin: 0; font-size: 13px; color: #64748b; }
        .audit-modal__close { border: none; background: #f8fafc; color: #475569; width: 36px; height: 36px; border-radius: 999px; cursor: pointer; font-size: 18px; }
        .audit-modal__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .audit-modal__item { padding: 14px; border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .audit-modal__label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; font-weight: 700; margin-bottom: 6px; }
        .audit-modal__value { font-size: 13px; color: #0f172a; line-height: 1.55; word-break: break-word; }
        .audit-modal__item--full { grid-column: 1 / -1; }
        .audit-modal__pre { margin: 0; white-space: pre-wrap; word-break: break-word; font-family: Consolas, Monaco, monospace; font-size: 12px; color: #0f172a; }
        @media (max-width: 900px) {
            .audit-filter-bar { grid-template-columns: 1fr; }
            .audit-modal__grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="audit-shell">
        <div class="audit-header">
            <h1>Activity Log</h1>
            <p>Review uploads, edits, deletions, status changes, and calendar-related actions across the system.</p>
        </div>

        <div class="ui-card">
            <form action="{{ route('admin.audit') }}" method="GET" class="audit-filter-bar">
                <div class="audit-field audit-field--search">
                    <label class="audit-label" for="search">Search</label>
                    <input id="search" type="text" name="search" value="{{ $search }}" class="audit-input" placeholder="Search by user, action, file, project, or status">
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="action">Action</label>
                    <select id="action" name="action" class="audit-select">
                        <option value="">All actions</option>
                        @foreach($actions as $actionOption)
                            <option value="{{ $actionOption }}" {{ $action === $actionOption ? 'selected' : '' }}>{{ $actionOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="team">Team</label>
                    <select id="team" name="team" class="audit-select">
                        <option value="">All teams</option>
                        @foreach($teams as $teamOption)
                            <option value="{{ $teamOption }}" {{ $team === $teamOption ? 'selected' : '' }}>{{ $teamOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="user">User</label>
                    <select id="user" name="user" class="audit-select">
                        <option value="">All users</option>
                        @foreach($users as $userOption)
                            <option value="{{ $userOption }}" {{ $user === $userOption ? 'selected' : '' }}>{{ $userOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="status">Status</label>
                    <select id="status" name="status" class="audit-select">
                        <option value="">All statuses</option>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="date_from">Date From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" class="audit-input">
                </div>
                <div class="audit-field">
                    <label class="audit-label" for="date_to">Date To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" class="audit-input">
                </div>
                <div class="audit-actions">
                    <button type="submit" class="audit-btn audit-btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.audit.export', request()->query()) }}" class="audit-btn audit-btn-ghost">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </a>
                    <a href="{{ route('admin.audit') }}" class="audit-btn audit-btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="ui-card">
            <div class="audit-toolbar-meta">
                <div class="audit-summary">{{ number_format($logs->total()) }} log entr{{ $logs->total() === 1 ? 'y' : 'ies' }} found. Sorted newest first.</div>
            </div>
            <div class="audit-table-wrap">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Activity</th>
                            <th style="width: 14%;">User</th>
                            <th style="width: 15%;">Action</th>
                            <th style="width: 15%;">Subject</th>
                            <th style="width: 14%;">When</th>
                            <th style="width: 14%;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="audit-entry">
                                        <div class="audit-desc">{{ $log->description }}</div>
                                        <div class="audit-meta">
                                            {{ $log->method }}
                                            @if(!empty($log->metadata['team']))
                                                · {{ $log->metadata['team'] }}
                                            @endif
                                            @if(!empty($log->metadata['status']))
                                                · Status: {{ $log->metadata['status'] }}
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="audit-entry">
                                        <div class="audit-desc">{{ $log->user_name ?? 'Unknown user' }}</div>
                                        <div class="audit-meta">{{ $log->user_role ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td><span class="audit-badge">{{ $log->action }}</span></td>
                                <td>
                                    <div class="audit-entry">
                                        <div class="audit-desc">{{ $log->subject_label ?? 'N/A' }}</div>
                                        <div class="audit-meta">{{ $log->subject_type ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="audit-entry">
                                        <div class="audit-desc">{{ optional($log->created_at)->format('M d, Y') }}</div>
                                        <div class="audit-meta">{{ optional($log->created_at)->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="audit-link-btn"
                                        data-audit-log='{{ json_encode([
                                            "description" => $log->description,
                                            "user_name" => $log->user_name ?? "Unknown user",
                                            "user_role" => $log->user_role ?? "N/A",
                                            "action" => $log->action,
                                            "subject_type" => $log->subject_type ?? "N/A",
                                            "subject_label" => $log->subject_label ?? "N/A",
                                            "created_at" => optional($log->created_at)->format("M d, Y h:i:s A"),
                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                                    >
                                        View details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="audit-empty">No activity log entries matched the current filters yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="audit-pagination">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="auditDetailsModal" class="audit-modal" aria-hidden="true">
        <div class="audit-modal__dialog">
            <div class="audit-modal__header">
                <div>
                    <h2 class="audit-modal__title">Activity Details</h2>
                    <p class="audit-modal__subtitle">Full context for the selected log entry.</p>
                </div>
                <button type="button" class="audit-modal__close" aria-label="Close details modal" onclick="closeAuditDetailsModal()">×</button>
            </div>
            <div class="audit-modal__grid">
                <div class="audit-modal__item audit-modal__item--full">
                    <span class="audit-modal__label">Description</span>
                    <div class="audit-modal__value" id="auditDetailDescription">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">User</span>
                    <div class="audit-modal__value" id="auditDetailUser">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">Role</span>
                    <div class="audit-modal__value" id="auditDetailRole">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">Action</span>
                    <div class="audit-modal__value" id="auditDetailAction">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">Timestamp</span>
                    <div class="audit-modal__value" id="auditDetailCreatedAt">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">Subject Type</span>
                    <div class="audit-modal__value" id="auditDetailSubjectType">N/A</div>
                </div>
                <div class="audit-modal__item">
                    <span class="audit-modal__label">Subject</span>
                    <div class="audit-modal__value" id="auditDetailSubjectLabel">N/A</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeAuditDetailsModal() {
            const modal = document.getElementById('auditDetailsModal');
            if (!modal) {
                return;
            }

            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
        }

        function openAuditDetailsModal(payload) {
            const modal = document.getElementById('auditDetailsModal');
            if (!modal) {
                return;
            }

            const assign = (id, value) => {
                const node = document.getElementById(id);
                if (node) {
                    node.textContent = value && String(value).trim() !== '' ? value : 'N/A';
                }
            };

            assign('auditDetailDescription', payload.description);
            assign('auditDetailUser', payload.user_name);
            assign('auditDetailRole', payload.user_role);
            assign('auditDetailAction', payload.action);
            assign('auditDetailCreatedAt', payload.created_at);
            assign('auditDetailSubjectType', payload.subject_type);
            assign('auditDetailSubjectLabel', payload.subject_label);

            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-audit-log]');
            if (trigger) {
                try {
                    const payload = JSON.parse(trigger.getAttribute('data-audit-log') || '{}');
                    openAuditDetailsModal(payload);
                } catch (error) {
                    console.error('Unable to parse audit log details.', error);
                }
                return;
            }

            if (event.target?.id === 'auditDetailsModal') {
                closeAuditDetailsModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAuditDetailsModal();
            }
        });
    </script>
@endsection
