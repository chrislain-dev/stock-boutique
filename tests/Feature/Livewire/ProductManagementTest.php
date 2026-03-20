<?php

namespace Tests\Feature\Livewire;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProductService;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
    }

    // ─── Accès ────────────────────────────────────────────────

    public function test_guest_cannot_access_products_page(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_products_page(): void
    {
        $this->actingAsAdmin();
        $this->get(route('products.index'))->assertStatus(200);
    }

    // ─── Création produit ─────────────────────────────────────

    public function test_admin_can_create_product(): void
    {
        $user         = $this->createAdmin();
        $productModel = ProductModel::factory()->create();
        $supplier     = Supplier::factory()->create();
        $this->actingAs($user);

        $product = $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '100200300400500',
            'purchase_price'   => 200000,
            'client_price'     => 260000,
            'reseller_price'   => 240000,
            'purchase_date'    => now()->toDateString(),
            'supplier_id'      => $supplier->id,
        ]);

        $this->assertDatabaseHas('products', ['imei' => '100200300400500']);
        $this->assertEquals('available', $product->state->value);
    }

    public function test_product_creation_records_stock_movement(): void
    {
        $user         = $this->createAdmin();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $product = $this->service->createSingle([
            'product_model_id' => $productModel->id,
            'imei'             => '200300400500600',
            'purchase_price'   => 200000,
            'client_price'     => 260000,
            'reseller_price'   => 240000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => 'stock_in',
            'quantity'   => 1,
            'created_by' => $user->id,
        ]);
    }

    public function test_duplicate_imei_is_rejected_in_bulk_import(): void
    {
        $user         = $this->createAdmin();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $commonData = [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 200000,
            'client_price'     => 260000,
            'reseller_price'   => 240000,
            'purchase_date'    => now()->toDateString(),
        ];

        // Créer d'abord
        $this->service->createBulkFromImei(['300400500600700'], $commonData);

        // Réessayer avec le même IMEI
        $results = $this->service->createBulkFromImei(['300400500600700', '400500600700800'], $commonData);

        $this->assertCount(1, $results['success']);
        $this->assertCount(1, $results['errors']);
    }

    // ─── Modification produit ─────────────────────────────────

    public function test_admin_can_update_product(): void
    {
        $user    = $this->createAdmin();
        $product = Product::factory()->create(['created_by' => $user->id]);
        $this->actingAs($user);

        $updated = $this->service->update($product, [
            'state'          => 'available',
            'location'       => 'store',
            'purchase_price' => 999999,
            'client_price'   => $product->client_price,
            'reseller_price' => $product->reseller_price,
            'purchase_date'  => $product->purchase_date,
        ]);

        $this->assertEquals(999999, $updated->purchase_price);
    }

    public function test_location_change_creates_transfer_movement(): void
    {
        $user    = $this->createAdmin();
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

    public function test_price_change_creates_price_history(): void
    {
        $user    = $this->createAdmin();
        $product = Product::factory()->create([
            'created_by'     => $user->id,
            'purchase_price' => 100000,
            'client_price'   => 130000,
            'reseller_price' => 120000,
        ]);
        $this->actingAs($user);

        $this->service->update($product, [
            'state'          => 'available',
            'location'       => $product->location->value,
            'purchase_price' => 110000,
            'client_price'   => 145000,
            'reseller_price' => 135000,
            'purchase_date'  => $product->purchase_date,
        ]);

        $this->assertDatabaseHas('price_history', [
            'product_id'         => $product->id,
            'old_purchase_price' => 100000,
            'new_purchase_price' => 110000,
        ]);
    }

    // ─── Vendeur ne peut pas modifier les prix ─────────────────

    public function test_vendeur_cannot_access_product_edit(): void
    {
        $vendeur = $this->createVendeur();
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($vendeur);
        $response = $this->get(route('products.edit', $product));

        $response->assertStatus(403);
    }

    // ─── États produit ────────────────────────────────────────

    public function test_product_states_are_valid(): void
    {
        $validStates = ['available', 'sold', 'reserved', 'returned', 'defective', 'in_repair', 'returned_to_supplier', 'trade_in', 'lost'];

        foreach ($validStates as $state) {
            $product = Product::factory()->make(['state' => $state]);
            $this->assertEquals($state, $product->state->value ?? $product->state);
        }
    }

    // ─── CSV Import ───────────────────────────────────────────

    public function test_csv_import_creates_products_from_csv_content(): void
    {
        $user         = $this->createAdmin();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $csv = "500600700800900\n600700800900100\n700800900100200";

        $results = $this->service->importFromCsv($csv, [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 200000,
            'client_price'     => 260000,
            'reseller_price'   => 240000,
            'purchase_date'    => now()->toDateString(),
        ]);

        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['errors']);
    }

    public function test_csv_import_skips_header_row(): void
    {
        $user         = $this->createAdmin();
        $productModel = ProductModel::factory()->create();
        $this->actingAs($user);

        $csv = "IMEI\n800900100200300\n900100200300400";

        $results = $this->service->importFromCsv($csv, [
            'product_model_id' => $productModel->id,
            'purchase_price'   => 200000,
            'client_price'     => 260000,
            'reseller_price'   => 240000,
            'purchase_date'    => now()->toDateString(),
        ]);

        // Le header est ignoré, seules les 2 lignes de données passent
        $this->assertCount(2, $results['success']);
    }
}
