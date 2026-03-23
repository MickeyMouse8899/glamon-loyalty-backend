<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index()
    {
        $rewards = Reward::with('brand')->latest()->paginate(20);
        return view('admin.rewards.index', compact('rewards'));
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->get();
        return view('admin.rewards.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id'        => 'required|exists:brands,id',
            'name'            => 'required|string',
            'description'     => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'stock'           => 'required_if:unlimited_stock,false|integer|min:0',
            'unlimited_stock' => 'boolean',
            'valid_until'     => 'nullable|date',
        ]);

        Reward::create([
            'brand_id'        => $request->brand_id,
            'name'            => $request->name,
            'description'     => $request->description,
            'points_required' => $request->points_required,
            'stock'           => $request->stock ?? 0,
            'unlimited_stock' => $request->boolean('unlimited_stock'),
            'is_active'       => true,
            'valid_until'     => $request->valid_until,
        ]);

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil dibuat.');
    }

    public function toggle(Reward $reward)
    {
        $reward->update(['is_active' => !$reward->is_active]);
        return back()->with('success', 'Status reward diubah.');
    }
}
