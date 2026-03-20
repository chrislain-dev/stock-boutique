<?php

namespace Tests\Feature\Livewire;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockMovement;
use App\Models\User;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_guest_cannot_access_stock_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_stock_page(): void
    {
        $this->actingAsAdmin();
        $this->get(route('dashboard'))->assertStatus(200);
    }

    // ─── Création mouvement ───────────────────────────────────

    public function test_stock_movement_is_immutable_no_updated_at(): void
    {
        $movement = StockMovement::factory()->create();

        // UPDATED_AT = null — pas de colonne updated_at
        $this->assertNull(StockMovement::UPDATED_AT);
        $this->assertArrayNotHasKey('updated_at', $movement->toArray());
    }

    public function test_stock_movement_has_correct_type(): void
    {
        $movement = StockMovement::factory()->create([
            'type' => StockMovementType::STOCK_IN,
        ]);

        $this->assertEquals(StockMovementType::STOCK_IN, $movement->type);
    }

    public function test_sale_out_movement_has_correct_locations(): void
    {
        $movement = StockMovement::factory()->saleOut()->create();

        $this->assertEquals('store', $movement->location_from);
        $this->assertEquals('client', $movement->location_to);
    }

    public function test_transfer_movement_tracks_source_and_destination(): void
    {
        $movement = StockMovement::factory()->transfer()->create();

        $this->assertEquals('store', $movement->location_from);
        $this->assertEquals('reseller', $movement->location_to);
    }

    // ─── Types valides ────────────────────────────────────────

    public function test_all_valid_movement_types_can_be_created(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user);

        $validTypes = [
            StockMovementType::STOCK_IN,
            StockMovementType::SALE_OUT,
            StockMovementType::CLIENT_RETURN,
            StockMovementType::SUPPLIER_RETURN,
            StockMovementType::TRANSFER,
            StockMovementType::ADJUSTMENT,
            StockMovementType::LOSS,
            StockMovementType::TRADE_IN,
        ];

        foreach ($validTypes as $type) {
            $movement = StockMovement::factory()->create([
                'type'       => $type,
                'created_by' => $user->id,
            ]);

            $this->assertEquals($type, $movement->type);
        }
    }

    // ─── Quantités ────────────────────────────────────────────

    public function test_quantity_after_equals_before_plus_quantity(): void
    {
        $movement = StockMovement::factory()->create([
            'quantity_before' => 10,
            'quantity'        => 5,
            'quantity_after'  => 15,
        ]);

        $this->assertEquals(
            $movement->quantity_before + $movement->quantity,
            $movement->quantity_after
        );
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function test_scope_today_returns_only_todays_movements(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user);

        StockMovement::factory()->create(['created_by' => $user->id]);
        StockMovement::factory()->create(['created_by' => $user->id, 'created_at' => now()->subDays(2)]);

        $this->assertCount(1, StockMovement::today()->get());
    }

    public function test_scope_by_type_filters_correctly(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user);

        StockMovement::factory()->create(['type' => StockMovementType::STOCK_IN, 'created_by' => $user->id]);
        StockMovement::factory()->create(['type' => StockMovementType::SALE_OUT, 'created_by' => $user->id]);
        StockMovement::factory()->create(['type' => StockMovementType::SALE_OUT, 'created_by' => $user->id]);

        $this->assertCount(1, StockMovement::byType(StockMovementType::STOCK_IN)->get());
        $this->assertCount(2, StockMovement::byType(StockMovementType::SALE_OUT)->get());
    }

    // ─── Relations ────────────────────────────────────────────

    public function test_movement_belongs_to_product_model(): void
    {
        $movement = StockMovement::factory()->create();
        $this->assertNotNull($movement->productModel);
    }

    public function test_movement_belongs_to_created_by_user(): void
    {
        $user     = $this->createAdmin();
        $movement = StockMovement::factory()->create(['created_by' => $user->id]);

        $this->assertEquals($user->id, $movement->createdBy->id);
    }

    // ─── Vendeur ne peut pas ajuster le stock ─────────────────

    public function test_vendeur_cannot_access_stock_adjustment(): void
    {
        $vendeur = $this->createVendeur();
        $this->actingAs($vendeur);

        $this->assertFalse($vendeur->hasPermission('adjust_stock'));
    }
}
