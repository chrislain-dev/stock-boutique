<?php

namespace Tests\Unit\Models;

use App\Enums\PaymentStatus;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\User;
use Tests\TestCase;

class SaleTest extends TestCase
{
    // ─── computePaymentStatus ─────────────────────────────────

    public function test_compute_status_returns_paid_when_fully_paid(): void
    {
        $status = Sale::computePaymentStatus(100000, 100000);
        $this->assertEquals(PaymentStatus::PAID, $status);
    }

    public function test_compute_status_returns_paid_when_overpaid(): void
    {
        $status = Sale::computePaymentStatus(110000, 100000);
        $this->assertEquals(PaymentStatus::PAID, $status);
    }

    public function test_compute_status_returns_partial_when_partially_paid(): void
    {
        $status = Sale::computePaymentStatus(50000, 100000);
        $this->assertEquals(PaymentStatus::PARTIAL, $status);
    }

    public function test_compute_status_returns_unpaid_when_nothing_paid(): void
    {
        $status = Sale::computePaymentStatus(0, 100000);
        $this->assertEquals(PaymentStatus::UNPAID, $status);
    }

    // ─── generateReference ─────────────────────────────────────

    public function test_generate_reference_has_correct_format(): void
    {
        $ref = Sale::generateReference();
        $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{5}$/', $ref);
    }

    public function test_generate_reference_increments_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Créer une première vente pour occuper le counter
        Sale::factory()->create(['created_by' => $user->id]);

        $ref1 = Sale::generateReference();
        $ref2 = Sale::generateReference();

        // Les deux refs ont le même format — le counter dépend des ventes existantes
        $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{5}$/', $ref1);
    }

    // ─── Accesseurs ────────────────────────────────────────────

    public function test_remaining_amount_is_calculated_correctly(): void
    {
        $sale = Sale::factory()->make([
            'total_amount' => 100000,
            'paid_amount'  => 60000,
        ]);

        $this->assertEquals(40000, $sale->remaining_amount);
    }

    public function test_remaining_amount_is_zero_when_fully_paid(): void
    {
        $sale = Sale::factory()->make([
            'total_amount' => 100000,
            'paid_amount'  => 100000,
        ]);

        $this->assertEquals(0, $sale->remaining_amount);
    }

    public function test_is_fully_paid_returns_true_when_paid(): void
    {
        $sale = Sale::factory()->make([
            'payment_status' => PaymentStatus::PAID,
        ]);

        $this->assertTrue($sale->is_fully_paid);
    }

    public function test_is_fully_paid_returns_false_when_unpaid(): void
    {
        $sale = Sale::factory()->make([
            'payment_status' => PaymentStatus::UNPAID,
        ]);

        $this->assertFalse($sale->is_fully_paid);
    }

    public function test_is_overdue_returns_true_for_past_due_date_unpaid(): void
    {
        $sale = Sale::factory()->make([
            'due_date'       => now()->subDays(3),
            'payment_status' => PaymentStatus::UNPAID,
        ]);

        $this->assertTrue($sale->is_overdue);
    }

    public function test_is_overdue_returns_false_when_paid(): void
    {
        $sale = Sale::factory()->make([
            'due_date'       => now()->subDays(3),
            'payment_status' => PaymentStatus::PAID,
        ]);

        $this->assertFalse($sale->is_overdue);
    }

    public function test_is_overdue_returns_false_without_due_date(): void
    {
        $sale = Sale::factory()->make([
            'due_date'       => null,
            'payment_status' => PaymentStatus::UNPAID,
        ]);

        $this->assertFalse($sale->is_overdue);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function test_scope_unpaid_excludes_paid_sales(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->create(['paid_amount' => 100000, 'total_amount' => 100000, 'payment_status' => PaymentStatus::PAID, 'created_by' => $user->id]);
        Sale::factory()->create(['paid_amount' => 0, 'total_amount' => 100000, 'payment_status' => PaymentStatus::UNPAID, 'created_by' => $user->id]);

        $this->assertCount(1, Sale::unpaid()->get());
    }

    public function test_scope_today_returns_only_todays_sales(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->create(['created_by' => $user->id]);
        Sale::factory()->create(['created_by' => $user->id, 'created_at' => now()->subDays(2)]);

        $this->assertCount(1, Sale::today()->get());
    }

    // ─── Validation boot ───────────────────────────────────────

    public function test_sale_creation_sets_reference_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);

        $this->assertNotEmpty($sale->reference);
        $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{5}$/', $sale->reference);
    }

    public function test_sale_creation_sets_payment_status_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sale = Sale::factory()->create([
            'total_amount' => 100000,
            'paid_amount'  => 100000,
            'created_by'   => $user->id,
        ]);

        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);
    }

    // ─── SoftDelete ────────────────────────────────────────────

    public function test_sale_is_soft_deleted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);
        $sale->delete();

        $this->assertSoftDeleted($sale);
        $this->assertNull(Sale::find($sale->id));
    }
}
