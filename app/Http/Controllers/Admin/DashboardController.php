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

class DashboardController extends Controller
{
    public function loginForm()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $input   = $request->input('password');
        $correct = 'password123';

        if ($input !== $correct) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        session(['admin_logged_in' => true]);
        session()->save();

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('admin.login');
    }

    public function index()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $stats = [
            'total_members'      => User::count(),
            'total_transactions' => Transaction::count(),
            'total_points_given' => Transaction::sum('points_earned'),
            'total_redemptions'  => Redemption::count(),
        ];

        $recentTransactions = Transaction::with(['user', 'brand'])
            ->latest()
            ->limit(10)
            ->get();

        $pointsPerBrand = Brand::withSum('transactions as total_points', 'points_earned')
            ->get();

        $membersByTier = UserBrandProfile::select('tier', DB::raw('count(*) as total'))
            ->groupBy('tier')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'pointsPerBrand', 'membersByTier'));
    }
}
