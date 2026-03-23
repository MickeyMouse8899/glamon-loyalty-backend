<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\UserBrandProfile;
use App\Services\PointEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::where('is_active', true)->get();
        return response()->json($brands);
    }

    public function join(Request $request, Brand $brand)
    {
        $user = $request->user();

        $existing = UserBrandProfile::where('user_id', $user->id)
            ->where('brand_id', $brand->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah terdaftar di brand ini.',
                'profile' => $existing,
            ], 409);
        }

        $prefix = strtoupper(substr($brand->slug, 0, 3));
        $profile = UserBrandProfile::create([
            'user_id'     => $user->id,
            'brand_id'    => $brand->id,
            'member_code' => $prefix . '-' . strtoupper(Str::random(8)),
            'total_points' => 0,
            'tier'        => 'bronze',
        ]);

        return response()->json([
            'message' => 'Berhasil bergabung dengan ' . $brand->name,
            'profile' => $profile->load('brand'),
        ], 201);
    }

    public function profile(Request $request, Brand $brand)
    {
        $profile = UserBrandProfile::where('user_id', $request->user()->id)
            ->where('brand_id', $brand->id)
            ->with('brand')
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Anda belum terdaftar di brand ini.'], 404);
        }

        return response()->json($profile);
    }
}
