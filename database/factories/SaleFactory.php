<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $total  = $this->faker->numberBetween(10000, 500000);
        $paid   = $total; // fully paid by default

        return [
            'reference'      => Sale::generateReference(),
            'customer_type'  => 'client',
            'reseller_id'    => null,
            'customer_name'  => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'total_amount'   => $total,
            'paid_amount'    => $paid,
            'payment_status' => PaymentStatus::PAID,
            'sale_status'    => 'completed',
            'is_trade_in'    => false,
            'trade_in_product_id' => null,
            'trade_in_value' => null,
            'trade_in_notes' => null,
            'due_date'       => null,
            'notes'          => null,
            'created_by'     => User::factory(),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_amount'    => 0,
            'payment_status' => PaymentStatus::UNPAID,
        ]);
    }

    public function partial(float $ratio = 0.5): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_amount'    => (int) ($attrs['total_amount'] * $ratio),
            'payment_status' => PaymentStatus::PARTIAL,
        ]);
    }

    public function forReseller(): static
    {
        return $this->state([
            'customer_type' => 'reseller',
            'reseller_id'   => Reseller::factory(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date'       => now()->subDays(5),
            'paid_amount'    => 0,
            'payment_status' => PaymentStatus::UNPAID,
        ]);
    }

    public function withTradeIn(): static
    {
        return $this->state([
            'is_trade_in'    => true,
            'trade_in_value' => $this->faker->numberBetween(5000, 50000),
            'trade_in_notes' => 'Reprise appareil client',
        ]);
    }
}
