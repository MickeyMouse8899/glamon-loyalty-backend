@extends('admin.layout')
@section('title', 'Rewards')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div class="page-title" style="margin:0">Rewards</div>
    <a href="{{ route('admin.rewards.create') }}" class="btn btn-primary">+ Tambah Reward</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Reward</th><th>Brand</th><th>Poin</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($rewards as $reward)
        <tr>
            <td><strong>{{ $reward->name }}</strong></td>
            <td>{{ $reward->brand->name }}</td>
            <td>{{ number_format($reward->points_required) }} poin</td>
            <td>{{ $reward->unlimited_stock ? '∞' : number_format($reward->stock) }}</td>
            <td><span class="badge" style="background:{{ $reward->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $reward->is_active ? '#166534' : '#dc2626' }}">{{ $reward->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
                <form method="POST" action="{{ route('admin.rewards.toggle', $reward) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-sm {{ $reward->is_active ? 'btn-danger' : 'btn-success' }}">{{ $reward->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $rewards->links() }}</div>
</div>
@endsection
