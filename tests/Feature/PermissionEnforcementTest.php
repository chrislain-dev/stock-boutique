<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    /**
     * Test that admin can access admin endpoints.
     */
    public function test_admin_can_access_admin_endpoints(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($admin);

        // Assuming dashboard requires auth
        $response = $this->get('/dashboard');

        // Should not be 403
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /**
     * Test that vendeur cannot adjust stock.
     */
    public function test_vendeur_cannot_adjust_stock(): void
    {
        $vendeur = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($vendeur->hasPermission('adjust_stock'));
    }

    /**
     * Test that admin can adjust stock.
     */
    public function test_admin_can_adjust_stock(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->hasPermission('adjust_stock'));
    }

    /**
     * Test that vendeur cannot see purchase price.
     */
    public function test_vendeur_cannot_see_purchase_price(): void
    {
        $vendeur = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($vendeur->hasPermission('see_purchase_price'));
    }

    /**
     * Test that admin can see purchase price.
     */
    public function test_admin_can_see_purchase_price(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->hasPermission('see_purchase_price'));
    }

    /**
     * Test that vendeur cannot cancel sales.
     */
    public function test_vendeur_cannot_cancel_sales(): void
    {
        $vendeur = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($vendeur->hasPermission('cancel_sale'));
    }

    /**
     * Test that admin can cancel sales.
     */
    public function test_admin_can_cancel_sales(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->hasPermission('cancel_sale'));
    }

    /**
     * Test that vendor cannot manage users.
     */
    public function test_vendeur_cannot_manage_users(): void
    {
        $vendeur = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($vendeur->hasPermission('manage_users'));
    }

    /**
     * Test that only admin can manage users.
     */
    public function test_only_admin_can_manage_users(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->hasPermission('manage_users'));
    }

    /**
     * Test that unauthenticated user cannot see anything.
     */
    public function test_unauthenticated_redirect_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
