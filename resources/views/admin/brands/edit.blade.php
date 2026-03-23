@extends('admin.layout')
@section('title', 'Edit Brand: ' . $brand->name)
@section('content')
<div class="page-title">Edit Brand: {{ $brand->name }}</div>

<form method="POST" action="{{ route('admin.brands.update', $brand) }}">
@csrf @method('PUT')

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Info Brand</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        <div class="form-group">
            <label>Nama Brand</label>
            <input type="text" name="name" value="{{ $brand->name }}" required />
        </div>
        <div class="form-group">
            <label>Warna Utama</label>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="color" name="primary_color" value="{{ $brand->primary_color }}"
                    style="width:50px;height:38px;padding:2px;cursor:pointer"
                    oninput="document.getElementById('hex_display').value=this.value" />
                <input type="text" id="hex_display" value="{{ $brand->primary_color }}" style="flex:1"
                    oninput="document.querySelector('[name=primary_color]').value=this.value" />
            </div>
        </div>
        <div class="form-group">
            <label>Logo URL</label>
            <input type="text" name="logo_url" value="{{ $brand->logo_url }}" placeholder="https://..." />
        </div>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ $brand->is_active ? 'checked' : '' }}> Brand Aktif</label>
    </div>
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:4px">Point Rules</h3>
    <p style="font-size:12px;color:#999;margin-bottom:16px">Mengatur berapa Rupiah yang setara dengan 1 poin per sumber transaksi</p>
    @foreach(['instore' => 'Di Toko (Kasir)', 'inapp' => 'Via Aplikasi', 'webstore' => 'Via Webstore'] as $source => $label)
    <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:12px">
        <div style="font-weight:500;margin-bottom:12px;font-size:13px;color:#1a1a2e">{{ $label }}</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
            <div class="form-group" style="margin:0">
                <label>Rp per Poin</label>
                <input type="number" name="rules[{{ $source }}][rp_per_point]"
                    value="{{ $rules[$source]->rp_per_point ?? 10000 }}" min="1" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Multiplier</label>
                <input type="number" step="0.01" name="rules[{{ $source }}][multiplier]"
                    value="{{ $rules[$source]->multiplier ?? 1.00 }}" min="0.1" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Min. Transaksi (Rp)</label>
                <input type="number" name="rules[{{ $source }}][min_transaction]"
                    value="{{ $rules[$source]->min_transaction ?? 0 }}" min="0" />
            </div>
            <div class="form-group" style="margin:0">
                <label>Aktif</label><br>
                <input type="checkbox" name="rules[{{ $source }}][is_active]" value="1"
                    {{ ($rules[$source]->is_active ?? true) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:4px">Tier Rules</h3>
    <p style="font-size:12px;color:#999;margin-bottom:16px">Tentukan minimal poin untuk setiap tier. Kosongkan untuk pakai default sistem.</p>

    @php
        $defaultTiers = [
            'bronze'   => ['min' => 0,     'color' => '#92400e'],
            'silver'   => ['min' => 5000,  'color' => '#475569'],
            'gold'     => ['min' => 20000, 'color' => '#854d0e'],
            'platinum' => ['min' => 50000, 'color' => '#0369a1'],
        ];
        $existingTiers = $brand->tierRules->keyBy('tier');
    @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px">
    @foreach($defaultTiers as $tierName => $default)
    <div style="background:#f9fafb;border-radius:8px;padding:14px">
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px">
            <div style="width:12px;height:12px;border-radius:50%;background:{{ $existingTiers[$tierName]->color ?? $default['color'] }}"></div>
            <strong style="font-size:13px;text-transform:capitalize">{{ $tierName }}</strong>
        </div>
        <div class="form-group" style="margin-bottom:8px">
            <label>Min. Poin</label>
            <input type="number" name="tiers[{{ $tierName }}][min_points]"
                value="{{ $existingTiers[$tierName]->min_points ?? $default['min'] }}" min="0" />
        </div>
        <div class="form-group" style="margin-bottom:8px">
            <label>Warna Badge</label>
            <div style="display:flex;gap:6px;align-items:center">
                <input type="color" name="tiers[{{ $tierName }}][color]"
                    value="{{ $existingTiers[$tierName]->color ?? $default['color'] }}"
                    style="width:36px;height:32px;padding:1px;cursor:pointer" />
                <span style="font-size:11px;color:#888">{{ $existingTiers[$tierName]->color ?? $default['color'] }}</span>
            </div>
        </div>
        <div class="form-group" style="margin:0">
            <label>Benefit</label>
            <input type="text" name="tiers[{{ $tierName }}][benefits]"
                value="{{ $existingTiers[$tierName]->benefits ?? '' }}"
                placeholder="contoh: Diskon 5%" style="font-size:12px" />
        </div>
    </div>
    @endforeach
    </div>
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">WooCommerce Integration</h3>
    <p style="font-size:12px;color:#999;margin-bottom:12px">
        Webhook URL: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;color:#4f46e5">{{ url('/api/v1/webhooks/woocommerce/' . $brand->slug) }}</code>
    </p>
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
</div>

<div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-primary">Simpan Semua Perubahan</button>
    <a href="{{ route('admin.brands.index') }}" class="btn" style="background:#f3f4f6;color:#333">Kembali</a>
</div>
</form>
@endsection
