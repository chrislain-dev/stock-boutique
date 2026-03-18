<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Product;
use App\Models\Reseller;
use Tests\TestCase;

class SaleValidationTest extends TestCase
{
    /**
     * Test that paid amount cannot exceed total amount.
     */
    public function test_paid_amount_cannot_exceed_total(): void
    {
        $this->signInAdmin();

        $product = Product::factory()->create();
        $reseller = Reseller::factory()->create();

        $response = $this->post('/api/sales', [
            'reseller_id' => $reseller->id,
            'sale_items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'paid_amount' => 1000, // Way more than product price
            'payment_method' => PaymentMethod::CASH->value,
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        // This should fail validation in a real scenario
        // The actual check happens in controller or Livewire
        $response->assertSessionHasErrors('paid_amount');
    }

    /**
     * Test that sale requires at least one item.
     */
    public function test_sale_requires_items(): void
    {
        $this->signInAdmin();

        $reseller = Reseller::factory()->create();

        $response = $this->post('/api/sales', [
            'reseller_id' => $reseller->id,
            'sale_items' => [],
            'paid_amount' => 0,
            'payment_method' => PaymentMethod::CASH->value,
            'payment_status' => PaymentStatus::UNPAID->value,
        ]);

        $response->assertSessionHasErrors('sale_items');
    }

    /**
     * Test that product must exist.
     */
    public function test_product_must_exist(): void
    {
        $this->signInAdmin();

        $reseller = Reseller::factory()->create();

        $response = $this->post('/api/sales', [
            'reseller_id' => $reseller->id,
            'sale_items' => [
                [
                    'product_id' => 99999,
                    'quantity' => 1,
                ],
            ],
            'paid_amount' => 0,
            'payment_method' => PaymentMethod::CASH->value,
            'payment_status' => PaymentStatus::UNPAID->value,
        ]);

        $response->assertSessionHasErrors('sale_items.0.product_id');
    }
}
