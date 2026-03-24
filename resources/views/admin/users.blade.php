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

    <div class="dashboard-grid">
        <div class="ui-card">
            <h3 class="section-title">Active Directory</h3>
            <table class="sleek-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role / Team</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
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
                            <td style="color:#a1a1aa; font-size: 12px;">{{ $user->created_at->format('M d, Y') }}</td>
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
@endsection