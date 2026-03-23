<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Glamon Loyalty</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:sans-serif;background:#f5f5f5;color:#333;display:flex;min-height:100vh}
        .sidebar{width:220px;background:#1a1a2e;color:#fff;flex-shrink:0;display:flex;flex-direction:column}
        .sidebar .logo{padding:20px 16px;font-size:16px;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.1)}
        .sidebar .logo span{font-size:11px;font-weight:400;opacity:0.6;display:block;margin-top:2px}
        .sidebar nav{flex:1;padding:12px 0}
        .sidebar nav a{display:block;padding:10px 16px;color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;border-left:3px solid transparent}
        .sidebar nav a:hover,.sidebar nav a.active{color:#fff;background:rgba(255,255,255,0.08);border-left-color:#7c6fff}
        .sidebar nav .nav-section{padding:8px 16px 4px;font-size:10px;text-transform:uppercase;color:rgba(255,255,255,0.3);letter-spacing:1px}
        .sidebar .user-info{padding:12px 16px;border-top:1px solid rgba(255,255,255,0.1)}
        .sidebar .user-info .name{font-size:13px;color:#fff;font-weight:500}
        .sidebar .user-info .role{font-size:10px;text-transform:uppercase;letter-spacing:1px;margin-top:2px;margin-bottom:8px}
        .sidebar .user-info .role.superadmin{color:#7c6fff}
        .sidebar .user-info .role.admin{color:#4ade80}
        .sidebar .user-info .role.kasir{color:#fbbf24}
        .sidebar .user-info a{color:rgba(255,255,255,0.4);font-size:12px;text-decoration:none}
        .main{flex:1;display:flex;flex-direction:column;overflow:auto}
        .topbar{background:#fff;padding:14px 24px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#666}
        .content{padding:24px;flex:1}
        .page-title{font-size:20px;font-weight:600;margin-bottom:20px;color:#1a1a2e}
        .card{background:#fff;border-radius:10px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
        .stat-card{background:#fff;border-radius:10px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
        .stat-card .value{font-size:28px;font-weight:700;color:#1a1a2e}
        .stat-card .label{font-size:12px;color:#888;margin-top:4px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th{text-align:left;padding:10px 12px;background:#f9fafb;color:#666;font-weight:500;border-bottom:1px solid #e5e7eb}
        td{padding:10px 12px;border-bottom:1px solid #f3f4f6}
        tr:last-child td{border:none}
        .badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-instore{background:#dcfce7;color:#166534}
        .badge-inapp{background:#eff6ff;color:#1d4ed8}
        .badge-webstore{background:#faf5ff;color:#7e22ce}
        .btn{padding:8px 16px;border:none;border-radius:6px;font-size:13px;cursor:pointer;font-weight:500;text-decoration:none;display:inline-block}
        .btn-primary{background:#4f46e5;color:#fff}
        .btn-sm{padding:4px 10px;font-size:12px}
        .btn-danger{background:#dc2626;color:#fff}
        .btn-success{background:#16a34a;color:#fff}
        .form-group{margin-bottom:14px}
        label{display:block;font-size:13px;color:#555;margin-bottom:5px;font-weight:500}
        input[type=text],input[type=number],input[type=password],input[type=date],input[type=email],select,textarea{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;outline:none}
        input:focus,select:focus{border-color:#4f46e5}
        .alert-success{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:13px}
        .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:13px}
        .pagination{display:flex;gap:4px;margin-top:16px;justify-content:center}
        .pagination a,.pagination span{padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;text-decoration:none;color:#666}
        .pagination .active span{background:#4f46e5;color:#fff;border-color:#4f46e5}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">Glamon Loyalty <span>Management Panel</span></div>
    <nav>
        @php $role = session('admin_user_role'); @endphp

        @if(in_array($role, ['superadmin', 'admin']))
        <div class="nav-section">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

        <div class="nav-section">Manajemen</div>
        <a href="{{ route('admin.brands.index') }}" class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">Brands & Rules</a>
        <a href="{{ route('admin.rewards.index') }}" class="{{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}">Rewards</a>
        <a href="{{ route('admin.integrations.index') }}" class="{{ request()->routeIs('admin.integrations.*') ? 'active' : '' }}">Integrations</a>

        <div class="nav-section">Laporan</div>
        <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">Transaksi</a>
        <a href="{{ route('admin.members.index') }}" class="{{ request()->routeIs('admin.members.*') ? 'active' : '' }}">Members</a>
        @endif

        @if($role === 'superadmin')
        <div class="nav-section">System</div>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Staff Management</a>
        @endif

        <div class="nav-section">Tools</div>
        <a href="/kasir" target="_blank">Kasir Panel</a>
    </nav>
    <div class="user-info">
        <div class="name">{{ session('admin_user_name') }}</div>
        <div class="role {{ $role }}">{{ ucfirst($role ?? '') }}</div>
        <a href="{{ route('admin.logout') }}" onclick="return confirm('Yakin logout?')">Logout</a>
    </div>
</div>
<div class="main">
    <div class="topbar">@yield('title', 'Dashboard')</div>
    <div class="content">
        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>
</body>
</html>
