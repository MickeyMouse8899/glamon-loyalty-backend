<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Glamon Loyalty</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:sans-serif;background:#f5f5f5;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .card{background:#fff;border-radius:12px;padding:36px;width:380px;box-shadow:0 4px 20px rgba(0,0,0,0.08)}
        h1{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
        p{font-size:13px;color:#888;margin-bottom:24px}
        label{display:block;font-size:13px;color:#555;margin-bottom:5px;font-weight:500}
        input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;outline:none;margin-bottom:14px}
        input:focus{border-color:#4f46e5}
        button{width:100%;padding:12px;background:#4f46e5;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .error{color:#dc2626;font-size:12px;margin-bottom:12px;background:#fef2f2;padding:8px 12px;border-radius:6px}
        .roles{display:flex;gap:8px;margin-bottom:20px}
        .role-badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
    </style>
</head>
<body>
<div class="card">
    <h1>Glamon Loyalty</h1>
    <p>Masuk ke panel management</p>

    <div class="roles">
        <span class="role-badge" style="background:#eff6ff;color:#1d4ed8">Super Admin</span>
        <span class="role-badge" style="background:#f0fdf4;color:#16a34a">Admin</span>
        <span class="role-badge" style="background:#fef9c3;color:#854d0e">Kasir</span>
    </div>

    @if($errors->any())
    <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" autofocus placeholder="admin@glamon.id" />
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" />
        <button type="submit">Masuk</button>
    </form>
</div>
</body>
</html>
