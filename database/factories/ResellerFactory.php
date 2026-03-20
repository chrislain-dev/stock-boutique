<?php

namespace Database\Factories;

use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResellerFactory extends Factory
{
    protected $model = Reseller::class;

    public function definition(): array
    {
        return [
            'name'      => $this->faker->name(),
            'phone'     => $this->faker->phoneNumber(),
            'email'     => $this->faker->optional()->safeEmail(),
            'address'   => $this->faker->optional()->address(),
            'solde_du'  => 0,
            'is_active' => true,
            'notes'     => null,
        ];
    }

    public function withDebt(int $amount = 50000): static
    {
        return $this->state(['solde_du' => $amount]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
