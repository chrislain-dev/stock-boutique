<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Livewire\SupplierReturns\Index;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ProductReturn;
use App\Models\Sale;
use App\Models\StockMovement;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierReturnsIndexTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_admin_can_access_supplier_returns(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_vendeur_can_access_supplier_returns(): void
    {
        Livewire::actingAs($this->createVendeur())
            ->test(Index::class)
            ->assertStatus(200);
    }

    // ─── openDeclareModal ─────────────────────────────────────

    public function test_open_declare_modal_sets_correct_state(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->assertSet('showDeclareModal', true)
            ->assertSet('declare_sale_id', $sale->id)
            ->assertSet('declare_product_id', $product->id)
            ->assertSet('declare_reason', '');
    }

    // ─── declareReturn ────────────────────────────────────────

    public function test_declare_return_creates_product_return_ticket(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['state' => 'sold', 'location' => 'client']);
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->set('declare_reason', 'Écran cassé après livraison')
            ->call('declareReturn')
            ->assertSet('showDeclareModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_returns', [
            'product_id' => $product->id,
            'sale_id'    => $sale->id,
            'status'     => 'pending',
        ]);
    }

    public function test_declare_return_changes_product_state_to_defective(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['state' => 'sold', 'location' => 'client']);
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->set('declare_reason', 'Batterie défaillante après 2 semaines')
            ->call('declareReturn');

        $this->assertEquals('defective', $product->fresh()->state->value);
        $this->assertEquals('supplier_return', $product->fresh()->location->value);
    }

    public function test_declare_return_creates_stock_movement(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['state' => 'sold', 'location' => 'client']);
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->set('declare_reason', 'Défaut constaté lors de la réception')
            ->call('declareReturn');

        $this->assertDatabaseHas('stock_movements', [
            'product_id'    => $product->id,
            'type'          => 'client_return',
            'location_from' => 'client',
            'location_to'   => 'supplier_return',
        ]);
    }

    public function test_declare_return_reason_is_required(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->set('declare_reason', '')
            ->call('declareReturn')
            ->assertHasErrors(['declare_reason' => 'required']);
    }

    public function test_declare_return_reason_must_be_at_least_5_chars(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openDeclareModal', $sale->id, $product->id)
            ->set('declare_reason', 'Mal')
            ->call('declareReturn')
            ->assertHasErrors(['declare_reason' => 'min']);
    }

    // ─── openSentModal / markAsSent ───────────────────────────

    public function test_open_sent_modal_sets_correct_state(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createPendingReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openSentModal', $productReturn->id)
            ->assertSet('showSentModal', true)
            ->assertSet('sentReturnId', $productReturn->id)
            ->assertSet('sent_notes', '');
    }

    public function test_mark_as_sent_updates_return_status(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createPendingReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openSentModal', $productReturn->id)
            ->set('sent_notes', 'Envoyé via DHL')
            ->call('markAsSent')
            ->assertSet('showSentModal', false);

        $this->assertEquals('sent_to_supplier', $productReturn->fresh()->status);
        $this->assertNotNull($productReturn->fresh()->sent_at);
    }

    public function test_mark_as_sent_changes_product_state_to_returned_to_supplier(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createPendingReturn($admin);
        $product       = $productReturn->product;

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openSentModal', $productReturn->id)
            ->call('markAsSent');

        $this->assertEquals('returned_to_supplier', $product->fresh()->state->value);
    }

    public function test_mark_as_sent_creates_supplier_return_movement(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createPendingReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openSentModal', $productReturn->id)
            ->call('markAsSent');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productReturn->product_id,
            'type'       => 'supplier_return',
        ]);
    }

    // ─── openReplaceModal / searchReplacement / receiveReplacement ────

    public function test_open_replace_modal_sets_correct_state(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->assertSet('showReplaceModal', true)
            ->assertSet('replaceReturnId', $productReturn->id)
            ->assertSet('replace_imei', '')
            ->assertSet('replace_product_id', null);
    }

    public function test_search_replacement_finds_available_product(): void
    {
        $admin           = $this->createAdmin();
        $productReturn   = $this->createSentReturn($admin);
        $replacement     = Product::factory()->create([
            'imei'  => '999888777666555',
            'state' => 'available',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->set('replace_imei', '999888777666555')
            ->call('searchReplacement')
            ->assertSet('replace_product_id', $replacement->id)
            ->assertSet('replace_search_error', '');
    }

    public function test_search_replacement_fails_if_product_not_available(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);

        Product::factory()->create([
            'imei'  => '888777666555444',
            'state' => 'sold',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->set('replace_imei', '888777666555444')
            ->call('searchReplacement')
            ->assertSet('replace_product_id', null)
            ->assertSet('replace_search_error', 'Produit non trouvé dans le stock disponible.');
    }

    public function test_receive_replacement_closes_return_ticket(): void
    {
        $admin           = $this->createAdmin();
        $productReturn   = $this->createSentReturn($admin);
        $replacement     = Product::factory()->create(['state' => 'available']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->set('replace_product_id', $replacement->id)
            ->call('receiveReplacement')
            ->assertSet('showReplaceModal', false);

        $this->assertEquals('replacement_received', $productReturn->fresh()->status);
        $this->assertEquals($replacement->id, $productReturn->fresh()->replacement_product_id);
    }

    public function test_receive_replacement_creates_stock_in_movement(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);
        $replacement   = Product::factory()->create(['state' => 'available']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->set('replace_product_id', $replacement->id)
            ->call('receiveReplacement');

        $this->assertDatabaseHas('stock_movements', [
            'product_id'  => $replacement->id,
            'type'        => 'stock_in',
            'location_to' => 'store',
        ]);
    }

    public function test_receive_replacement_requires_product_id(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->set('replace_product_id', null)
            ->call('receiveReplacement')
            ->assertHasErrors(['replace_product_id' => 'required']);
    }

    // ─── createReplacementProduct ─────────────────────────────

    public function test_create_replacement_product_validates_unique_imei(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);
        $existing      = Product::factory()->create(['imei' => '111111111111111']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->call('openCreateReplacementModal')
            ->set('new_product_model_id', $productReturn->product->product_model_id)
            ->set('new_product_imei', '111111111111111') // déjà existant
            ->call('createReplacementProduct')
            ->assertHasErrors(['new_product_imei' => 'unique']);
    }

    public function test_create_replacement_product_requires_model(): void
    {
        $admin         = $this->createAdmin();
        $productReturn = $this->createSentReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openReplaceModal', $productReturn->id)
            ->call('openCreateReplacementModal')
            ->set('new_product_model_id', '')
            ->set('new_product_imei', '222222222222222')
            ->call('createReplacementProduct')
            ->assertHasErrors(['new_product_model_id' => 'required']);
    }

    // ─── Filtre statut ────────────────────────────────────────

    public function test_status_filter_defaults_to_pending(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertSet('statusFilter', 'pending');
    }

    public function test_status_filter_all_shows_all_returns(): void
    {
        $admin = $this->createAdmin();
        $this->createPendingReturn($admin);
        $this->createSentReturn($admin);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('statusFilter', 'all')
            ->assertViewHas('returns', fn($returns) => $returns->total() === 2);
    }

    // ─── Helpers privés ───────────────────────────────────────

    private function createPendingReturn($admin): ProductReturn
    {
        $product = Product::factory()->create([
            'state'    => ProductState::DEFECTIVE->value,
            'location' => ProductLocation::SUPPLIER_RETURN->value,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        return ProductReturn::create([
            'product_id'  => $product->id,
            'sale_id'     => $sale->id,
            'reason'      => 'Défaut de fabrication constaté',
            'status'      => 'pending',
            'declared_by' => $admin->id,
        ]);
    }

    private function createSentReturn($admin): ProductReturn
    {
        $return = $this->createPendingReturn($admin);
        $return->update([
            'status'  => 'sent_to_supplier',
            'sent_at' => now(),
        ]);
        $return->product->update(['state' => 'returned_to_supplier']);

        return $return->fresh();
    }
}
