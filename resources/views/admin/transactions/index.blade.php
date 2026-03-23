@extends('admin.layout')
@section('title', 'Transaksi')
@section('content')
<div class="page-title">Transaksi</div>
<div class="card">
    <form method="GET" style="display:grid;grid-template-columns:1fr 160px 160px auto;gap:8px;margin-bottom:16px">
        <input type="text" name="search" placeholder="Cari member..." value="{{ request('search') }}" />
        <select name="brand_id">
            <option value="">Semua Brand</option>
            @foreach($brands as $b)
            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        <select name="source">
            <option value="">Semua Source</option>
            <option value="instore" {{ request('source') == 'instore' ? 'selected' : '' }}>In-store</option>
            <option value="inapp" {{ request('source') == 'inapp' ? 'selected' : '' }}>In-app</option>
            <option value="webstore" {{ request('source') == 'webstore' ? 'selected' : '' }}>Webstore</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <table>
        <thead><tr><th>Member</th><th>Brand</th><th>Invoice</th><th>Nominal</th><th>Poin</th><th>Source</th><th>Waktu</th></tr></thead>
        <tbody>
        @foreach($transactions as $t)
        <tr>
            <td>{{ $t->user->name ?? '-' }}<br><small style="color:#999">{{ $t->user->phone ?? '' }}</small></td>
            <td>{{ $t->brand->name ?? '-' }}</td>
            <td style="font-size:12px;color:#999">{{ $t->invoice_number }}</td>
            <td>Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
            <td><strong style="color:#16a34a">+{{ $t->points_earned }}</strong></td>
            <td><span class="badge badge-{{ $t->source }}">{{ $t->source }}</span></td>
            <td style="font-size:12px;color:#999">{{ $t->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $transactions->links() }}</div>
</div>
@endsection
