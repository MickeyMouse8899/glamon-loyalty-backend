@extends('admin.layout')
@section('title', 'Edit Brand: ' . $brand->name)
@section('content')
<div class="page-title">Edit Brand: {{ $brand->name }}</div>
<form method="POST" action="{{ route('admin.brands.update', $brand) }}">
@csrf @method('PUT')

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Info Brand</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
            <label>Nama Brand</label>
            <input type="text" name="name" value="{{ $brand->name }}" />
        </div>
        <div class="form-group">
            <label>Warna Utama</label>
            <input type="text" name="primary_color" value="{{ $brand->primary_color }}" />
        </div>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ $brand->is_active ? 'checked' : '' }}> Brand Aktif</label>
    </div>
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Point Rules</h3>
    @foreach(['instore' => 'Di Toko (Kasir)', 'inapp' => 'Via Aplikasi', 'webstore' => 'Via Webstore'] as $source => $label)
    <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:12px">
        <div style="font-weight:500;margin-bottom:12px;font-size:13px">{{ $label }}</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
            <div class="form-group" style="margin:0">
                <label>Rp per Poin</label>
                <input type="number" name="rules[{{ $source }}][rp_per_point]" value="{{ $rules[$source]->rp_per_point ?? 10000 }}" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Multiplier</label>
                <input type="number" step="0.01" name="rules[{{ $source }}][multiplier]" value="{{ $rules[$source]->multiplier ?? 1.00 }}" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Min. Transaksi</label>
                <input type="number" name="rules[{{ $source }}][min_transaction]" value="{{ $rules[$source]->min_transaction ?? 0 }}" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Aktif</label>
                <input type="checkbox" name="rules[{{ $source }}][is_active]" value="1" {{ ($rules[$source]->is_active ?? true) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">WooCommerce Integration</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
            <label>Store URL</label>
            <input type="text" name="store_url" value="{{ $integration->store_url ?? '' }}" placeholder="https://toko.com" />
        </div>
        <div class="form-group">
            <label>Webhook Secret</label>
            <input type="text" name="webhook_secret" placeholder="Kosongkan jika tidak berubah" />
        </div>
        <div class="form-group">
            <label>Consumer Key</label>
            <input type="text" name="consumer_key" placeholder="ck_xxxx" />
        </div>
        <div class="form-group">
            <label>Consumer Secret</label>
            <input type="text" name="consumer_secret" placeholder="cs_xxxx" />
        </div>
    </div>
    <p style="font-size:12px;color:#999">Webhook URL untuk WooCommerce: <code>https://id.glamon.id/api/v1/webhooks/woocommerce/{{ $brand->slug }}</code></p>
</div>

<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>
@endsection
