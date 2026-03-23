@extends('admin.layout')
@section('title', 'Tambah Brand Baru')
@section('content')
<div class="page-title">Tambah Brand Baru</div>

<div class="card">
<form method="POST" action="{{ route('admin.brands.store') }}">
@csrf
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="form-group">
        <label>Nama Brand</label>
        <input type="text" name="name" placeholder="contoh: Winix" required />
        <small style="color:#999;font-size:11px">Slug akan dibuat otomatis</small>
    </div>
    <div class="form-group">
        <label>Warna Utama</label>
        <div style="display:flex;gap:8px;align-items:center">
            <input type="color" name="primary_color" value="#4f46e5" style="width:50px;height:38px;padding:2px;cursor:pointer" />
            <input type="text" id="color_hex" value="#4f46e5" style="flex:1"
                oninput="document.querySelector('[name=primary_color]').value=this.value" />
        </div>
    </div>
    <div class="form-group">
        <label>Logo URL (opsional)</label>
        <input type="text" name="logo_url" placeholder="https://..." />
    </div>
</div>

<div style="background:#eff6ff;border-radius:8px;padding:12px;font-size:13px;color:#1d4ed8;margin-bottom:16px">
    Point rules default akan dibuat otomatis (Rp10.000 = 1 poin untuk semua source). Bisa diubah setelah brand dibuat.
</div>

<button type="submit" class="btn btn-primary">Buat Brand</button>
<a href="{{ route('admin.brands.index') }}" class="btn" style="background:#f3f4f6;color:#333;margin-left:8px">Batal</a>
</form>
</div>
@endsection

<script>
document.querySelector('[name=primary_color]').addEventListener('input', function() {
    document.getElementById('color_hex').value = this.value;
});
</script>
