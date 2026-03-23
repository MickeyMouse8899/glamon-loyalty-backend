@extends('admin.layout')
@section('title', 'Staff Management')
@section('content')
<div class="page-title">Staff Management</div>

@if($currentUser->isSuperAdmin())
<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Tambah Staff Baru</h3>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
            <div class="form-group" style="margin:0">
                <label>Nama</label>
                <input type="text" name="name" required />
            </div>
            <div class="form-group" style="margin:0">
                <label>Email</label>
                <input type="email" name="email" required />
            </div>
            <div class="form-group" style="margin:0">
                <label>Password</label>
                <input type="password" name="password" required />
            </div>
            <div class="form-group" style="margin:0">
                <label>Role</label>
                <select name="role">
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                    @if($currentUser->isSuperAdmin())
                    <option value="superadmin">Super Admin</option>
                    @endif
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
        @if($errors->any())
        <div class="alert-error" style="margin-top:10px">{{ $errors->first() }}</div>
        @endif
    </form>
</div>
@endif

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($staff as $user)
        <tr>
            <td>
                <strong>{{ $user->name }}</strong>
                @if($user->id === $currentUser->id)
                <span style="font-size:10px;background:#eff6ff;color:#1d4ed8;padding:1px 6px;border-radius:10px;margin-left:4px">Anda</span>
                @endif
            </td>
            <td style="font-size:13px;color:#666">{{ $user->email }}</td>
            <td>
                @if($currentUser->isSuperAdmin() && $user->id !== $currentUser->id)
                <form method="POST" action="{{ route('admin.users.role', $user) }}" style="display:inline">
                    @csrf
                    <select name="role" onchange="this.form.submit()" style="font-size:12px;padding:3px 6px;border:1px solid #ddd;border-radius:4px">
                        <option value="kasir" {{ $user->role === 'kasir' ? 'selected' : '' }}>Kasir</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="superadmin" {{ $user->role === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </form>
                @else
                <span class="badge" style="background:{{ $user->role === 'superadmin' ? '#eff6ff' : ($user->role === 'admin' ? '#f0fdf4' : '#fef9c3') }};color:{{ $user->role === 'superadmin' ? '#1d4ed8' : ($user->role === 'admin' ? '#16a34a' : '#854d0e') }}">
                    {{ ucfirst($user->role) }}
                </span>
                @endif
            </td>
            <td>
                <span class="badge" style="background:{{ $user->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $user->is_active ? '#166534' : '#dc2626' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </td>
            <td>
                @if($user->id !== $currentUser->id)
                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
