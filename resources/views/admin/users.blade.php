@extends('layouts.app')
@section('title', 'User Management')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #18181b;
            --primary-hover: #3f3f46;
            --bg-main: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
        }

        /* FIXED WIDTH & CENTERING */
        .content-wrapper {
            background-color: var(--bg-main);
            font-family: 'Inter', sans-serif;
            padding: 40px 20px;
            min-height: 100vh;
            max-width: 1300px; /* Limits expansion to the right */
            margin: 0 auto;    /* Centers the whole dashboard */
        }

        .header-section {
            margin-bottom: 32px;
        }

        .header-title {
            font-family: 'Poppins', sans-serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .header-desc {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* ADJUSTED GRID FOR SCREEN VISIBILITY */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr; /* Use fractions instead of fixed pixels */
            gap: 24px;
            align-items: start;
        }

        /* Stack earlier to prevent horizontal overflow */
        @media (max-width: 1200px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        .ui-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden; /* Keeps content inside the card */
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
        }

        /* Form Styling */
        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            background: #fff;
            transition: all 0.2s;
            outline: none;
            color: var(--text-main);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.1);
        }

        .btn-dark {
            background: var(--primary);
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
        }

        .btn-dark:hover { background: var(--primary-hover); transform: translateY(-1px); }

        /* Table Styling */
        .table-container { 
            overflow-x: auto; 
            width: 100%;
        }

        .sleek-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 600px; /* Ensures table doesn't get too squished */
        }

        .sleek-table th {
            text-align: left;
            padding: 12px 16px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }

        .sleek-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: var(--text-main);
        }

        .sleek-table tr:hover td { background-color: #fafafa; }

        /* Badges & Pills */
        .role-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
        }

        .role-badge.admin { background: #e0e7ff; color: #4338ca; }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            background: #ecfdf5;
            color: #065f46;
        }

        .status-pill.inactive { background: #fff1f2; color: #9f1239; }

        .status-select {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 13px;
            background: #fff;
            cursor: pointer;
        }

        .btn-danger {
            background: #fff;
            color: var(--danger);
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger:hover:not(:disabled) { background: #fef2f2; border-color: #fecaca; }

        .btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }

        .muted-note { color: var(--text-muted); font-size: 11px; font-weight: 500; margin-top: 4px; }

        /* Ajax & Sessions */
        .alert-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success { background: #18181b; color: #fff; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }

        .ajax-feedback {
            display: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        .ajax-feedback.success { display: block; background: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0; }
        .ajax-feedback.error { display: block; background: #fff1f2; color: #9f1239; border: 1px solid #fecaca; }

        .is-loading { opacity: 0.5; pointer-events: none; }
    </style>

    <div class="content-wrapper">
        <div class="header-section">
            <h1 class="header-title">User Management</h1>
            <p class="header-desc">Manage agency staff, team roles, and system access permissions.</p>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        <div id="account-feedback" class="ajax-feedback"></div>

        <div class="dashboard-grid">
            <div class="ui-card">
                <div class="section-header">
                    <h3 class="section-title">Team Directory</h3>
                    <span class="role-badge">{{ count($users) }} Users Total</span>
                </div>
                
                <div class="table-container">
                    <table class="sleek-table">
                        <thead>
                            <tr>
                                <th>User Details</th>
                                <th>Role / Team</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                @php $isCurrentUser = auth()->id() === $user->id; @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--primary);">{{ $user->name }}</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">{{ $user->email }}</div>
                                    </td>
                                    <td>
                                        <span class="role-badge {{ $user->role == 'admin' ? 'admin' : '' }}">
                                            @php
                                                $roleNames = [
                                                    'admin' => 'Administrator',
                                                    'fs_team' => 'Feasibility Study',
                                                    'rpwsis_team' => 'RP-WSIS',
                                                    'cm_team' => 'Contract Management',
                                                    'row_team' => 'Right Of Way',
                                                    'pcr_team' => 'Completion Report',
                                                    'pao_team' => 'Programming'
                                                ];
                                                echo $roleNames[$user->role] ?? $user->role;
                                            @endphp
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-pill js-status-pill {{ $user->is_active ? '' : 'inactive' }}">
                                            {{ $user->is_active ? 'Active' : 'Deactivated' }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 12px;">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                            <form action="{{ route('admin.users.status', $user) }}" method="POST" class="js-status-form" style="margin:0;">
                                                @csrf
                                                @method('PATCH')
                                                <select name="is_active" class="status-select js-status-select" 
                                                    data-current-value="{{ $user->is_active ? '1' : '0' }}" 
                                                    {{ $isCurrentUser ? 'disabled' : '' }}>
                                                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Activate</option>
                                                    <option value="0" {{ ! $user->is_active ? 'selected' : '' }}>Deactivate</option>
                                                </select>
                                            </form>

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="margin:0;"
                                                onsubmit="return confirm('Permanently delete this user account?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-danger" {{ $isCurrentUser ? 'disabled' : '' }}>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                        @if($isCurrentUser)
                                            <div class="muted-note" style="text-align: right;">(You)</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ui-card">
                <h3 class="section-title" style="margin-bottom: 24px;">Register User</h3>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" required placeholder="name@agency.gov">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Initial Password</label>
                        <input type="password" name="password" class="form-input" required placeholder="Min. 8 characters">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign Team</label>
                        <select name="role" class="form-input" required>
                            <option value="" disabled selected>Select Team...</option>
                            <option value="admin">Admin (Full Access)</option>
                            <option value="fs_team">Feasibility Study Team</option>
                            <option value="rpwsis_team">RP-WSIS Team</option>
                            <option value="cm_team">Contract Management Team</option>
                            <option value="row_team">Right Of Way Team</option>
                            <option value="pcr_team">Program Completion Report Team</option>
                            <option value="pao_team">Programming Team</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-dark">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const feedback = document.getElementById('account-feedback');

            function showFeedback(message, type) {
                if (!feedback) return;
                feedback.textContent = message;
                feedback.className = `ajax-feedback ${type}`;
                window.clearTimeout(showFeedback.timeoutId);
                showFeedback.timeoutId = window.setTimeout(() => {
                    feedback.className = 'ajax-feedback';
                    feedback.textContent = '';
                }, 3000);
            }

            document.querySelectorAll('.js-status-select').forEach((select) => {
                select.addEventListener('change', async function() {
                    const form = this.closest('.js-status-form');
                    const row = this.closest('tr');
                    const statusPill = row ? row.querySelector('.js-status-pill') : null;
                    const previousValue = this.dataset.currentValue || '1';
                    const wasDisabled = this.disabled;
                    const formData = new FormData(form);

                    this.disabled = true;
                    this.classList.add('is-loading');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const contentType = response.headers.get('content-type') || '';
                        const data = contentType.includes('application/json') ? await response.json() : {};

                        if (!response.ok) throw new Error(data.message || 'Unable to update account status.');

                        const isActive = data.is_active === true || data.is_active === 1 || data.is_active === '1';
                        this.value = isActive ? '1' : '0';
                        this.dataset.currentValue = this.value;

                        if (statusPill) {
                            statusPill.textContent = isActive ? 'Active' : 'Deactivated';
                            statusPill.classList.toggle('inactive', !isActive);
                        }

                        showFeedback(data.message || 'Account status updated successfully.', 'success');
                    } catch (error) {
                        this.value = previousValue;
                        showFeedback(error.message || 'Unable to update account status.', 'error');
                    } finally {
                        this.disabled = wasDisabled;
                        this.classList.remove('is-loading');
                    }
                });
            });
        });
    </script>
@endsection