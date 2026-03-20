<?php

namespace Tests\Feature\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Tests\TestCase;

class SaleFlowTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_guest_cannot_access_sales_page(): void
    {
        $this->withExceptionHandling();
        $this->get(route('sales.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_sales_page(): void
    {
        $this->actingAsAdmin();
        $this->get(route('sales.index'))->assertStatus(200);
    }

    public function test_vendeur_can_access_sales_page(): void
    {
        $this->actingAsVendeur();
        $this->get(route('sales.index'))->assertStatus(200);
    }

    // ─── Création vente ───────────────────────────────────────

    public function test_sale_is_created_with_correct_reference(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);

        $this->assertNotEmpty($sale->reference);
        $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{5}$/', $sale->reference);
    }

    public function test_paid_sale_has_paid_status(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create([
            'total_amount' => 100000,
            'paid_amount'  => 100000,
            'created_by'   => $user->id,
        ]);

        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);
    }

    public function test_partial_payment_has_partial_status(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->partial()->create(['created_by' => $user->id]);

        $this->assertEquals(PaymentStatus::PARTIAL, $sale->payment_status);
    }

    public function test_unpaid_sale_has_unpaid_status(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->unpaid()->create(['created_by' => $user->id]);

        $this->assertEquals(PaymentStatus::UNPAID, $sale->payment_status);
    }

    // ─── Validation montants ──────────────────────────────────

    public function test_paid_amount_cannot_exceed_total_amount(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create([
            'total_amount' => 100000,
            'paid_amount'  => 100000,
            'created_by'   => $user->id,
        ]);

        $this->expectException(\Exception::class);

        $sale->update(['paid_amount' => 200000]);
    }

    public function test_paid_amount_cannot_be_negative(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);

        $this->expectException(\Exception::class);

        $sale->update(['paid_amount' => -1]);
    }

    public function test_total_amount_must_be_positive(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $this->expectException(\Exception::class);

        Sale::factory()->create([
            'total_amount' => 0,
            'paid_amount'  => 0,
            'created_by'   => $user->id,
        ]);
    }

    // ─── Vente revendeur ──────────────────────────────────────

    public function test_reseller_sale_is_linked_to_reseller(): void
    {
        $user     = User::factory()->create(['is_active' => true]);
        $reseller = Reseller::factory()->create();
        $this->actingAs($user);

        $sale = Sale::factory()->forReseller()->create([
            'reseller_id' => $reseller->id,
            'created_by'  => $user->id,
        ]);

        $this->assertEquals($reseller->id, $sale->reseller_id);
        $this->assertNotNull($sale->reseller);
    }

    // ─── Trade-in ─────────────────────────────────────────────

    public function test_trade_in_sale_has_trade_in_flag(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->withTradeIn()->create(['created_by' => $user->id]);

        $this->assertTrue($sale->is_trade_in);
        $this->assertGreaterThan(0, $sale->trade_in_value);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function test_overdue_scope_returns_unpaid_past_due_date(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        Sale::factory()->overdue()->create(['created_by' => $user->id]);
        Sale::factory()->create(['created_by' => $user->id]);

        $this->assertCount(1, Sale::overdue()->get());
    }

    public function test_this_month_scope_returns_only_current_month(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        Sale::factory()->create(['created_by' => $user->id]);
        Sale::factory()->create(['created_by' => $user->id, 'created_at' => now()->subMonths(2)]);

        $this->assertCount(1, Sale::thisMonth()->get());
    }

    // ─── Relations ────────────────────────────────────────────

    public function test_sale_has_many_items(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);
        SaleItem::factory()->count(3)->create(['sale_id' => $sale->id]);

        $this->assertCount(3, $sale->items);
    }

    public function test_sale_belongs_to_created_by_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);

        $this->assertEquals($user->id, $sale->createdBy->id);
    }

    // ─── Soft delete ──────────────────────────────────────────

    public function test_sale_can_be_soft_deleted(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create(['created_by' => $user->id]);
        $sale->delete();

        $this->assertSoftDeleted($sale);
    }

    // ─── Remaining amount ─────────────────────────────────────

    public function test_remaining_amount_updates_after_payment(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $sale = Sale::factory()->create([
            'total_amount' => 100000,
            'paid_amount'  => 40000,
            'created_by'   => $user->id,
        ]);

        $this->assertEquals(60000, $sale->remaining_amount);

        $sale->update(['paid_amount' => 100000]);
        $this->assertEquals(0, $sale->fresh()->remaining_amount);
    }
}
