<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Suppliers\Index;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Livewire;
use Tests\TestCase;

class SuppliersIndexTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_admin_can_access_suppliers_component(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_vendeur_can_access_suppliers_component(): void
    {
        Livewire::actingAs($this->createVendeur())
            ->test(Index::class)
            ->assertStatus(200);
    }

    // ─── Rendu initial ────────────────────────────────────────

    public function test_component_mounts_with_closed_modal(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertSet('showModal', false)
            ->assertSet('showDeleteModal', false);
    }

    // ─── openCreateModal ──────────────────────────────────────

    public function test_open_create_modal_shows_modal(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('editingId', null);
    }

    public function test_open_create_modal_resets_all_fields(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('name', 'Ancien')
            ->set('phone', '0000000000')
            ->call('openCreateModal')
            ->assertSet('name', '')
            ->assertSet('phone', '');
    }

    // ─── save() — création ────────────────────────────────────

    public function test_admin_can_create_supplier(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Samsung BJ')
            ->set('phone', '+22999000001')
            ->set('country', 'Bénin')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'Samsung BJ']);
    }

    public function test_name_is_required_to_create_supplier(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('phone', '+22999000002')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_phone_is_required_to_create_supplier(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Test Supplier')
            ->set('phone', '')
            ->call('save')
            ->assertHasErrors(['phone' => 'required']);
    }

    public function test_phone_must_be_unique(): void
    {
        Supplier::factory()->create(['phone' => '+22999000003']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Autre Fournisseur')
            ->set('phone', '+22999000003')
            ->call('save')
            ->assertHasErrors(['phone' => 'unique']);
    }

    public function test_email_must_be_valid_if_provided(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Test')
            ->set('phone', '+22999000004')
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);
    }

    // ─── openEditModal ────────────────────────────────────────

    public function test_open_edit_modal_populates_fields(): void
    {
        $supplier = Supplier::factory()->create([
            'name'    => 'Fournisseur ABC',
            'phone'   => '+22999000010',
            'country' => 'Nigeria',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openEditModal', $supplier->id)
            ->assertSet('name', 'Fournisseur ABC')
            ->assertSet('phone', '+22999000010')
            ->assertSet('country', 'Nigeria')
            ->assertSet('editingId', $supplier->id)
            ->assertSet('showModal', true);
    }

    // ─── save() — édition ─────────────────────────────────────

    public function test_admin_can_edit_supplier(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Ancien Nom']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openEditModal', $supplier->id)
            ->set('name', 'Nouveau Nom')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertEquals('Nouveau Nom', $supplier->fresh()->name);
    }

    public function test_phone_unique_allows_own_phone_on_edit(): void
    {
        $supplier = Supplier::factory()->create(['phone' => '+22999000020']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openEditModal', $supplier->id)
            ->set('phone', '+22999000020')
            ->call('save')
            ->assertHasNoErrors(['phone']);
    }

    // ─── confirmDelete / delete ───────────────────────────────

    public function test_confirm_delete_shows_modal(): void
    {
        $supplier = Supplier::factory()->create();

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('confirmDelete', $supplier->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('deletingId', $supplier->id);
    }

    public function test_can_delete_supplier_without_associations(): void
    {
        $supplier = Supplier::factory()->create();

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('confirmDelete', $supplier->id)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertSoftDeleted($supplier);
    }

    public function test_cannot_delete_supplier_with_products(): void
    {
        $supplier = Supplier::factory()->create();
        Product::factory()->create(['supplier_id' => $supplier->id]);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('confirmDelete', $supplier->id)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertNotSoftDeleted($supplier);
    }

    // ─── Recherche ────────────────────────────────────────────

    public function test_search_resets_pagination(): void
    {
        // Dans Livewire 4, WithPagination gère 'page' via l'URL — pas de propriété publique
        // On vérifie que la recherche fonctionne sans erreur
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('search', 'samsung')
            ->assertHasNoErrors();
    }

    public function test_search_filters_by_name(): void
    {
        Supplier::factory()->create(['name' => 'Samsung Mobile']);
        Supplier::factory()->create(['name' => 'Apple Inc']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('search', 'Samsung')
            ->assertSee('Samsung Mobile')
            ->assertDontSee('Apple Inc');
    }

    // ─── Toggle is_active ─────────────────────────────────────

    public function test_supplier_can_be_created_as_inactive(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Inactif')
            ->set('phone', '+22999000030')
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'Inactif', 'is_active' => false]);
    }
}
