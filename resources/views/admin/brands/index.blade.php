@extends('admin.layout')
@section('title', 'Brands & Rules')
@section('content')
<div class="page-title">Brands & Point Rules</div>
<div class="card">
    <table>
        <thead><tr><th>Brand</th><th>Members</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($brands as $brand)
        <tr>
            <td><strong>{{ $brand->name }}</strong><br><small style="color:#999">{{ $brand->slug }}</small></td>
            <td>{{ number_format($brand->user_profiles_count) }} member</td>
            <td><span class="badge" style="background:{{ $brand->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $brand->is_active ? '#166534' : '#dc2626' }}">{{ $brand->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td><a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-primary btn-sm">Edit Rules</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
