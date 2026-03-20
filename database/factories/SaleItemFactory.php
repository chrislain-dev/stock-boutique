<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $qty        = 1;
        $unitPrice  = $this->faker->random_int(10000, 300000);
        $purchPrice = $unitPrice * 0.8;
        $discount   = 0;

        return [
            'sale_id'                  => Sale::factory(),
            'product_model_id'         => ProductModel::factory(),
            'product_id'               => Product::factory(),
            'quantity'                 => $qty,
            'unit_price'               => $unitPrice,
            'purchase_price_snapshot'  => $purchPrice,
            'discount'                 => $discount,
            'line_total'               => ($qty * $unitPrice) - $discount,
        ];
    }
}
