<?php

namespace Tests\Feature\Livewire;

use App\Models\Reseller;
use App\Models\User;
use Tests\TestCase;

class ResellerTest extends TestCase
{
    // ─── Accès ────────────────────────────────────────────────

    public function test_guest_cannot_access_resellers_page(): void
    {
        $this->withExceptionHandling();
        $response = $this->get(route('resellers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_resellers_page(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('resellers.index'));
        $response->assertStatus(200);
    }

    // ─── Création ─────────────────────────────────────────────

    public function test_reseller_is_created_with_valid_data(): void
    {
        $reseller = Reseller::factory()->create([
            'name'  => 'John Doe',
            'phone' => '+22912345678',
        ]);

        $this->assertDatabaseHas('resellers', [
            'name'  => 'John Doe',
            'phone' => '+22912345678',
        ]);
    }

    public function test_new_reseller_has_zero_debt_by_default(): void
    {
        $reseller = Reseller::factory()->create();
        $this->assertEquals(0, $reseller->solde_du);
    }

    public function test_new_reseller_is_active_by_default(): void
    {
        $reseller = Reseller::factory()->create();
        $this->assertTrue($reseller->is_active);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function test_scope_active_excludes_inactive_resellers(): void
    {
        Reseller::factory()->create(['is_active' => true]);
        Reseller::factory()->create(['is_active' => false]);

        $this->assertCount(1, Reseller::active()->get());
    }

    public function test_scope_with_debt_returns_only_debtors(): void
    {
        Reseller::factory()->withDebt(50000)->create();
        Reseller::factory()->create(['solde_du' => 0]);

        $this->assertCount(1, Reseller::withDebt()->get());
    }

    // ─── has_debt accessor ────────────────────────────────────

    public function test_has_debt_returns_true_when_solde_du_positive(): void
    {
        $reseller = Reseller::factory()->make(['solde_du' => 50000]);
        $this->assertTrue($reseller->has_debt);
    }

    public function test_has_debt_returns_false_when_solde_du_zero(): void
    {
        $reseller = Reseller::factory()->make(['solde_du' => 0]);
        $this->assertFalse($reseller->has_debt);
    }

    // ─── SoftDelete ───────────────────────────────────────────

    public function test_reseller_is_soft_deleted(): void
    {
        $reseller = Reseller::factory()->create();
        $reseller->delete();

        $this->assertSoftDeleted($reseller);
        $this->assertNull(Reseller::find($reseller->id));
        $this->assertNotNull(Reseller::withTrashed()->find($reseller->id));
    }
}
