<?php

namespace Tests\Unit;

use App\Models\Reseller;
use App\Models\Sale;
use Tests\TestCase;

class SaleBusinessRulesTest extends TestCase
{
    /**
     * Test that paid amount cannot exceed total amount.
     */
    public function test_paid_amount_cannot_exceed_total(): void
    {
        $reseller = Reseller::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ne peut pas dépasser');

        Sale::create([
            'customer_type' => 'reseller',
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 2000,
            'payment_status' => 'unpaid',
            'sale_status' => 'completed',
            'created_by' => $this->signInAdmin()->id,
        ]);
    }

    /**
     * Test that paid amount must be non-negative.
     */
    public function test_paid_amount_must_be_non_negative(): void
    {
        $reseller = Reseller::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('must be positive or zero');

        Sale::create([
            'customer_type' => 'reseller',
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => -100,
            'payment_status' => 'unpaid',
            'sale_status' => 'completed',
            'created_by' => $this->signInAdmin()->id,
        ]);
    }

    /**
     * Test that total amount must be positive.
     */
    public function test_total_amount_must_be_positive(): void
    {
        $this->signInAdmin();
        $reseller = Reseller::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('must be positive');

        Sale::create([
            'customer_type' => 'reseller',
            'reseller_id' => $reseller->id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'sale_status' => 'completed',
            'created_by' => $this->signInAdmin()->id,
        ]);
    }

    /**
     * Test that valid sale is created successfully.
     */
    public function test_valid_sale_is_created(): void
    {
        $admin = $this->signInAdmin();
        $reseller = Reseller::factory()->create();

        $sale = Sale::create([
            'customer_type' => 'reseller',
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 500,
            'payment_status' => 'unpaid',
            'sale_status' => 'completed',
            'created_by' => $admin->id,
        ]);

        $this->assertNotNull($sale->id);
        $this->assertEquals(1000, $sale->total_amount);
        $this->assertEquals(500, $sale->paid_amount);
    }
}
