<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BrandPointRule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Winix', 'slug' => 'winix', 'primary_color' => '#0066CC'],
            ['name' => "De'Longhi", 'slug' => 'delonghi', 'primary_color' => '#CC0000'],
            ['name' => 'Remington', 'slug' => 'remington', 'primary_color' => '#006633'],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::create(array_merge($brandData, ['is_active' => true]));

            foreach (['instore', 'inapp', 'webstore'] as $source) {
                BrandPointRule::create([
                    'brand_id'        => $brand->id,
                    'source'          => $source,
                    'rp_per_point'    => match($brand->slug) {
                        'winix'     => 5000,
                        'delonghi'  => 10000,
                        'remington' => 7500,
                    },
                    'multiplier'      => 1.00,
                    'min_transaction' => 50000,
                    'is_active'       => true,
                ]);
            }
        }
    }
}
