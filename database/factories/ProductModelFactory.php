<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductModelFactory extends Factory
{
    protected $model = ProductModel::class;

    public function definition(): array
    {
        return [
            'brand_id'               => Brand::factory(),
            'name'                   => $this->faker->word() . ' ' . $this->faker->numerify('###'),
            'category'               => $this->faker->randomElement(['telephone', 'pc', 'tablet', 'accessory']),
            'is_serialized'          => true,
            'is_active'              => true,
            'quantity_stock'         => 0,
            'stock_minimum'          => 2,
            'default_purchase_price' => $this->faker->random_int(50000, 500000),
            'default_client_price'   => $this->faker->random_int(60000, 600000),
            'default_reseller_price' => $this->faker->random_int(55000, 550000),
        ];
    }

    public function nonSerialized(): static
    {
        return $this->state([
            'is_serialized'  => false,
            'quantity_stock' => $this->faker->numberBetween(1, 100),
        ]);
    }

    public function lowStock(): static
    {
        return $this->state([
            'quantity_stock' => 1,
            'stock_minimum'  => 5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
