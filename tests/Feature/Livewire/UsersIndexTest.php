<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserRole;
use App\Livewire\Users\Index;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class UsersIndexTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_admin_can_access_users_component(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_vendeur_gets_403_on_mount(): void
    {
        $vendeur = $this->createVendeur();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::actingAs($vendeur)->test(Index::class);
    }

    // ─── Rendu initial ────────────────────────────────────────

    public function test_component_mounts_with_empty_form(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSet('showModal', false)
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('password', '');
    }

    // ─── openCreate ───────────────────────────────────────────

    public function test_open_create_shows_modal(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->assertSet('editingId', null);
    }

    public function test_open_create_resets_form_fields(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('name', 'ancien nom')
            ->call('openCreate')
            ->assertSet('name', '')
            ->assertSet('email', '');
    }

    // ─── save() — création ────────────────────────────────────

    public function test_admin_can_create_new_user(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Nouveau Vendeur')
            ->set('email', 'nouveau@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nouveau@test.com']);
    }

    public function test_name_is_required_on_create(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', '')
            ->set('email', 'test@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_email_is_required_on_create(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test User')
            ->set('email', '')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_email_must_be_unique_on_create(): void
    {
        $admin    = $this->createAdmin();
        $existing = User::factory()->create(['email' => 'taken@test.com']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('email', 'taken@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_password_is_required_on_create(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', '')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['password' => 'required']);
    }

    public function test_password_must_be_confirmed_on_create(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['password' => 'confirmed']);
    }

    public function test_password_must_be_at_least_8_chars(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', '1234567')
            ->set('password_confirmation', '1234567')
            ->set('role', UserRole::VENDEUR->value)
            ->call('save')
            ->assertHasErrors(['password' => 'min']);
    }

    public function test_role_is_required_on_create(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openCreate')
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', '')
            ->call('save')
            ->assertHasErrors(['role' => 'required']);
    }

    // ─── openEdit ─────────────────────────────────────────────

    public function test_open_edit_populates_form_with_user_data(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create([
            'name'  => 'Jean Dupont',
            'email' => 'jean@test.com',
            'role'  => UserRole::VENDEUR,
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openEdit', $target->id)
            ->assertSet('name', 'Jean Dupont')
            ->assertSet('email', 'jean@test.com')
            ->assertSet('editingId', $target->id)
            ->assertSet('showModal', true);
    }

    // ─── save() — édition ─────────────────────────────────────

    public function test_admin_can_edit_existing_user(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create(['name' => 'Ancien Nom']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openEdit', $target->id)
            ->set('name', 'Nouveau Nom')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertEquals('Nouveau Nom', $target->fresh()->name);
    }

    public function test_password_is_optional_on_edit(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openEdit', $target->id)
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('save')
            ->assertHasNoErrors(['password']);
    }

    public function test_email_unique_allows_own_email_on_edit(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create(['email' => 'own@test.com']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('openEdit', $target->id)
            ->set('email', 'own@test.com') // même email
            ->call('save')
            ->assertHasNoErrors(['email']);
    }

    // ─── toggleActive ─────────────────────────────────────────

    public function test_admin_can_deactivate_user(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('toggleActive', $target->id);

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create(['is_active' => false]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('toggleActive', $target->id);

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('toggleActive', $admin->id)
            ->assertDispatched('toast'); // Mary Toast error

        $this->assertTrue($admin->fresh()->is_active);
    }

    // ─── confirmDelete / delete ───────────────────────────────

    public function test_confirm_delete_shows_modal(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('confirmDelete', $target->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('deletingId', $target->id);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin  = $this->createAdmin();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('confirmDelete', $target->id)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertSoftDeleted($target);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('confirmDelete', $admin->id)
            ->call('delete');

        $this->assertNotSoftDeleted($admin);
    }

    // ─── Recherche ────────────────────────────────────────────

    public function test_search_resets_page(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('search', 'jean')
            ->assertSet('page', 1);
    }

    // ─── Filtre rôle ──────────────────────────────────────────

    public function test_role_filter_works(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur(['name' => 'Vendeur Test']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('roleFilter', UserRole::VENDEUR->value)
            ->assertSee('Vendeur Test');
    }
}
