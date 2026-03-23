@extends('admin.layout')
@section('title', 'Tambah Reward')
@section('content')
<div class="page-title">Tambah Reward Baru</div>
<div class="card">
<form method="POST" action="{{ route('admin.rewards.store') }}">
@csrf
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="form-group">
        <label>Brand</label>
        <select name="brand_id">
            @foreach($brands as $brand)
            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Nama Reward</label>
        <input type="text" name="name" />
    </div>
    <div class="form-group">
        <label>Poin yang Dibutuhkan</label>
        <input type="number" name="points_required" min="1" />
    </div>
    <div class="form-group">
        <label>Stok</label>
        <input type="number" name="stock" min="0" value="0" />
    </div>
    <div class="form-group">
        <label>Berlaku Hingga</label>
        <input type="date" name="valid_until" />
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="unlimited_stock" value="1"> Stok Tidak Terbatas</label>
    </div>
</div>
<div class="form-group">
    <label>Deskripsi</label>
    <textarea name="description" rows="3" style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px"></textarea>
</div>
<button type="submit" class="btn btn-primary">Simpan Reward</button>
</form>
</div>
@endsection
