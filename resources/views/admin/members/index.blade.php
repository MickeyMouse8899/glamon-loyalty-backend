@extends('admin.layout')
@section('title', 'Members')
@section('content')
<div class="page-title">Members ({{ number_format($members->total()) }})</div>
<div class="card">
    <form method="GET" style="display:flex;gap:8px;margin-bottom:16px">
        <input type="text" name="search" placeholder="Cari nama, email, atau HP..." value="{{ request('search') }}" style="flex:1" />
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
    <table>
        <thead><tr><th>Member</th><th>HP</th><th>Brand & Poin</th><th>Bergabung</th></tr></thead>
        <tbody>
        @foreach($members as $member)
        <tr>
            <td>
                <strong>{{ $member->name }}</strong><br>
                <small style="color:#999">{{ $member->email }}</small>
            </td>
            <td>{{ $member->phone }}</td>
            <td>
                @foreach($member->brandProfiles as $profile)
                <div style="font-size:12px;margin-bottom:2px">
                    {{ $profile->brand->name ?? '-' }}:
                    <strong>{{ number_format($profile->total_points) }} poin</strong>
                    <span class="badge badge-{{ $profile->tier }}">{{ $profile->tier }}</span>
                </div>
                @endforeach
            </td>
            <td style="font-size:12px;color:#999">{{ $member->created_at->format('d/m/Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $members->links() }}</div>
</div>
@endsection
