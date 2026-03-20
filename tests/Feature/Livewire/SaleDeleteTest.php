<?php

namespace Tests\Feature\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\ProductState;
use App\Livewire\Sales\Show;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class SaleDeleteTest extends TestCase
{
    // ─── openDeleteModal ──────────────────────────────────────

    public function test_admin_can_open_delete_modal(): void
    {
        $admin = $this->createAdmin();
        $sale  = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->call('openDeleteModal')
            ->assertSet('showDeleteModal', true)
            ->assertSet('delete_password', '')
            ->assertSet('delete_reason', '')
            ->assertSet('delete_password_error', false);
    }

    public function test_vendeur_cannot_open_delete_modal(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();
        $sale    = Sale::factory()->create(['created_by' => $admin->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::actingAs($vendeur)
            ->test(Show::class, ['sale' => $sale])
            ->call('openDeleteModal');
    }

    // ─── Validation champs ────────────────────────────────────

    public function test_delete_reason_is_required(): void
    {
        $admin = $this->createAdmin();
        $sale  = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', '')
            ->set('delete_password', 'password')
            ->call('deleteSale')
            ->assertHasErrors(['delete_reason' => 'required']);
    }

    public function test_delete_reason_must_be_at_least_10_chars(): void
    {
        $admin = $this->createAdmin();
        $sale  = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Trop court')
            ->set('delete_password', 'password')
            ->call('deleteSale')
            ->assertHasErrors(['delete_reason' => 'min']);
    }

    public function test_delete_password_is_required(): void
    {
        $admin = $this->createAdmin();
        $sale  = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Motif suffisamment long pour passer la validation')
            ->set('delete_password', '')
            ->call('deleteSale')
            ->assertHasErrors(['delete_password' => 'required']);
    }

    // ─── Mot de passe incorrect ───────────────────────────────

    public function test_wrong_password_sets_error_flag(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Motif suffisamment long pour la validation')
            ->set('delete_password', 'wrong_password')
            ->call('deleteSale')
            ->assertSet('delete_password_error', true)
            ->assertHasErrors(['delete_password']);
    }

    public function test_wrong_password_does_not_delete_sale(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Motif suffisamment long pour la validation')
            ->set('delete_password', 'wrong_password')
            ->call('deleteSale');

        $this->assertNotSoftDeleted($sale);
    }

    // ─── Suppression réussie ──────────────────────────────────

    public function test_correct_password_soft_deletes_sale(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Double saisie par erreur de caisse')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        $this->assertSoftDeleted($sale);
    }

    public function test_sale_status_is_set_to_cancelled_before_delete(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create([
            'created_by'  => $admin->id,
            'sale_status' => 'completed',
        ]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Double saisie par erreur de caisse')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        $this->assertEquals('cancelled', Sale::withTrashed()->find($sale->id)->sale_status);
    }

    public function test_deletion_redirects_to_sales_index(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Double saisie par erreur de caisse')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale')
            ->assertRedirect(route('sales.index'));
    }

    // ─── Remise en stock des produits ─────────────────────────

    public function test_sold_products_are_restored_to_available_on_delete(): void
    {
        $admin        = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $productModel = ProductModel::factory()->create();
        $product      = Product::factory()->create([
            'product_model_id' => $productModel->id,
            'state'            => 'sold',
            'location'         => 'client',
            'created_by'       => $admin->id,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);
        SaleItem::factory()->create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Remboursement client demandé')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        $this->assertEquals('available', $product->fresh()->state->value);
        $this->assertEquals('store', $product->fresh()->location->value);
    }

    public function test_stock_movement_is_created_for_each_restored_product(): void
    {
        $admin        = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $productModel = ProductModel::factory()->create();
        $product      = Product::factory()->create([
            'product_model_id' => $productModel->id,
            'state'            => 'sold',
            'location'         => 'client',
            'created_by'       => $admin->id,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);
        SaleItem::factory()->create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Remboursement client demandé suite retour')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        $this->assertDatabaseHas('stock_movements', [
            'product_id'    => $product->id,
            'type'          => 'client_return',
            'location_from' => 'client',
            'location_to'   => 'store',
        ]);
    }

    public function test_non_sold_products_are_not_touched_on_delete(): void
    {
        $admin        = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $productModel = ProductModel::factory()->create();

        // Produit déjà retourné fournisseur — ne doit pas être touché
        $product = Product::factory()->create([
            'product_model_id' => $productModel->id,
            'state'            => 'returned_to_supplier',
            'location'         => 'supplier_return',
            'created_by'       => $admin->id,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);
        SaleItem::factory()->create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Annulation administrative exceptionnelle')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        // L'état ne doit pas avoir changé
        $this->assertEquals('returned_to_supplier', $product->fresh()->state->value);
    }

    // ─── Log d'activité ───────────────────────────────────────

    public function test_activity_log_is_created_on_deletion(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Double saisie par erreur de caisse')
            ->set('delete_password', 'correct_password')
            ->call('deleteSale');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action'  => 'delete',
        ]);
    }

    // ─── Vendeur bloqué ───────────────────────────────────────

    public function test_vendeur_cannot_delete_sale(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = User::factory()->create([
            'role'      => 'vendeur',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);
        $sale = Sale::factory()->create(['created_by' => $admin->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::actingAs($vendeur)
            ->test(Show::class, ['sale' => $sale])
            ->set('delete_reason', 'Tentative vendeur non autorisée')
            ->set('delete_password', 'password123')
            ->call('deleteSale');
    }
}
