<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandPointRule;
use App\Models\WebstoreIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('userProfiles')->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function edit(Brand $brand)
    {
        $rules       = BrandPointRule::where('brand_id', $brand->id)->get()->keyBy('source');
        $integration = WebstoreIntegration::where('brand_id', $brand->id)->first();
        return view('admin.brands.edit', compact('brand', 'rules', 'integration'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name'          => 'required|string',
            'primary_color' => 'required|string',
            'is_active'     => 'boolean',
        ]);

        $brand->update($request->only(['name', 'primary_color', 'is_active']));

        foreach (['instore', 'inapp', 'webstore'] as $source) {
            if ($request->has("rules.{$source}.rp_per_point")) {
                BrandPointRule::updateOrCreate(
                    ['brand_id' => $brand->id, 'source' => $source],
                    [
                        'rp_per_point'    => $request->input("rules.{$source}.rp_per_point"),
                        'multiplier'      => $request->input("rules.{$source}.multiplier", 1),
                        'min_transaction' => $request->input("rules.{$source}.min_transaction", 0),
                        'is_active'       => $request->boolean("rules.{$source}.is_active"),
                    ]
                );
            }
        }

        if ($request->filled('store_url')) {
            WebstoreIntegration::updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'platform'       => 'woocommerce',
                    'store_url'      => $request->store_url,
                    'consumer_key'   => Crypt::encryptString($request->consumer_key ?? ''),
                    'consumer_secret'=> Crypt::encryptString($request->consumer_secret ?? ''),
                    'webhook_secret' => Crypt::encryptString($request->webhook_secret ?? ''),
                    'is_active'      => true,
                ]
            );
        }

        return back()->with('success', 'Brand berhasil diupdate.');
    }
}
