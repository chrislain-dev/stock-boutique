<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BrandSeeder::class,
            SupplierSeeder::class,
            SettingSeeder::class,
            ProductModelSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
