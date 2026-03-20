<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ProductService;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
    }

    // ─── createSingle ─────────────────────────────────────────

    public function test_create_single_creates_product_in_database(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $supplier     = Supplier::factory()->create();
        $this->actingAs($user);

        $product = $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '123456789012345',
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
            'supplier_id'      => $supplier->id,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', ['imei' => '123456789012345']);
    }

    public function test_create_single_creates_stock_movement(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $product = $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '111111111111111',
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => 'stock_in',
            'quantity'   => 1,
        ]);
    }

    public function test_create_single_sets_state_available(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $product = $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '222222222222222',
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertEquals('available', $product->state->value);
        $this->assertEquals('store', $product->location->value);
    }

    // ─── createBulkFromImei ───────────────────────────────────

    public function test_create_bulk_creates_multiple_products(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $imeiList = ['111000111000111', '222000222000222', '333000333000333'];

        $results = $this->service->createBulkFromImei($imeiList, [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['errors']);
    }

    public function test_create_bulk_skips_duplicate_imei(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        // Créer un premier produit avec cet IMEI
        $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '999000999000999',
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $results = $this->service->createBulkFromImei(['999000999000999', '888000888000888'], [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertCount(1, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('999000999000999', $results['errors'][0]);
    }

    public function test_create_bulk_skips_empty_imei_lines(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $results = $this->service->createBulkFromImei(['444000444000444', '', '  '], [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertCount(1, $results['success']);
    }

    // ─── importFromCsv ────────────────────────────────────────

    public function test_import_from_csv_parses_imei_list(): void
    {
        $user         = User::factory()->create();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $csv = "555000555000555\n666000666000666\n777000777000777";

        $results = $this->service->importFromCsv($csv, [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 100000,
            'client_price'     => 130000,
            'reseller_price'   => 120000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertCount(3, $results['success']);
    }

    // ─── update ───────────────────────────────────────────────

    public function test_update_changes_product_fields(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $user->id]);
        $this->actingAs($user);

        $updated = $this->service->update($product, [
            'imei'           => $product->imei,
            'state'          => 'available',
            'location'       => 'store',
            'purchase_price' => 120000,
            'client_price'   => 150000,
            'reseller_price' => 140000,
            'purchase_date'  => now()->toDateString(),
        ]);

        $this->assertEquals(120000, $updated->purchase_price);
        $this->assertEquals(150000, $updated->client_price);
    }

    public function test_update_creates_stock_movement_on_location_change(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $user->id, 'location' => 'store']);
        $this->actingAs($user);

        $this->service->update($product, [
            'state'          => 'available',
            'location'       => 'reseller',
            'purchase_price' => $product->purchase_price,
            'client_price'   => $product->client_price,
            'reseller_price' => $product->reseller_price,
            'purchase_date'  => $product->purchase_date,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'    => $product->id,
            'type'          => 'transfer',
            'location_from' => 'store',
            'location_to'   => 'reseller',
        ]);
    }

    public function test_update_creates_price_history_on_price_change(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create([
            'created_by'    => $user->id,
            'purchase_price' => 100000,
            'client_price'   => 130000,
            'reseller_price' => 120000,
        ]);
        $this->actingAs($user);

        $this->service->update($product, [
            'state'          => 'available',
            'location'       => $product->location->value,
            'purchase_price' => 110000,
            'client_price'   => 140000,
            'reseller_price' => 130000,
            'purchase_date'  => $product->purchase_date,
        ]);

        $this->assertDatabaseHas('price_history', [
            'product_id'         => $product->id,
            'old_purchase_price' => 100000,
            'new_purchase_price' => 110000,
        ]);
    }
}
