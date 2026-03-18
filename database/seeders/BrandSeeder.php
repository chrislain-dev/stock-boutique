<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Apple',
            'Samsung',
            'Huawei',
            'Xiaomi',
            'Oppo',
            'Tecno',
            'Infinix',
            'Itel',
            'Nokia',
            'Motorola',
            'HP',
            'Dell',
            'Lenovo',
            'Asus',
            'Acer',
            'Microsoft',
            'Sony',
            'LG',
            'OnePlus',
            'Realme',

            // ─── Sextoys ──────────────────────────────────────
            'Satisfyer',
            'Lovense',
            'We-Vibe',
            'Lelo',
            'Womanizer',
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name'      => $brand,
                'is_active' => true,
            ]);
        }
    }
}
