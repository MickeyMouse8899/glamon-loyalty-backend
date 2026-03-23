<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $currentUser = User::find(session('admin_user_id'));
        $staff = User::whereIn('role', ['superadmin', 'admin', 'kasir'])
            ->latest()->get();
        return view('admin.users.index', compact('staff', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = User::find(session('admin_user_id'));

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:superadmin,admin,kasir',
        ]);

        if ($request->role === 'superadmin' && !$currentUser->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Hanya superadmin yang bisa membuat superadmin.']);
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'is_active'=> true,
        ]);

        return back()->with('success', 'Staff berhasil ditambahkan.');
    }

    public function toggleActive(User $user)
    {
        $currentUser = User::find(session('admin_user_id'));

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status user diubah.');
    }

    public function updateRole(Request $request, User $user)
    {
        $currentUser = User::find(session('admin_user_id'));

        if (!$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Hanya superadmin yang bisa ubah role.');
        }

        $request->validate(['role' => 'required|in:superadmin,admin,kasir']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Role berhasil diubah.');
    }
}
