<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Brand;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'brand'])->latest();

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
            );
        }

        $transactions = $query->paginate(25);
        $brands       = Brand::all();

        return view('admin.transactions.index', compact('transactions', 'brands'));
    }

    public function members(Request $request)
    {
        $query = User::with('brandProfiles.brand')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        $members = $query->paginate(25);
        return view('admin.members.index', compact('members'));
    }
}
