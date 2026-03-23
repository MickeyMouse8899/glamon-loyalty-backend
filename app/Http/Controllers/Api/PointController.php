<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\PointLedger;
use App\Models\UserBrandProfile;
use App\Services\PointEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PointController extends Controller
{
    public function __construct(protected PointEngine $pointEngine) {}

    public function balance(Request $request, Brand $brand)
    {
        $profile = UserBrandProfile::where('user_id', $request->user()->id)
            ->where('brand_id', $brand->id)
            ->with('brand')
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Anda belum terdaftar di brand ini.'], 404);
        }

        return response()->json([
            'brand'        => $profile->brand->name,
            'member_code'  => $profile->member_code,
            'total_points' => $profile->total_points,
            'tier'         => $profile->tier,
        ]);
    }

    public function history(Request $request, Brand $brand)
    {
        $history = PointLedger::where('user_id', $request->user()->id)
            ->where('brand_id', $brand->id)
            ->with('transaction')
            ->latest()
            ->paginate(20);

        return response()->json($history);
    }

    public function allBalance(Request $request)
    {
        $profiles = UserBrandProfile::where('user_id', $request->user()->id)
            ->with('brand')
            ->get()
            ->map(fn($p) => [
                'brand'        => $p->brand->name,
                'brand_id'     => $p->brand_id,
                'member_code'  => $p->member_code,
                'total_points' => $p->total_points,
                'tier'         => $p->tier,
            ]);

        return response()->json($profiles);
    }

    public function earnInstore(Request $request)
    {
        $request->validate([
            'brand_id'  => 'required|exists:brands,id',
            'amount'    => 'required|numeric|min:1000',
            'invoice'   => 'required|string',
        ]);

        $transaction = $this->pointEngine->earnPoints(
            userId: $request->user()->id,
            brandId: $request->brand_id,
            amount: $request->amount,
            source: 'instore',
            invoiceNumber: $request->invoice,
        );

        return response()->json([
            'message'       => 'Poin berhasil ditambahkan.',
            'points_earned' => $transaction->points_earned,
            'total_points'  => $request->user()->getPointsForBrand($request->brand_id),
        ], 201);
    }
}
