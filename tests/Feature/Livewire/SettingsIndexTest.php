<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Settings\Index;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
    }

    // ─── Accès ────────────────────────────────────────────────

    public function test_admin_can_access_settings(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_vendeur_gets_403_on_settings(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::actingAs($this->createVendeur())->test(Index::class);
    }

    // ─── mount / loadSettings ─────────────────────────────────

    public function test_settings_are_loaded_on_mount(): void
    {
        Setting::set('boutique.nom', 'TechShop BJ');
        Cache::flush();

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertSet('nom', 'TechShop BJ');
    }

    public function test_default_active_tab_is_general(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->assertSet('activeTab', 'general');
    }

    // ─── saveGeneral ──────────────────────────────────────────

    public function test_save_general_persists_boutique_settings(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', 'Ma Boutique')
            ->set('telephone', '+22999123456')
            ->set('email', 'contact@boutique.bj')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        Cache::flush();
        $this->assertEquals('Ma Boutique', Setting::get('boutique.nom'));
        $this->assertEquals('+22999123456', Setting::get('boutique.telephone'));
    }

    public function test_nom_is_required_in_save_general(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', '')
            ->call('saveGeneral')
            ->assertHasErrors(['nom' => 'required']);
    }

    public function test_email_must_be_valid_in_save_general(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', 'Test')
            ->set('email', 'not-an-email')
            ->call('saveGeneral')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_logo_must_be_image_file(): void
    {
        $notAnImage = UploadedFile::fake()->create('document.pdf', 100);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', 'Test')
            ->set('logo', $notAnImage)
            ->call('saveGeneral')
            ->assertHasErrors(['logo' => 'image']);
    }

    public function test_logo_upload_stores_file_and_saves_setting(): void
    {
        $image = UploadedFile::fake()->image('logo.png', 200, 200);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', 'TechShop')
            ->set('logo', $image)
            ->call('saveGeneral')
            ->assertHasNoErrors();

        Cache::flush();
        $logoPath = Setting::get('boutique.logo');
        $this->assertNotEmpty($logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_logo_is_reset_to_null_after_upload(): void
    {
        $image = UploadedFile::fake()->image('logo.png');

        $component = Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('nom', 'TechShop')
            ->set('logo', $image)
            ->call('saveGeneral');

        $component->assertSet('logo', null);
    }

    // ─── saveTheme ────────────────────────────────────────────

    public function test_save_theme_persists_color_settings(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('primary', '#1a1a2e')
            ->set('primary_dark', '#0f0f1e')
            ->set('secondary', '#4f46e5')
            ->set('accent', '#06b6d4')
            ->set('sidebar_bg', '#0a0a0f')
            ->set('sidebar_text', '#ffffff')
            ->call('saveTheme')
            ->assertHasNoErrors();

        Cache::flush();
        $this->assertEquals('#1a1a2e', Setting::get('theme.primary'));
        $this->assertEquals('#0f0f1e', Setting::get('theme.primary_dark'));
    }

    public function test_primary_is_required_in_save_theme(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('primary', '')
            ->call('saveTheme')
            ->assertHasErrors(['primary' => 'required']);
    }

    public function test_all_theme_colors_are_required(): void
    {
        $requiredFields = ['primary', 'primary_dark', 'secondary', 'accent', 'sidebar_bg', 'sidebar_text'];

        foreach ($requiredFields as $field) {
            Livewire::actingAs($this->createAdmin())
                ->test(Index::class)
                ->set($field, '')
                ->call('saveTheme')
                ->assertHasErrors([$field => 'required']);
        }
    }

    public function test_save_theme_redirects(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('primary', '#111111')
            ->set('primary_dark', '#222222')
            ->set('secondary', '#333333')
            ->set('accent', '#444444')
            ->set('sidebar_bg', '#555555')
            ->set('sidebar_text', '#ffffff')
            ->call('saveTheme')
            ->assertRedirect(route('settings.index'));
    }

    // ─── saveVente ────────────────────────────────────────────

    public function test_save_vente_persists_business_rules(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('permettre_credit_client', true)
            ->set('acompte_minimum_revendeur', 30)
            ->set('delai_paiement_max_jours', 60)
            ->call('saveVente')
            ->assertHasNoErrors();

        Cache::flush();
        $this->assertEquals(30, Setting::get('vente.acompte_minimum_revendeur'));
        $this->assertEquals(60, Setting::get('vente.delai_paiement_max_jours'));
    }

    public function test_acompte_must_be_between_0_and_100(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('acompte_minimum_revendeur', 150)
            ->call('saveVente')
            ->assertHasErrors(['acompte_minimum_revendeur' => 'max']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('acompte_minimum_revendeur', -1)
            ->call('saveVente')
            ->assertHasErrors(['acompte_minimum_revendeur' => 'min']);
    }

    public function test_delai_paiement_must_be_between_1_and_365(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('delai_paiement_max_jours', 0)
            ->call('saveVente')
            ->assertHasErrors(['delai_paiement_max_jours' => 'min']);

        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('delai_paiement_max_jours', 400)
            ->call('saveVente')
            ->assertHasErrors(['delai_paiement_max_jours' => 'max']);
    }

    public function test_permettre_credit_false_saves_as_zero(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Index::class)
            ->set('permettre_credit_client', false)
            ->set('acompte_minimum_revendeur', 20)
            ->set('delai_paiement_max_jours', 30)
            ->call('saveVente')
            ->assertHasNoErrors();

        Cache::flush();
        $this->assertEquals('0', Setting::get('vente.permettre_credit_client'));
    }
}
