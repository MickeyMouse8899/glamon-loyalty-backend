<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $winix = Brand::where('slug', 'winix')->first();

        Reward::create([
            'brand_id'        => $winix->id,
            'name'            => 'Voucher Diskon 50rb',
            'description'     => 'Voucher diskon Rp50.000 untuk pembelian berikutnya',
            'points_required' => 100,
            'stock'           => 50,
            'unlimited_stock' => false,
            'is_active'       => true,
        ]);

        Reward::create([
            'brand_id'        => $winix->id,
            'name'            => 'Free Filter HEPA',
            'description'     => 'Filter HEPA gratis untuk air purifier Winix',
            'points_required' => 500,
            'stock'           => 10,
            'unlimited_stock' => false,
            'is_active'       => true,
        ]);
    }
}
