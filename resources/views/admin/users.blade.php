@extends('layouts.app')
@section('title', 'User Management')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: #111;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header-desc {
            color: #a1a1aa;
            font-size: 13px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e4e4e7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #18181b;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #71717a;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e4e4e7;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            background: #fafafa;
            transition: 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #18181b;
            background: #ffffff;
        }

        .btn-dark {
            background: #18181b;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            display: block;
            width: 100%;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
            transition: 0.2s;
        }

        .btn-dark:hover {
            background: #3f3f46;
        }

        /* Sleek Table */
        .sleek-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sleek-table th {
            text-align: left;
            padding-bottom: 15px;
            color: #a1a1aa;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f4f4f5;
        }

        .sleek-table td {
            padding: 15px 0;
            border-bottom: 1px solid #f4f4f5;
            font-size: 13px;
            font-weight: 500;
            vertical-align: middle;
        }

        .sleek-table tr:last-child td {
            border-bottom: none;
            padding-bottom: 0;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            background: #f4f4f5;
            color: #18181b;
        }

        .role-badge.admin {
            background: #18181b;
            color: #ffffff;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #d4d4d8;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
            background: #fff;
            cursor: pointer;
        }

        .status-select:disabled {
            background: #f4f4f5;
            cursor: not-allowed;
        }

        .table-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: nowrap;
            min-width: 230px;
        }

        .inline-form {
            margin: 0;
        }

        .status-form {
            flex: 1;
        }

        .status-form .status-select {
            width: 100%;
        }

        .btn-danger {
            background: #b91c1c;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-danger:hover {
            background: #991b1b;
        }

        .btn-danger:disabled {
            background: #d4d4d8;
            cursor: not-allowed;
        }

        .muted-note {
            color: #71717a;
            font-size: 11px;
            font-weight: 600;
        }

        .ajax-feedback {
            display: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .ajax-feedback.success {
            display: block;
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .ajax-feedback.error {
            display: block;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-select.is-loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>

    <h1 class="header-title">User Management</h1>
    <p class="header-desc">Create and manage accounts and team assignments for agency staff.</p>

    @if(session('success'))
        <div
            style="background: #18181b; color: #ffffff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px; height:18px; color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; border: 1px solid #fecaca;">
            {{ session('error') }}
        </div>
    @endif

    <div id="account-feedback" class="ajax-feedback"></div>

    <div class="dashboard-grid">
        <div class="ui-card">
            <h3 class="section-title">Active Directory</h3>
            <table class="sleek-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role / Team</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Account Controls</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $isCurrentUser = auth()->id() === $user->id;
                        @endphp
                        <tr>
                            <td><strong style="color:#18181b;">{{ $user->name }}</strong></td>
                            <td style="color:#71717a;">{{ $user->email }}</td>
                            <td>
                                <span class="role-badge {{ $user->role == 'admin' ? 'admin' : '' }}">
                                    @php
                                        // Make the database roles readable for the table
                                        $roleNames = [
                                            'admin' => 'Admin',
                                            'fs_team' => 'Feasibility Study',
                                            'rpwsis_team' => 'RP-WSIS',
                                            'cm_team' => 'Contract Management',
                                            'row_team' => 'Right Of Way',
                                            'pcr_team' => 'Prog. Completion Report',
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
                            <td style="color:#a1a1aa; font-size: 12px;">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    <form action="{{ route('admin.users.status', $user) }}" method="POST" class="inline-form status-form js-status-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="is_active" class="status-select js-status-select"
                                            data-current-value="{{ $user->is_active ? '1' : '0' }}" {{ $isCurrentUser ? 'disabled' : '' }}>
                                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Activate</option>
                                            <option value="0" {{ ! $user->is_active ? 'selected' : '' }}>Deactivate</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-form"
                                        onsubmit="return confirm('Are you sure you want to delete this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" {{ $isCurrentUser ? 'disabled' : '' }}>Delete</button>
                                    </form>
                                </div>

                                @if($isCurrentUser)
                                    <div class="muted-note" style="margin-top: 6px;">Current account</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="ui-card">
            <h3 class="section-title">Register New User</h3>

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" required placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" required placeholder="john@agency.gov">
                </div>

                <div class="form-group">
                    <label class="form-label">Temporary Password</label>
                    <input type="password" name="password" class="form-input" required placeholder="Minimum 8 characters">
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label" style="color: #18181b;">Assign Team Role</label>
                    <select name="role" class="form-input" required style="cursor: pointer;">
                        <option value="" disabled selected>Select a role...</option>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const feedback = document.getElementById('account-feedback');

            function showFeedback(message, type) {
                if (!feedback) {
                    return;
                }

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

                        if (!response.ok) {
                            throw new Error(data.message || 'Unable to update account status.');
                        }

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