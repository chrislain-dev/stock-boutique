<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Enums\ProductState;
use App\Enums\ProductLocation;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::first();

        $models = ProductModel::where('is_serialized', true)
            ->where('is_active', true)
            ->get();

        if ($models->isEmpty()) {
            $this->command->warn('Aucun modèle sérialisé trouvé — lance d\'abord ProductModelSeeder.');
            return;
        }

        foreach ($models as $model) {
            // 3 unités disponibles par modèle
            for ($i = 1; $i <= 3; $i++) {
                Product::create([
                    'product_model_id' => $model->id,
                    'imei'             => $this->generateImei(),
                    'serial_number'    => null,
                    'state'            => ProductState::AVAILABLE->value,
                    'location'         => ProductLocation::STORE->value,
                    'defects'          => null,
                    'purchase_price'   => $model->default_purchase_price ?? 100000,
                    'client_price'     => $model->default_client_price ?? 150000,
                    'reseller_price'   => $model->default_reseller_price ?? 130000,
                    'purchase_date'    => now()->subDays(rand(1, 90)),
                    'supplier_id'      => $supplier?->id,
                    'notes'            => null,
                    'created_by'       => 1,
                    'updated_by'       => 1,
                ]);
            }

            // 1 unité vendue par modèle
            Product::create([
                'product_model_id' => $model->id,
                'imei'             => $this->generateImei(),
                'state'            => ProductState::SOLD->value,
                'location'         => ProductLocation::CLIENT->value,
                'defects'          => null,
                'purchase_price'   => $model->default_purchase_price ?? 100000,
                'client_price'     => $model->default_client_price ?? 150000,
                'reseller_price'   => $model->default_reseller_price ?? 130000,
                'purchase_date'    => now()->subDays(rand(91, 180)),
                'supplier_id'      => $supplier?->id,
                'created_by'       => 1,
                'updated_by'       => 1,
            ]);
        }

        $this->command->info('Produits créés : ' . Product::count());
    }

    private function generateImei(): string
    {
        do {
            $imei = '35' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (Product::where('imei', $imei)->exists());

        return $imei;
    }
}
