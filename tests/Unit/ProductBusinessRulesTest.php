<?php

namespace Tests\Unit;

use App\Enums\ProductState;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductModel;
use Tests\TestCase;

class ProductBusinessRulesTest extends TestCase
{
    /**
     * Test that product prices must be positive.
     */
    public function test_product_purchase_price_must_be_positive(): void
    {
        $brand = Brand::factory()->create();
        $productModel = ProductModel::factory()->create();

        $this->expectException(\Exception::class);

        Product::create([
            'product_model_id' => $productModel->id,
            'brand_id' => $brand->id,
            'purchase_price' => -100,
            'client_price' => 200,
            'reseller_price' => 180,
            'state' => ProductState::AVAILABLE,
            'location' => 'store',
        ]);
    }

    /**
     * Test that client price must be >= purchase price.
     */
    public function test_client_price_must_be_gte_purchase_price(): void
    {
        $brand = Brand::factory()->create();
        $productModel = ProductModel::factory()->create();

        $this->expectException(\Exception::class);

        Product::create([
            'product_model_id' => $productModel->id,
            'brand_id' => $brand->id,
            'purchase_price' => 200,
            'client_price' => 100,
            'reseller_price' => 180,
            'state' => ProductState::AVAILABLE,
            'location' => 'store',
        ]);
    }

    /**
     * Test that product cannot be deleted if not AVAILABLE.
     */
    public function test_cannot_delete_sold_product(): void
    {
        $product = Product::factory()->create([
            'state' => ProductState::SOLD,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Impossible de supprimer');

        $product->delete();
    }

    /**
     * Test that product can be deleted if AVAILABLE.
     */
    public function test_can_delete_available_product(): void
    {
        $product = Product::factory()->create([
            'state' => ProductState::AVAILABLE,
        ]);

        $result = $product->delete();

        $this->assertTrue($result);
        $this->assertSoftDeleted($product);
    }
}
