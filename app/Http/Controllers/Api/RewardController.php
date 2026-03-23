<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\UserBrandProfile;
use App\Services\PointEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardController extends Controller
{
    public function __construct(protected PointEngine $pointEngine) {}

    public function index(Request $request, Brand $brand)
    {
        $rewards = Reward::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            })
            ->get();

        $userPoints = $request->user()->getPointsForBrand($brand->id);

        return response()->json([
            'your_points' => $userPoints,
            'rewards'     => $rewards,
        ]);
    }

    public function redeem(Request $request, Reward $reward)
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:10',
        ]);

        $quantity = $request->quantity ?? 1;
        $user = $request->user();
        $totalPoints = $reward->points_required * $quantity;

        return DB::transaction(function () use ($user, $reward, $quantity, $totalPoints) {
            $profile = UserBrandProfile::where('user_id', $user->id)
                ->where('brand_id', $reward->brand_id)
                ->lockForUpdate()
                ->first();

            if (!$profile) {
                return response()->json(['message' => 'Anda belum terdaftar di brand ini.'], 403);
            }

            if ($profile->total_points < $totalPoints) {
                return response()->json([
                    'message'        => 'Poin tidak mencukupi.',
                    'required'       => $totalPoints,
                    'your_points'    => $profile->total_points,
                ], 422);
            }

            if (!$reward->unlimited_stock && $reward->stock < $quantity) {
                return response()->json(['message' => 'Stok reward tidak mencukupi.'], 422);
            }

            if (!$reward->unlimited_stock) {
                $reward->decrement('stock', $quantity);
            }

            $this->pointEngine->redeemPoints(
                $user->id,
                $reward->brand_id,
                $totalPoints,
                "Redeem: {$reward->name} x{$quantity}"
            );

            $redemption = Redemption::create([
                'user_id'          => $user->id,
                'reward_id'        => $reward->id,
                'points_used'      => $totalPoints,
                'redemption_code'  => strtoupper(Str::random(12)),
                'status'           => 'pending',
                'expires_at'       => now()->addDays(30),
            ]);

            return response()->json([
                'message'          => 'Redeem berhasil!',
                'redemption_code'  => $redemption->redemption_code,
                'points_used'      => $totalPoints,
                'expires_at'       => $redemption->expires_at,
            ], 201);
        });
    }

    public function myRedemptions(Request $request)
    {
        $redemptions = Redemption::where('user_id', $request->user()->id)
            ->with('reward.brand')
            ->latest()
            ->paginate(20);

        return response()->json($redemptions);
    }
}