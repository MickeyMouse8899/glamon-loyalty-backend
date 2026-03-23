<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Glamon Loyalty</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:sans-serif;background:#f5f5f5;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .card{background:#fff;border-radius:12px;padding:36px;width:360px;box-shadow:0 4px 20px rgba(0,0,0,0.08)}
        h1{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
        p{font-size:13px;color:#888;margin-bottom:24px}
        label{display:block;font-size:13px;color:#555;margin-bottom:5px;font-weight:500}
        input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;outline:none;margin-bottom:16px}
        input:focus{border-color:#4f46e5}
        button{width:100%;padding:12px;background:#4f46e5;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
        .error{color:#dc2626;font-size:13px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="card">
    <h1>Admin Login</h1>
    <p>Glamon Loyalty System</p>
    @error('password')<div class="error">{{ $message }}</div>@enderror
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label>Password</label>
        <input type="password" name="password" autofocus />
        <button type="submit">Masuk</button>
    </form>
</div>
</body>
</html>
