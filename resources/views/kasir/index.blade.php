<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir Panel - Loyalty Point</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f5; color: #333; }
        .header { background: #1a1a2e; color: white; padding: 16px 24px; }
        .header h1 { font-size: 18px; font-weight: 600; }
        .tabs { display: flex; background: white; border-bottom: 1px solid #e5e7eb; padding: 0 24px; }
        .tab { padding: 14px 20px; font-size: 14px; cursor: pointer; border-bottom: 2px solid transparent; color: #6b7280; }
        .tab.active { border-bottom-color: #4f46e5; color: #4f46e5; font-weight: 600; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .container { max-width: 600px; margin: 24px auto; padding: 0 16px; }
        .card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .card h2 { font-size: 15px; font-weight: 600; margin-bottom: 16px; color: #444; }
        .form-group { margin-bottom: 14px; }
        label { display: block; font-size: 13px; color: #666; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #4f46e5; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 4px; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-success { background: #16a34a; color: white; }
        .btn-warning { background: #d97706; color: white; }
        .member-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .member-info .name { font-size: 16px; font-weight: 600; color: #166534; }
        .brand-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .brand-pill { background: white; border: 1px solid #d1fae5; border-radius: 20px; padding: 4px 12px; font-size: 12px; color: #065f46; }
        .result-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-top: 16px; text-align: center; }
        .result-box .points { font-size: 32px; font-weight: 700; color: #1d4ed8; }
        .verify-box { border-radius: 8px; padding: 16px; margin-top: 16px; }
        .verify-valid { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .verify-invalid { background: #fef2f2; border: 1px solid #fecaca; }
        .verify-box .reward-name { font-size: 18px; font-weight: 700; color: #166534; }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-top: 12px; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .hidden { display: none; }
        .tier-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .tier-bronze { background: #fef3c7; color: #92400e; }
        .tier-silver { background: #f1f5f9; color: #475569; }
        .tier-gold { background: #fef9c3; color: #854d0e; }
        .tier-platinum { background: #f0f9ff; color: #0369a1; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .info-row:last-child { border: none; }
    </style>
</head>
<body>

<div class="header">
    <h1>Kasir Panel — Loyalty Point</h1>
</div>

<div class="tabs">
    <div class="tab active" onclick="switchTab('earn')">Tambah Poin</div>
    <div class="tab" onclick="switchTab('redeem')">Verifikasi Redeem</div>
</div>

<!-- TAB: Earn Points -->
<div id="tab-earn" class="tab-content active">
<div class="container">
    <div class="card">
        <h2>Cari Member by Nomor HP</h2>
        <div class="form-group">
            <label>Nomor HP Member</label>
            <input type="text" id="phone" placeholder="08xxxxxxxxxx" />
        </div>
        <button class="btn btn-primary" onclick="cariMember()">Cari Member</button>
        <div id="member-info" class="member-info hidden">
            <div class="name" id="member-name"></div>
            <div style="font-size:13px;color:#4ade80;margin-top:2px" id="member-phone"></div>
            <div class="brand-pills" id="member-brands"></div>
        </div>
        <div id="alert-notfound" class="alert alert-error hidden">
            Member tidak ditemukan. Minta customer untuk daftar di aplikasi.
        </div>
    </div>

    <div class="card hidden" id="transaksi-card">
        <h2>Input Transaksi</h2>
        <input type="hidden" id="user-id" />
        <div class="form-group">
            <label>Brand</label>
            <select id="brand-id">
                @foreach($brands as $brand)
                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Total Belanja (Rp)</label>
            <input type="number" id="amount" placeholder="250000" min="1000" />
        </div>
        <button class="btn btn-success" onclick="prosesTransaksi()">Proses & Tambah Poin</button>
        <div id="result-box" class="result-box hidden">
            <div class="points" id="result-points">0</div>
            <div style="font-size:13px;color:#3b82f6;margin-top:4px">poin ditambahkan</div>
            <div style="margin-top:8px;font-size:13px;color:#3b82f6">Total poin: <strong id="result-total">0</strong></div>
            <div style="margin-top:4px;font-size:12px;color:#93c5fd" id="result-invoice"></div>
        </div>
    </div>
</div>
</div>

<!-- TAB: Verifikasi Redeem -->
<div id="tab-redeem" class="tab-content">
<div class="container">
    <div class="card">
        <h2>Verifikasi Kode Redeem</h2>
        <div class="form-group">
            <label>Kode Redeem dari Aplikasi</label>
            <input type="text" id="redeem-code" placeholder="XXXXXXXXXXXX" style="text-transform:uppercase;letter-spacing:2px;font-size:18px;text-align:center" />
        </div>
        <button class="btn btn-primary" onclick="verifikasiKode()">Verifikasi Kode</button>

        <div id="verify-result" class="hidden">
            <div id="verify-valid-box" class="verify-box verify-valid hidden">
                <div class="reward-name" id="verify-reward"></div>
                <div style="font-size:13px;color:#4ade80;margin-top:4px" id="verify-brand"></div>
                <div style="margin-top:12px">
                    <div class="info-row"><span>Member</span><strong id="verify-member"></strong></div>
                    <div class="info-row"><span>No. HP</span><strong id="verify-phone"></strong></div>
                    <div class="info-row"><span>Berlaku hingga</span><strong id="verify-expires"></strong></div>
                </div>
                <button class="btn btn-warning" style="margin-top:16px" onclick="claimKode()">Tandai Sudah Diklaim</button>
            </div>
            <div id="verify-invalid-box" class="verify-box verify-invalid hidden">
                <div style="font-weight:600;color:#dc2626" id="verify-error-msg"></div>
            </div>
        </div>

        <div id="claim-result" class="hidden"></div>
    </div>
</div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let currentRedeemCode = null;

function switchTab(tab) {
    document.querySelectorAll('.tab').forEach((t, i) => t.classList.toggle('active', (i === 0 && tab === 'earn') || (i === 1 && tab === 'redeem')));
    document.getElementById('tab-earn').classList.toggle('active', tab === 'earn');
    document.getElementById('tab-redeem').classList.toggle('active', tab === 'redeem');
}

async function cariMember() {
    const phone = document.getElementById('phone').value;
    ['member-info','alert-notfound','transaksi-card','result-box'].forEach(id => document.getElementById(id).classList.add('hidden'));

    const res = await fetch('/kasir/cari', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ phone })
    });
    const data = await res.json();

    if (!data.found) {
        document.getElementById('alert-notfound').classList.remove('hidden');
        return;
    }

    document.getElementById('member-name').textContent = data.user.name;
    document.getElementById('member-phone').textContent = data.user.phone;
    document.getElementById('user-id').value = data.user.id;

    const brandsEl = document.getElementById('member-brands');
    brandsEl.innerHTML = data.user.brands.map(b =>
        `<span class="brand-pill">${b.brand_name} · <strong>${b.total_points} poin</strong> · <span class="tier-badge tier-${b.tier}">${b.tier}</span></span>`
    ).join('');

    document.getElementById('member-info').classList.remove('hidden');
    document.getElementById('transaksi-card').classList.remove('hidden');
}

async function prosesTransaksi() {
    const res = await fetch('/kasir/transaksi', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({
            user_id: document.getElementById('user-id').value,
            brand_id: document.getElementById('brand-id').value,
            amount: document.getElementById('amount').value,
        })
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById('result-points').textContent = '+' + data.points_earned;
        document.getElementById('result-total').textContent = data.total_points;
        document.getElementById('result-invoice').textContent = 'Invoice: ' + data.invoice;
        document.getElementById('result-box').classList.remove('hidden');
        document.getElementById('amount').value = '';
    }
}

async function verifikasiKode() {
    const code = document.getElementById('redeem-code').value;
    ['verify-valid-box','verify-invalid-box','claim-result'].forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById('verify-result').classList.remove('hidden');

    const res = await fetch('/kasir/verifikasi', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ code })
    });
    const data = await res.json();

    if (data.valid) {
        currentRedeemCode = data.redemption.code;
        document.getElementById('verify-reward').textContent = data.redemption.reward;
        document.getElementById('verify-brand').textContent = data.redemption.brand;
        document.getElementById('verify-member').textContent = data.redemption.member;
        document.getElementById('verify-phone').textContent = data.redemption.phone;
        document.getElementById('verify-expires').textContent = new Date(data.redemption.expires_at).toLocaleDateString('id-ID');
        document.getElementById('verify-valid-box').classList.remove('hidden');
    } else {
        document.getElementById('verify-error-msg').textContent = data.message;
        document.getElementById('verify-invalid-box').classList.remove('hidden');
    }
}

async function claimKode() {
    const res = await fetch('/kasir/claim', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ code: currentRedeemCode })
    });
    const data = await res.json();
    document.getElementById('verify-valid-box').classList.add('hidden');
    const claimEl = document.getElementById('claim-result');
    claimEl.innerHTML = `<div class="alert ${data.success ? 'alert-success' : 'alert-error'}">${data.message}</div>`;
    claimEl.classList.remove('hidden');
    document.getElementById('redeem-code').value = '';
    currentRedeemCode = null;
}
</script>
</body>
</html>
