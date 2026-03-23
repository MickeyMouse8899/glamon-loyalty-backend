@extends('admin.layout')
@section('title', 'Integrations')
@section('content')
<div class="page-title">Integrations</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Webhook URLs</h3>
    <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:12px">
        <div style="font-weight:500;font-size:13px;margin-bottom:8px">WooCommerce</div>
        @foreach($brands as $brand)
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;font-size:12px">
            <span style="color:#666;width:80px">{{ $brand->name }}</span>
            <code style="background:#fff;border:1px solid #e5e7eb;padding:4px 8px;border-radius:4px;flex:1;color:#4f46e5">
                {{ url('/api/v1/webhooks/woocommerce/' . $brand->slug) }}
            </code>
        </div>
        @endforeach
    </div>

    <div style="background:#f9fafb;border-radius:8px;padding:16px">
        <div style="font-weight:500;font-size:13px;margin-bottom:8px">MokaPOS (GoBiz)</div>
        @foreach($brands as $brand)
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;font-size:12px">
            <span style="color:#666;width:80px">{{ $brand->name }}</span>
            <code style="background:#fff;border:1px solid #e5e7eb;padding:4px 8px;border-radius:4px;flex:1;color:#16a34a">
                {{ url('/api/v1/webhooks/mokapos/' . $brand->slug) }}
            </code>
        </div>
        @endforeach
    </div>
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Tambah / Update Integrasi</h3>
    <form method="POST" action="{{ route('admin.integrations.store') }}">
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
                <label>Platform</label>
                <select name="platform" id="platform-select" onchange="toggleFields()">
                    <option value="woocommerce">WooCommerce</option>
                    <option value="mokapos">MokaPOS</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
        </div>

        <div id="woo-fields">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>Store URL</label>
                    <input type="text" name="store_url" placeholder="https://toko.com" />
                </div>
                <div class="form-group">
                    <label>Webhook Secret</label>
                    <input type="text" name="webhook_secret" placeholder="secret key" />
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

        <div id="moka-fields" style="display:none">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>App ID (GoBiz)</label>
                    <input type="text" name="app_id" placeholder="GoBiz App ID" />
                </div>
                <div class="form-group">
                    <label>Outlet ID</label>
                    <input type="text" name="outlet_id" placeholder="MokaPOS Outlet ID" />
                </div>
                <div class="form-group">
                    <label>Webhook Secret</label>
                    <input type="text" name="webhook_secret" placeholder="secret key untuk verifikasi" />
                </div>
            </div>
            <div style="background:#eff6ff;border-radius:8px;padding:12px;font-size:12px;color:#1d4ed8;margin-bottom:14px">
                Daftarkan webhook URL di GoBiz Developer Portal → Webhooks → Add Webhook. Pilih event: <strong>transaction.completed</strong>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Integrasi</button>
    </form>
</div>

<div class="card">
    <h3 style="font-size:14px;margin-bottom:16px">Integrasi Aktif</h3>
    <table>
        <thead><tr><th>Brand</th><th>Platform</th><th>Store URL / Outlet</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($integrations as $integration)
        <tr>
            <td>{{ $integration->brand->name ?? '-' }}</td>
            <td>
                <span class="badge" style="background:{{ $integration->platform === 'woocommerce' ? '#eff6ff' : '#f0fdf4' }};color:{{ $integration->platform === 'woocommerce' ? '#1d4ed8' : '#16a34a' }}">
                    {{ ucfirst($integration->platform) }}
                </span>
            </td>
            <td style="font-size:12px;color:#666">{{ $integration->store_url ?: ($integration->outlet_id ? 'Outlet: '.$integration->outlet_id : '-') }}</td>
            <td>
                <span class="badge" style="background:{{ $integration->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $integration->is_active ? '#166534' : '#dc2626' }}">
                    {{ $integration->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </td>
            <td>
                <form method="POST" action="{{ route('admin.integrations.toggle', $integration) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-sm {{ $integration->is_active ? 'btn-danger' : 'btn-success' }}">
                        {{ $integration->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#999">Belum ada integrasi</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
function toggleFields() {
    const platform = document.getElementById('platform-select').value;
    document.getElementById('woo-fields').style.display = platform === 'woocommerce' ? 'block' : 'none';
    document.getElementById('moka-fields').style.display = platform === 'mokapos' ? 'block' : 'none';
}
</script>
@endsection
