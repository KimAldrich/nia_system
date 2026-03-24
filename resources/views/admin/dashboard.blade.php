@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}">Dashboard Home</a>
    <a href="#">Manage Teams</a>
    <a href="#">System Settings</a>
@endsection

@section('content')
    <h2 class="page-title">Admin Dashboard</h2>

    @if(session('success'))
        <div style="background: #d1fae5; color: #059669; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
        <div class="card">
            <h3 style="margin-top:0;">Create New User</h3>
            <form action="{{ route('admin.users.create') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-size: 14px;">Full Name</label>
                    <input type="text" name="name" required
                        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-size: 14px;">Email Address</label>
                    <input type="email" name="email" required
                        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-size: 14px;">Role</label>
                    <select name="role" required
                        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="fs_team">FS Team</option>
                    </select>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:5px; font-size: 14px;">Team ID (Optional)</label>
                    <input type="number" name="team_id"
                        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Save User</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Registered Users</h3>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td><span
                                style="background:#e2e8f0; padding:4px 8px; border-radius:4px; font-size:12px;">{{ strtoupper($user->role) }}</span>
                        </td>
                        <td>
                            @if($user->agreed_to_terms)
                                <span style="color:#059669;">Agreed</span>
                            @else
                                <span style="color:#dc2626;">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection