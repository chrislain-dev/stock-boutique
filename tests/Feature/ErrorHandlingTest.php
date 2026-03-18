<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    /**
     * Test 404 error page displays.
     */
    public function test_404_error_page_displays(): void
    {
        $response = $this->get('/non-existent-page');

        $response->assertStatus(404);
        $response->assertViewIs('errors.404');
    }

    /**
     * Test 403 forbidden error.
     */
    public function test_403_forbidden_without_permission(): void
    {
        $this->signInUser();

        // Attempt to access admin-only endpoint
        $response = $this->get('/admin/settings');

        // This assumes a middleware check exists
        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user redirects to login.
     */
    public function test_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
