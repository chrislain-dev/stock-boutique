<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        $apple   = Brand::where('name', 'Apple')->first();
        $samsung = Brand::where('name', 'Samsung')->first();
        $hp      = Brand::where('name', 'HP')->first();
        $dell    = Brand::where('name', 'Dell')->first();
        $lenovo  = Brand::where('name', 'Lenovo')->first();
        $xiaomi  = Brand::where('name', 'Xiaomi')->first();

        $satisfyer  = Brand::where('name', 'Satisfyer')->first();
        $lovense    = Brand::where('name', 'Lovense')->first();
        $wevibe     = Brand::where('name', 'We-Vibe')->first();
        $lelo       = Brand::where('name', 'Lelo')->first();
        $womanizer  = Brand::where('name', 'Womanizer')->first();

        $models = [

            // ─── iPhones ──────────────────────────────────────────
            ['brand' => $apple, 'name' => 'iPhone 15 Pro Max', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 256, 'network' => '5G', 'sim_type' => 'eSIM', 'screen_size' => '6.7',
             'purchase' => 450000, 'client' => 550000, 'reseller' => 520000],

            ['brand' => $apple, 'name' => 'iPhone 15 Pro Max', 'category' => 'telephone', 'condition' => 'used',
             'storage_gb' => 256, 'network' => '5G', 'sim_type' => 'eSIM', 'screen_size' => '6.7',
             'purchase' => 280000, 'client' => 350000, 'reseller' => 320000],

            ['brand' => $apple, 'name' => 'iPhone 15 Pro Max', 'category' => 'telephone', 'condition' => 'refurbished',
             'storage_gb' => 256, 'network' => '5G', 'sim_type' => 'eSIM', 'screen_size' => '6.7',
             'purchase' => 320000, 'client' => 400000, 'reseller' => 370000],

            ['brand' => $apple, 'name' => 'iPhone 15', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'eSIM', 'screen_size' => '6.1',
             'purchase' => 320000, 'client' => 400000, 'reseller' => 370000],

            ['brand' => $apple, 'name' => 'iPhone 14', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'Nano/eSIM', 'screen_size' => '6.1',
             'purchase' => 250000, 'client' => 320000, 'reseller' => 295000],

            ['brand' => $apple, 'name' => 'iPhone 14', 'category' => 'telephone', 'condition' => 'used',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'Nano/eSIM', 'screen_size' => '6.1',
             'purchase' => 160000, 'client' => 210000, 'reseller' => 190000],

            ['brand' => $apple, 'name' => 'iPhone 13', 'category' => 'telephone', 'condition' => 'used',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'Nano/eSIM', 'screen_size' => '6.1',
             'purchase' => 120000, 'client' => 165000, 'reseller' => 150000],

            // ─── Samsung ──────────────────────────────────────────
            ['brand' => $samsung, 'name' => 'Galaxy S24 Ultra', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 256, 'network' => '5G', 'sim_type' => 'Nano/eSIM', 'screen_size' => '6.8',
             'purchase' => 420000, 'client' => 520000, 'reseller' => 490000],

            ['brand' => $samsung, 'name' => 'Galaxy S24', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'Nano/eSIM', 'screen_size' => '6.2',
             'purchase' => 280000, 'client' => 350000, 'reseller' => 320000],

            ['brand' => $samsung, 'name' => 'Galaxy A55', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 128, 'network' => '5G', 'sim_type' => 'Nano', 'screen_size' => '6.6',
             'purchase' => 130000, 'client' => 170000, 'reseller' => 155000],

            ['brand' => $samsung, 'name' => 'Galaxy A15', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 128, 'network' => '4G', 'sim_type' => 'Nano', 'screen_size' => '6.5',
             'purchase' => 65000, 'client' => 90000, 'reseller' => 80000],

            // ─── Xiaomi ───────────────────────────────────────────
            ['brand' => $xiaomi, 'name' => 'Redmi Note 13 Pro', 'category' => 'telephone', 'condition' => 'sealed',
             'storage_gb' => 256, 'ram_gb' => 8, 'network' => '4G', 'sim_type' => 'Nano', 'screen_size' => '6.67',
             'purchase' => 80000, 'client' => 110000, 'reseller' => 100000],

            // ─── PC HP ────────────────────────────────────────────
            ['brand' => $hp, 'name' => 'Pavilion 15', 'category' => 'pc', 'condition' => 'sealed',
             'storage_gb' => 512, 'ram_gb' => 8, 'storage_type' => 'SSD',
             'cpu' => 'Intel Core i5-1235U', 'cpu_generation' => '12th Gen',
             'gpu' => 'Intel Iris Xe', 'screen_size_pc' => '15.6', 'screen_resolution' => 'FHD',
             'os' => 'Windows 11', 'pc_type' => 'laptop',
             'purchase' => 200000, 'client' => 270000, 'reseller' => 250000],

            ['brand' => $hp, 'name' => 'Pavilion 15', 'category' => 'pc', 'condition' => 'used',
             'storage_gb' => 512, 'ram_gb' => 8, 'storage_type' => 'SSD',
             'cpu' => 'Intel Core i5-1235U', 'cpu_generation' => '12th Gen',
             'gpu' => 'Intel Iris Xe', 'screen_size_pc' => '15.6', 'screen_resolution' => 'FHD',
             'os' => 'Windows 11', 'pc_type' => 'laptop',
             'purchase' => 120000, 'client' => 165000, 'reseller' => 150000],

            ['brand' => $hp, 'name' => 'EliteBook 840 G10', 'category' => 'pc', 'condition' => 'sealed',
             'storage_gb' => 512, 'ram_gb' => 16, 'storage_type' => 'NVMe',
             'cpu' => 'Intel Core i7-1355U', 'cpu_generation' => '13th Gen',
             'gpu' => 'Intel Iris Xe', 'screen_size_pc' => '14', 'screen_resolution' => 'FHD',
             'os' => 'Windows 11 Pro', 'pc_type' => 'laptop',
             'purchase' => 380000, 'client' => 480000, 'reseller' => 450000],

            // ─── PC Dell ──────────────────────────────────────────
            ['brand' => $dell, 'name' => 'Inspiron 15 3000', 'category' => 'pc', 'condition' => 'sealed',
             'storage_gb' => 256, 'ram_gb' => 8, 'storage_type' => 'SSD',
             'cpu' => 'Intel Core i3-1215U', 'cpu_generation' => '12th Gen',
             'gpu' => 'Intel UHD', 'screen_size_pc' => '15.6', 'screen_resolution' => 'FHD',
             'os' => 'Windows 11', 'pc_type' => 'laptop',
             'purchase' => 160000, 'client' => 210000, 'reseller' => 195000],

            // ─── PC Lenovo ────────────────────────────────────────
            ['brand' => $lenovo, 'name' => 'ThinkPad E14 Gen 5', 'category' => 'pc', 'condition' => 'sealed',
             'storage_gb' => 512, 'ram_gb' => 16, 'storage_type' => 'NVMe',
             'cpu' => 'Intel Core i5-1335U', 'cpu_generation' => '13th Gen',
             'gpu' => 'Intel Iris Xe', 'screen_size_pc' => '14', 'screen_resolution' => 'FHD',
             'os' => 'Windows 11 Pro', 'pc_type' => 'laptop',
             'purchase' => 320000, 'client' => 410000, 'reseller' => 380000],

            // ─── iPad ─────────────────────────────────────────────
            ['brand' => $apple, 'name' => 'iPad Air M2', 'category' => 'tablet', 'condition' => 'sealed',
             'storage_gb' => 128, 'ram_gb' => 8, 'screen_size' => '11',
             'connectivity' => 'WiFi', 'stylus_support' => 'Apple Pencil Pro',
             'purchase' => 280000, 'client' => 360000, 'reseller' => 330000],

            ['brand' => $samsung, 'name' => 'Galaxy Tab S9', 'category' => 'tablet', 'condition' => 'sealed',
             'storage_gb' => 128, 'ram_gb' => 8, 'screen_size' => '11',
             'connectivity' => 'WiFi+4G', 'stylus_support' => 'S-Pen',
             'purchase' => 220000, 'client' => 290000, 'reseller' => 265000],

            // ─── Sextoys ──────────────────────────────────────────
            [
                'brand' => $satisfyer,
                'name' => 'Pro 2 Generation 3',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Stimulateur',
                'color' => 'Noir',
                'purchase' => 25000,
                'client' => 40000,
                'reseller' => 35000
            ],

            [
                'brand' => $satisfyer,
                'name' => 'Curvy 1+',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Stimulateur',
                'color' => 'Rose',
                'purchase' => 18000,
                'client' => 30000,
                'reseller' => 26000
            ],

            [
                'brand' => $lovense,
                'name' => 'Lush 3',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Vibromasseur connecté',
                'color' => 'Rose',
                'purchase' => 35000,
                'client' => 55000,
                'reseller' => 48000
            ],

            [
                'brand' => $lovense,
                'name' => 'Nora',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Vibromasseur connecté',
                'color' => 'Rose',
                'purchase' => 40000,
                'client' => 65000,
                'reseller' => 57000
            ],

            [
                'brand' => $wevibe,
                'name' => 'Chorus',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Vibromasseur couple',
                'color' => 'Violet',
                'purchase' => 45000,
                'client' => 72000,
                'reseller' => 63000
            ],

            [
                'brand' => $lelo,
                'name' => 'Sona 2',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Stimulateur sonic',
                'color' => 'Bordeaux',
                'purchase' => 38000,
                'client' => 60000,
                'reseller' => 53000
            ],

            [
                'brand' => $womanizer,
                'name' => 'Premium 2',
                'category' => 'sextoys',
                'condition' => 'sealed',
                'accessory_type' => 'Stimulateur',
                'color' => 'Noir',
                'purchase' => 42000,
                'client' => 68000,
                'reseller' => 59000
            ],
        ];

        foreach ($models as $m) {
            $brand = $m['brand'];
            if (!$brand) continue;

            ProductModel::firstOrCreate(
                [
                    'brand_id'   => $brand->id,
                    'name'       => $m['name'],
                    'storage_gb' => $m['storage_gb'] ?? null,
                    'ram_gb'     => $m['ram_gb'] ?? null,
                    'condition'  => $m['condition'],
                ],
                [
                    'category'               => $m['category'],
                    'model_number'           => null,
                    'is_serialized'          => true,
                    'is_active'              => true,
                    'color'                  => $m['color'] ?? null,
                    'storage_type'           => $m['storage_type'] ?? null,
                    'network'                => $m['network'] ?? null,
                    'sim_type'               => $m['sim_type'] ?? null,
                    'screen_size'            => $m['screen_size'] ?? null,
                    'cpu'                    => $m['cpu'] ?? null,
                    'cpu_generation'         => $m['cpu_generation'] ?? null,
                    'gpu'                    => $m['gpu'] ?? null,
                    'screen_size_pc'         => $m['screen_size_pc'] ?? null,
                    'screen_resolution'      => $m['screen_resolution'] ?? null,
                    'os'                     => $m['os'] ?? null,
                    'battery'                => $m['battery'] ?? null,
                    'pc_type'                => $m['pc_type'] ?? null,
                    'connectivity'           => $m['connectivity'] ?? null,
                    'stylus_support'         => $m['stylus_support'] ?? null,
                    'default_purchase_price' => $m['purchase'],
                    'default_client_price'   => $m['client'],
                    'default_reseller_price' => $m['reseller'],
                ]
            );
        }

        $this->command->info('Modèles créés : ' . ProductModel::count());
    }
}
