<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Reseller;
use Tests\TestCase;

class PaymentBusinessRulesTest extends TestCase
{
    /**
     * Test that payment amount must be positive.
     */
    public function test_payment_amount_must_be_positive(): void
    {
        $admin = $this->signInAdmin();
        $reseller = Reseller::factory()->create();
        $sale = Sale::factory()->create([
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'created_by' => $admin->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('must be positive');

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => -100,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Test that total payments cannot exceed sale total_amount.
     */
    public function test_payment_cannot_exceed_sale_total(): void
    {
        $admin = $this->signInAdmin();
        $reseller = Reseller::factory()->create();
        $sale = Sale::factory()->create([
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'created_by' => $admin->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('dépasse');

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => 1500,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Test that valid payment is created successfully.
     */
    public function test_valid_payment_is_created(): void
    {
        $admin = $this->signInAdmin();
        $reseller = Reseller::factory()->create();
        $sale = Sale::factory()->create([
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'created_by' => $admin->id,
        ]);

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);

        $this->assertNotNull($payment->id);
        $this->assertEquals(500, $payment->amount);

        // Verify sale paid_amount is updated
        $this->assertEquals(500, $sale->refresh()->paid_amount);
    }

    /**
     * Test that multiple payments can be added.
     */
    public function test_multiple_payments_can_be_added(): void
    {
        $admin = $this->signInAdmin();
        $reseller = Reseller::factory()->create();
        $sale = Sale::factory()->create([
            'reseller_id' => $reseller->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'created_by' => $admin->id,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => 300,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => 700,
            'payment_method' => 'mobile_money',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals(1000, $sale->refresh()->paid_amount);
    }
}
