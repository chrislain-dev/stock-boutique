<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $before = $this->faker->numberBetween(0, 50);
        $qty    = $this->faker->numberBetween(1, 10);

        return [
            'product_model_id' => ProductModel::factory(),
            'product_id'       => null,
            'type'             => StockMovementType::STOCK_IN,
            'quantity'         => $qty,
            'quantity_before'  => $before,
            'quantity_after'   => $before + $qty,
            'location_from'    => null,
            'location_to'      => 'store',
            'moveable_type'    => null,
            'moveable_id'      => null,
            'notes'            => null,
            'created_by'       => User::factory(),
        ];
    }

    public function saleOut(): static
    {
        return $this->state([
            'type'          => StockMovementType::SALE_OUT,
            'location_from' => 'store',
            'location_to'   => 'client',
        ]);
    }

    public function transfer(): static
    {
        return $this->state([
            'type'          => StockMovementType::TRANSFER,
            'location_from' => 'store',
            'location_to'   => 'reseller',
        ]);
    }
}
