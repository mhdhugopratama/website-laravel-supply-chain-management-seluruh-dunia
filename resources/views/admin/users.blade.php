@extends('layouts.app')
@section('title', 'Manage Users — Admin')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <div>
            <h1><i class="bi bi-people-fill"></i> User Management</h1>
            <p>{{ $users->total() }} registered users</p>
        </div>
        <a href="{{ route('admin.index') }}" class="nb-btn nb-btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="nb-card">
        <div class="nb-card-body p-0">
            <table class="nb-table">
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="nb-pill {{ $user->role === 'admin' ? 'nb-pill-admin' : 'nb-pill-user' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="d-flex gap-1">
                                    @csrf @method('PUT')
                                    <select name="role" class="nb-select" style="width:90px;font-size:0.78rem">
                                        <option value="user" {{ $user->role==='user'?'selected':'' }}>user</option>
                                        <option value="admin" {{ $user->role==='admin'?'selected':'' }}>admin</option>
                                    </select>
                                    <button type="submit" class="nb-btn nb-btn-cyan btn-sm"><i class="bi bi-check"></i></button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete user?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="nb-btn nb-btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="nb-card-body pt-0">
            <div class="nb-pagination">{{ $users->links('vendor.pagination.nb') }}</div>
        </div>
    </div>
</div>
@endsection
