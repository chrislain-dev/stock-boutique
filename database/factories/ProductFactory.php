<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchase = fake()->numberBetween(50000, 100000);
        $client_price = $purchase + fake()->numberBetween(1000, 50000);
        $reseller_price = $client_price - fake()->numberBetween(1000, 20000);

        return [
            'product_model_id' => ProductModel::factory(),
            'imei'             => $this->faker->unique()->numerify('##############'),
            'serial_number'    => null,
            'state'            => 'available',
            'location'         => 'store',
            'defects'          => null,
            'purchase_price'   => $purchase,
            'client_price'     => $client_price,
            'reseller_price'   => $reseller_price,
            'purchase_date'    => $this->faker->dateTimeBetween('-6 months', 'now'),
            'supplier_id'      => Supplier::factory(),
            'notes'            => null,
            'created_by'       => User::factory(),
            'updated_by'       => null,
        ];
    }

    public function sold(): static
    {
        return $this->state(['state' => 'sold']);
    }

    public function defective(): static
    {
        return $this->state(['state' => 'defective']);
    }

    public function inRepair(): static
    {
        return $this->state(['state' => 'in_repair', 'location' => 'repair_shop']);
    }

    public function atReseller(): static
    {
        return $this->state(['location' => 'reseller']);
    }
}
