<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    // ─── Rendu ────────────────────────────────────────────────

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_login_component_mounts_correctly(): void
    {
        Livewire::test(Login::class)
            ->assertSet('email', '')
            ->assertSet('password', '')
            ->assertSet('remember', false);
    }

    public function test_login_page_contains_email_field(): void
    {
        Livewire::test(Login::class)
            ->assertSee('email');
    }

    // ─── Connexion réussie ────────────────────────────────────

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = User::factory()->create([
            'email'     => 'admin@test.com',
            'password'  => bcrypt('password123'),
            'role'      => UserRole::ADMIN,
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));
    }

    public function test_vendeur_can_login_with_correct_credentials(): void
    {
        $vendeur = User::factory()->create([
            'email'     => 'vendeur@test.com',
            'password'  => bcrypt('password123'),
            'role'      => UserRole::VENDEUR,
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'vendeur@test.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_is_authenticated_after_login(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login');

        $this->assertAuthenticatedAs($user);
    }

    public function test_remember_me_flag_is_passed_to_auth(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->set('remember', true)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    // ─── Connexion échouée ────────────────────────────────────

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('correct_password'),
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong_password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'nobody@test.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_inactive_user_is_logged_out_after_attempt(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login');

        $this->assertGuest();
    }

    // ─── Validation ───────────────────────────────────────────

    public function test_email_is_required(): void
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_email_must_be_valid_format(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'not-an-email')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_password_is_required(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'admin@test.com')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['password' => 'required']);
    }

    public function test_password_must_be_at_least_6_characters(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'admin@test.com')
            ->set('password', '12345')
            ->call('login')
            ->assertHasErrors(['password' => 'min']);
    }

    // ─── Activity Log ─────────────────────────────────────────

    public function test_successful_login_creates_activity_log(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action'  => 'login',
        ]);
    }

    public function test_failed_login_does_not_create_activity_log(): void
    {
        User::factory()->create([
            'email'     => 'admin@test.com',
            'password'  => bcrypt('correct'),
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'wrong')
            ->call('login');

        $this->assertDatabaseCount('activity_logs', 0);
    }

    // ─── Accès déjà authentifié ───────────────────────────────

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }
}
