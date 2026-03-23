<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Redemption;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBrandProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function loginForm()
    {
        if (session('admin_user_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.']);
        }

        if (!$user->isKasir()) {
            return back()->withErrors(['email' => 'Akun tidak memiliki akses panel ini.']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Akun tidak aktif.']);
        }

        session([
            'admin_user_id'   => $user->id,
            'admin_user_name' => $user->name,
            'admin_user_role' => $user->role,
        ]);
        session()->save();

        if ($user->isKasir() && !$user->isAdmin()) {
            return redirect('/kasir');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('admin.login');
    }

    public function index()
    {
        $user = User::find(session('admin_user_id'));
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('admin.login');
        }

        $stats = [
            'total_members'      => User::where('role', 'member')->count(),
            'total_transactions' => Transaction::count(),
            'total_points_given' => Transaction::sum('points_earned'),
            'total_redemptions'  => Redemption::count(),
        ];

        $recentTransactions = Transaction::with(['user', 'brand'])
            ->latest()->limit(10)->get();

        $pointsPerBrand = Brand::withSum('transactions as total_points', 'points_earned')->get();

        $membersByTier = UserBrandProfile::select('tier', DB::raw('count(*) as total'))
            ->groupBy('tier')->get();

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'pointsPerBrand', 'membersByTier'));
    }
}
