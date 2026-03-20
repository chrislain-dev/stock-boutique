<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    // ─── Rôles ────────────────────────────────────────────────

    public function test_isAdmin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($user->isAdmin());
    }

    public function test_isAdmin_returns_false_for_vendeur(): void
    {
        $user = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertFalse($user->isAdmin());
    }

    public function test_isVendeur_returns_true_for_vendeur_role(): void
    {
        $user = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertTrue($user->isVendeur());
    }

    public function test_isVendeur_returns_false_for_admin(): void
    {
        $user = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertFalse($user->isVendeur());
    }

    // ─── Permissions admin ─────────────────────────────────────

    public function test_admin_can_see_purchase_price(): void
    {
        $admin = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($admin->hasPermission('see_purchase_price'));
    }

    public function test_admin_can_see_profit(): void
    {
        $admin = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($admin->hasPermission('see_profit'));
    }

    public function test_admin_can_cancel_sale(): void
    {
        $admin = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($admin->hasPermission('cancel_sale'));
    }

    public function test_admin_can_adjust_stock(): void
    {
        $admin = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($admin->hasPermission('adjust_stock'));
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertTrue($admin->hasPermission('manage_users'));
    }

    // ─── Permissions vendeur ───────────────────────────────────

    public function test_vendeur_cannot_see_purchase_price(): void
    {
        $vendeur = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertFalse($vendeur->hasPermission('see_purchase_price'));
    }

    public function test_vendeur_cannot_see_profit(): void
    {
        $vendeur = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertFalse($vendeur->hasPermission('see_profit'));
    }

    public function test_vendeur_cannot_cancel_sale(): void
    {
        $vendeur = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertFalse($vendeur->hasPermission('cancel_sale'));
    }

    public function test_vendeur_cannot_manage_users(): void
    {
        $vendeur = User::factory()->make(['role' => UserRole::VENDEUR]);
        $this->assertFalse($vendeur->hasPermission('manage_users'));
    }

    public function test_unknown_permission_returns_false(): void
    {
        $user = User::factory()->make(['role' => UserRole::ADMIN]);
        $this->assertFalse($user->hasPermission('nonexistent_permission'));
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function test_scope_active_returns_only_active_users(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);

        $this->assertCount(1, User::active()->get());
    }

    public function test_scope_admins_returns_only_active_admins(): void
    {
        User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => false]);
        User::factory()->create(['role' => UserRole::VENDEUR, 'is_active' => true]);

        $this->assertCount(1, User::admins()->get());
    }

    public function test_scope_vendeurs_returns_only_vendeurs(): void
    {
        User::factory()->create(['role' => UserRole::VENDEUR]);
        User::factory()->create(['role' => UserRole::VENDEUR]);
        User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertCount(2, User::vendeurs()->get());
    }

    // ─── Password hashé ───────────────────────────────────────

    public function test_password_is_hashed_on_creation(): void
    {
        $user = User::factory()->create(['password' => 'plain_text_password']);
        $this->assertNotEquals('plain_text_password', $user->password);
        $this->assertTrue(password_verify('plain_text_password', $user->password));
    }

    // ─── SoftDelete ───────────────────────────────────────────

    public function test_user_is_soft_deleted(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }
}
