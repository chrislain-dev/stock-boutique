<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'    => 'TechImport Cotonou',
                'phone'   => '+229 01 00 00 01',
                'email'   => 'contact@techimport.bj',
                'address' => 'Cotonou, Bénin',
                'country' => 'Bénin',
            ],
            [
                'name'    => 'Global Phone Togo',
                'phone'   => '+228 90 00 00 01',
                'email'   => 'contact@globalphone.tg',
                'address' => 'Lomé, Togo',
                'country' => 'Togo',
            ],
            [
                'name'    => 'Dubai Tech Supply',
                'phone'   => '+971 50 000 0001',
                'email'   => 'contact@dubaitechsupply.ae',
                'address' => 'Dubai, UAE',
                'country' => 'Émirats Arabes Unis',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create([...$supplier, 'is_active' => true]);
        }
    }
}
