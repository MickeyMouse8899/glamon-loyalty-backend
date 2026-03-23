@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
<div class="page-title">Dashboard</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="value">{{ number_format($stats['total_members']) }}</div>
        <div class="label">Total Members</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ number_format($stats['total_transactions']) }}</div>
        <div class="label">Total Transaksi</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ number_format($stats['total_points_given']) }}</div>
        <div class="label">Total Poin Diberikan</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ number_format($stats['total_redemptions']) }}</div>
        <div class="label">Total Redemptions</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="card">
        <h3 style="font-size:14px;margin-bottom:14px;color:#444">Poin per Brand</h3>
        @foreach($pointsPerBrand as $brand)
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px">
            <span>{{ $brand->name }}</span>
            <strong>{{ number_format($brand->total_points ?? 0) }} poin</strong>
        </div>
        @endforeach
    </div>
    <div class="card">
        <h3 style="font-size:14px;margin-bottom:14px;color:#444">Member per Tier</h3>
        @foreach($membersByTier as $tier)
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px">
            <span class="badge badge-{{ $tier->tier }}">{{ ucfirst($tier->tier) }}</span>
            <strong>{{ number_format($tier->total) }} member</strong>
        </div>
        @endforeach
    </div>
</div>

<div class="card" style="margin-top:16px">
    <h3 style="font-size:14px;margin-bottom:14px;color:#444">Transaksi Terbaru</h3>
    <table>
        <thead><tr><th>Member</th><th>Brand</th><th>Nominal</th><th>Poin</th><th>Source</th><th>Waktu</th></tr></thead>
        <tbody>
        @foreach($recentTransactions as $t)
        <tr>
            <td>{{ $t->user->name ?? '-' }}<br><small style="color:#999">{{ $t->user->phone ?? '' }}</small></td>
            <td>{{ $t->brand->name ?? '-' }}</td>
            <td>Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
            <td><strong>+{{ $t->points_earned }}</strong></td>
            <td><span class="badge badge-{{ $t->source }}">{{ $t->source }}</span></td>
            <td style="color:#999">{{ $t->created_at->diffForHumans() }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
