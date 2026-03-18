<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    /**
     * Test that admin has permission.
     */
    public function test_admin_has_purchase_price_permission(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->hasPermission('see_purchase_price'));
    }

    /**
     * Test that seller does not have admin permissions.
     */
    public function test_seller_cannot_see_purchase_price(): void
    {
        $seller = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($seller->hasPermission('see_purchase_price'));
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
     * Test that seller cannot adjust stock.
     */
    public function test_seller_cannot_adjust_stock(): void
    {
        $seller = User::factory()->create([
            'role' => UserRole::VENDEUR,
        ]);

        $this->assertFalse($seller->hasPermission('adjust_stock'));
    }
}
