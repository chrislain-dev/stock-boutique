<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup methods run before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Disable query logging to speed up tests
        \Illuminate\Database\Eloquent\Model::unguard();
    }

    /**
     * Create and authenticate a user for testing.
     */
    protected function signInUser($user = null)
    {
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }

        $this->actingAs($user);

        return $user;
    }

    /**
     * Create and authenticate an admin user.
     */
    protected function signInAdmin($user = null)
    {
        if (!$user) {
            $user = \App\Models\User::factory()->create([
                'role' => \App\Enums\UserRole::ADMIN,
            ]);
        }

        return $this->signInUser($user);
    }
}
