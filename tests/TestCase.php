<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'      => UserRole::ADMIN,
            'is_active' => true,
        ], $overrides));
    }

    protected function createVendeur(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'      => UserRole::VENDEUR,
            'is_active' => true,
        ], $overrides));
    }

    protected function actingAsAdmin(): static
    {
        $this->actingAs($this->createAdmin());
        return $this;
    }

    protected function actingAsVendeur(): static
    {
        $this->actingAs($this->createVendeur());
        return $this;
    }
}
