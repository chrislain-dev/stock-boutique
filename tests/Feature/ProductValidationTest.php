<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ProductModel;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    /**
     * Test that product creation requires valid data.
     */
    public function test_product_requires_brand(): void
    {
        $this->signInAdmin();

        $response = $this->post('/api/products', [
            'brand_id' => null,
            'product_model_id' => 1,
            'category' => 'PHONE',
            'purchase_price' => 100,
            'retail_price' => 200,
            'reseller_price' => 180,
        ]);

        $response->assertSessionHasErrors('brand_id');
    }

    /**
     * Test that retail price must be >= purchase price.
     */
    public function test_retail_price_must_be_greater_than_purchase_price(): void
    {
        $this->signInAdmin();

        $brand = Brand::factory()->create();
        $productModel = ProductModel::factory()->create();

        $response = $this->post('/api/products', [
            'brand_id' => $brand->id,
            'product_model_id' => $productModel->id,
            'category' => 'PHONE',
            'purchase_price' => 200,
            'retail_price' => 100,
            'reseller_price' => 180,
        ]);

        $response->assertSessionHasErrors('retail_price');
    }

    /**
     * Test that IMEI must be unique globally.
     */
    public function test_imei_must_be_unique(): void
    {
        $this->signInAdmin();

        $brand = Brand::factory()->create();
        $productModel = ProductModel::factory()->create();

        // Create first product with IMEI
        \App\Models\Product::factory()->create([
            'imei' => '123456789012345',
            'brand_id' => $brand->id,
            'product_model_id' => $productModel->id,
        ]);

        // Try to create another with same IMEI
        $response = $this->post('/api/products', [
            'brand_id' => $brand->id,
            'product_model_id' => $productModel->id,
            'category' => 'PHONE',
            'purchase_price' => 100,
            'retail_price' => 200,
            'reseller_price' => 180,
            'imei' => '123456789012345',
        ]);

        $response->assertSessionHasErrors('imei');
    }
}
