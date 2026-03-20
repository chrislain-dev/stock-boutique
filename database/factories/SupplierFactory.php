<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->company(),
            'phone'           => $this->faker->phoneNumber(),
            'phone_secondary' => null,
            'email'           => $this->faker->optional()->safeEmail(),
            'address'         => $this->faker->optional()->address(),
            'country'         => $this->faker->country(),
            'notes'           => null,
            'is_active'       => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
