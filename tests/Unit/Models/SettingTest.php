<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ─── get() ────────────────────────────────────────────────

    public function test_get_returns_value_from_database(): void
    {
        Setting::create([
            'key'   => 'boutique.nom',
            'value' => 'TechShop BJ',
            'type'  => 'string',
            'group' => 'boutique',
            'label' => 'Nom boutique',
        ]);

        $this->assertEquals('TechShop BJ', Setting::get('boutique.nom'));
    }

    public function test_get_returns_default_when_key_not_found(): void
    {
        $result = Setting::get('nonexistent.key', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function test_get_casts_boolean_correctly(): void
    {
        Setting::create([
            'key'   => 'feature.active',
            'value' => 'true',
            'type'  => 'boolean',
            'group' => 'feature',
            'label' => 'Feature active',
        ]);

        Cache::flush();
        $this->assertTrue(Setting::get('feature.active'));
    }

    public function test_get_casts_integer_correctly(): void
    {
        Setting::create([
            'key'   => 'stock.minimum',
            'value' => '5',
            'type'  => 'integer',
            'group' => 'stock',
            'label' => 'Stock minimum',
        ]);

        Cache::flush();
        $result = Setting::get('stock.minimum');
        $this->assertIsInt($result);
        $this->assertEquals(5, $result);
    }

    public function test_get_returns_cached_value(): void
    {
        Setting::create([
            'key'   => 'cached.key',
            'value' => 'original',
            'type'  => 'string',
            'group' => 'test',
            'label' => 'Test',
        ]);

        // Premier appel — met en cache
        Setting::get('cached.key');

        // Modifier directement en DB sans vider le cache
        Setting::where('key', 'cached.key')->update(['value' => 'modified']);

        // Doit retourner la valeur cachée
        $this->assertEquals('original', Setting::get('cached.key'));
    }

    // ─── set() ────────────────────────────────────────────────

    public function test_set_creates_new_setting(): void
    {
        Setting::set('new.key', 'new_value');

        $this->assertDatabaseHas('settings', [
            'key'   => 'new.key',
            'value' => 'new_value',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        Setting::set('update.key', 'first_value');
        Setting::set('update.key', 'second_value');

        $this->assertDatabaseHas('settings', [
            'key'   => 'update.key',
            'value' => 'second_value',
        ]);
        $this->assertCount(1, Setting::where('key', 'update.key')->get());
    }

    public function test_set_clears_cache(): void
    {
        Setting::create([
            'key'   => 'clearable.key',
            'value' => 'old',
            'type'  => 'string',
            'group' => 'test',
            'label' => 'Test',
        ]);

        // Mettre en cache
        Setting::get('clearable.key');

        // set() doit vider le cache
        Setting::set('clearable.key', 'new_value');

        // Doit retourner la nouvelle valeur
        $this->assertEquals('new_value', Setting::get('clearable.key'));
    }
}
