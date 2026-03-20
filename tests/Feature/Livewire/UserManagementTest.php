<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    // ─── Accès admin ──────────────────────────────────────────

    public function test_admin_can_access_users_page(): void
    {
        $this->actingAsAdmin();
        $this->get(route('users.index'))->assertStatus(200);
    }

    public function test_vendeur_cannot_access_users_page(): void
    {
        $this->actingAsVendeur();
        $this->get(route('users.index'))->assertStatus(403);
    }

    public function test_guest_cannot_access_users_page(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    // ─── Création utilisateur ─────────────────────────────────

    public function test_admin_can_create_new_user(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $user = User::factory()->create([
            'name'      => 'Nouveau Vendeur',
            'email'     => 'vendeur@boutique.bj',
            'role'      => UserRole::VENDEUR,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'vendeur@boutique.bj',
            'role'  => UserRole::VENDEUR->value,
        ]);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        User::factory()->create(['email' => 'existing@test.com']);
    }

    // ─── Activation / Désactivation ───────────────────────────

    public function test_admin_can_deactivate_user(): void
    {
        $admin  = $this->createAdmin();
        $vendeur = $this->createVendeur();
        $this->actingAs($admin);

        $vendeur->update(['is_active' => false]);

        $this->assertFalse($vendeur->fresh()->is_active);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur(['is_active' => false]);
        $this->actingAs($admin);

        $vendeur->update(['is_active' => true]);

        $this->assertTrue($vendeur->fresh()->is_active);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    // ─── Changement de rôle ───────────────────────────────────

    public function test_admin_can_change_user_role(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();
        $this->actingAs($admin);

        $vendeur->update(['role' => UserRole::ADMIN]);

        $this->assertEquals(UserRole::ADMIN, $vendeur->fresh()->role);
    }

    // ─── Suppression ──────────────────────────────────────────

    public function test_admin_can_soft_delete_user(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();
        $this->actingAs($admin);

        $vendeur->delete();

        $this->assertSoftDeleted($vendeur);
    }

    public function test_deleted_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $user->delete();

        $response = $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function test_active_scope_excludes_soft_deleted_users(): void
    {
        $activeUser  = User::factory()->create(['is_active' => true]);
        $deletedUser = User::factory()->create(['is_active' => true]);
        $deletedUser->delete();

        $active = User::active()->get();

        $this->assertTrue($active->contains($activeUser));
        $this->assertFalse($active->contains($deletedUser));
    }

    // ─── Permissions ──────────────────────────────────────────

    public function test_only_admin_can_see_purchase_prices(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();

        $this->assertTrue($admin->hasPermission('see_purchase_price'));
        $this->assertFalse($vendeur->hasPermission('see_purchase_price'));
    }

    public function test_only_admin_can_see_profits(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();

        $this->assertTrue($admin->hasPermission('see_profit'));
        $this->assertFalse($vendeur->hasPermission('see_profit'));
    }

    public function test_only_admin_can_adjust_stock(): void
    {
        $admin   = $this->createAdmin();
        $vendeur = $this->createVendeur();

        $this->assertTrue($admin->hasPermission('adjust_stock'));
        $this->assertFalse($vendeur->hasPermission('adjust_stock'));
    }
}
