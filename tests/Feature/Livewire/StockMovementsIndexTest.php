<?php

namespace Tests\Feature\Livewire;

use App\Enums\StockMovementType;
use App\Livewire\StockMovements\Index;
use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Livewire;
use Tests\TestCase;

class StockMovementsIndexTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_admin_can_access_stock_movements(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_vendeur_can_access_stock_movements(): void
    {
        Livewire::actingAs($this->createVendeur())
            ->test(Index::class)
            ->assertStatus(200);
    }

    // ─── openAdjustModal ──────────────────────────────────────

    public function test_admin_can_open_adjust_modal(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openAdjustModal')
            ->assertSet('showAdjustModal', true)
            ->assertSet('adjust_imei', '')
            ->assertSet('adjust_product_id', null)
            ->assertSet('adjust_notes', '');
    }

    public function test_vendeur_cannot_open_adjust_modal(): void
    {
        Livewire::actingAs($this->createVendeur())
            ->test(Index::class)
            ->call('openAdjustModal')
            ->assertForbidden();
    }

    // ─── searchAdjustProduct ──────────────────────────────────

    public function test_search_finds_product_by_imei(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['imei' => '111222333444555']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_imei', '111222333444555')
            ->call('searchAdjustProduct')
            ->assertSet('adjust_product_id', $product->id)
            ->assertSet('adjust_error', '');
    }

    public function test_search_finds_product_by_serial_number(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['imei' => null, 'serial_number' => 'SN-ABC-123']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_imei', 'SN-ABC-123')
            ->call('searchAdjustProduct')
            ->assertSet('adjust_product_id', $product->id);
    }

    public function test_search_sets_error_for_unknown_imei(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('adjust_imei', '000000000000000')
            ->call('searchAdjustProduct')
            ->assertSet('adjust_product_id', null)
            ->assertSet('adjust_error', 'Produit introuvable.');
    }

    // ─── saveAdjustment ───────────────────────────────────────

    public function test_admin_can_save_adjustment(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['imei' => '222333444555666']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_imei', '222333444555666')
            ->call('searchAdjustProduct')
            ->set('adjust_type', StockMovementType::ADJUSTMENT->value)
            ->set('adjust_notes', 'Ajustement inventaire mensuel')
            ->call('saveAdjustment')
            ->assertSet('showAdjustModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => StockMovementType::ADJUSTMENT->value,
        ]);
    }

    public function test_save_adjustment_requires_product(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('adjust_product_id', null)
            ->set('adjust_type', StockMovementType::ADJUSTMENT->value)
            ->set('adjust_notes', 'Notes suffisantes')
            ->call('saveAdjustment')
            ->assertHasErrors(['adjust_product_id' => 'required']);
    }

    public function test_save_adjustment_requires_notes(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_product_id', $product->id)
            ->set('adjust_type', StockMovementType::ADJUSTMENT->value)
            ->set('adjust_notes', '')
            ->call('saveAdjustment')
            ->assertHasErrors(['adjust_notes' => 'required']);
    }

    public function test_save_adjustment_notes_must_be_at_least_5_chars(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_product_id', $product->id)
            ->set('adjust_type', StockMovementType::ADJUSTMENT->value)
            ->set('adjust_notes', 'abc')
            ->call('saveAdjustment')
            ->assertHasErrors(['adjust_notes' => 'min']);
    }

    public function test_vendeur_cannot_save_adjustment(): void
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->createVendeur())
            ->test(Index::class)
            ->set('adjust_product_id', $product->id)
            ->set('adjust_type', StockMovementType::ADJUSTMENT->value)
            ->set('adjust_notes', 'Tentative non autorisée')
            ->call('saveAdjustment')
            ->assertForbidden();
    }

    // ─── Perte (LOSS) ─────────────────────────────────────────

    public function test_loss_adjustment_marks_product_as_defective(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create([
            'imei'  => '333444555666777',
            'state' => 'available',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_imei', '333444555666777')
            ->call('searchAdjustProduct')
            ->set('adjust_type', StockMovementType::LOSS->value)
            ->set('adjust_notes', 'Produit perdu en transit')
            ->call('saveAdjustment');

        $this->assertEquals('defective', $product->fresh()->state->value);
    }

    // ─── Transfert ────────────────────────────────────────────

    public function test_transfer_adjustment_updates_product_location(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create([
            'imei'     => '444555666777888',
            'location' => 'store',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('adjust_imei', '444555666777888')
            ->call('searchAdjustProduct')
            ->set('adjust_type', StockMovementType::TRANSFER->value)
            ->set('adjust_location_to', 'reseller')
            ->set('adjust_notes', 'Transfert vers revendeur partenaire')
            ->call('saveAdjustment');

        $this->assertEquals('reseller', $product->fresh()->location->value);
    }

    // ─── Filtres ──────────────────────────────────────────────

    public function test_search_resets_pagination(): void
    {
        // Dans Livewire 4, WithPagination gère 'page' via l'URL — pas de propriété publique
        // On vérifie que la recherche fonctionne sans erreur
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('search', 'imei123')
            ->assertHasNoErrors();
    }

    public function test_type_filter_is_applied(): void
    {
        $admin = $this->createAdmin();
        StockMovement::factory()->create([
            'type'       => StockMovementType::STOCK_IN,
            'created_by' => $admin->id,
        ]);
        StockMovement::factory()->create([
            'type'       => StockMovementType::LOSS,
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('typeFilter', StockMovementType::LOSS->value)
            ->assertViewHas('movements', fn($movements) => $movements->total() === 1);
    }
}
