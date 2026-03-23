@extends('admin.layout')
@section('title', 'Brands & Rules')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div class="page-title" style="margin:0">Brands & Point Rules</div>
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">+ Tambah Brand</a>
</div>

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Brand</th>
                <th>Warna</th>
                <th>Members</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($brands as $brand)
        <tr>
            <td>
                <strong>{{ $brand->name }}</strong>
                <br><small style="color:#999">{{ $brand->slug }}</small>
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:20px;height:20px;border-radius:4px;background:{{ $brand->primary_color }};border:1px solid #ddd"></div>
                    <span style="font-size:12px;color:#666">{{ $brand->primary_color }}</span>
                </div>
            </td>
            <td>{{ number_format($brand->user_profiles_count) }} member</td>
            <td>
                <span class="badge" style="background:{{ $brand->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $brand->is_active ? '#166534' : '#dc2626' }}">
                    {{ $brand->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </td>
            <td style="display:flex;gap:6px">
                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-primary btn-sm">Edit Rules</a>
                <form method="POST" action="{{ route('admin.brands.toggle', $brand) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-sm" style="background:{{ $brand->is_active ? '#f59e0b' : '#16a34a' }};color:white">
                        {{ $brand->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}"
                    onsubmit="return confirm('Hapus brand {{ $brand->name }}?')" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
