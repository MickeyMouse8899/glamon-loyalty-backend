<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandPointRule;
use App\Models\TierRule;
use App\Models\WebstoreIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('userProfiles')->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'primary_color' => 'required|string',
        ]);

        $brand = Brand::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'primary_color' => $request->primary_color,
            'logo_url'      => $request->logo_url,
            'is_active'     => true,
        ]);

        foreach (['instore', 'inapp', 'webstore'] as $source) {
            BrandPointRule::create([
                'brand_id'        => $brand->id,
                'source'          => $source,
                'rp_per_point'    => 10000,
                'multiplier'      => 1.00,
                'min_transaction' => 0,
                'is_active'       => true,
            ]);
        }

        $defaultTiers = [
            'bronze'   => ['min_points' => 0,     'color' => '#92400e'],
            'silver'   => ['min_points' => 5000,  'color' => '#475569'],
            'gold'     => ['min_points' => 20000, 'color' => '#854d0e'],
            'platinum' => ['min_points' => 50000, 'color' => '#0369a1'],
        ];

        foreach ($defaultTiers as $tier => $data) {
            TierRule::create(array_merge(['brand_id' => $brand->id, 'tier' => $tier], $data));
        }

        return redirect()->route('admin.brands.edit', $brand)
            ->with('success', "Brand {$brand->name} berhasil dibuat.");
    }

    public function edit(Brand $brand)
    {
        $rules       = BrandPointRule::where('brand_id', $brand->id)->get()->keyBy('source');
        $integration = WebstoreIntegration::where('brand_id', $brand->id)->first();
        $brand->load('tierRules');
        return view('admin.brands.edit', compact('brand', 'rules', 'integration'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name'          => 'required|string',
            'primary_color' => 'required|string',
        ]);

        $brand->update([
            'name'          => $request->name,
            'primary_color' => $request->primary_color,
            'logo_url'      => $request->logo_url ?? $brand->logo_url,
            'is_active'     => $request->boolean('is_active'),
        ]);

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

        if ($request->has('tiers')) {
            foreach ($request->tiers as $tierName => $tierData) {
                TierRule::updateOrCreate(
                    ['brand_id' => $brand->id, 'tier' => $tierName],
                    [
                        'min_points' => $tierData['min_points'] ?? 0,
                        'color'      => $tierData['color'] ?? '#92400e',
                        'benefits'   => $tierData['benefits'] ?? null,
                    ]
                );
            }
        }

        if ($request->filled('store_url')) {
            WebstoreIntegration::updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'platform'        => 'woocommerce',
                    'store_url'       => $request->store_url,
                    'consumer_key'    => $request->consumer_key ? Crypt::encryptString($request->consumer_key) : '',
                    'consumer_secret' => $request->consumer_secret ? Crypt::encryptString($request->consumer_secret) : '',
                    'webhook_secret'  => $request->webhook_secret ? Crypt::encryptString($request->webhook_secret) : '',
                    'is_active'       => true,
                ]
            );
        }

        return back()->with('success', 'Brand berhasil diupdate.');
    }

    public function toggle(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        return back()->with('success', 'Status brand diubah.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->userProfiles()->count() > 0) {
            return back()->with('error', "Brand tidak bisa dihapus karena sudah ada {$brand->userProfiles()->count()} member.");
        }
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil dihapus.');
    }
}
