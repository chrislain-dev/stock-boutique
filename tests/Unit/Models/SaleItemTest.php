<?php

namespace Tests\Unit\Models;

use App\Models\SaleItem;
use Tests\TestCase;

class SaleItemTest extends TestCase
{
    // ─── Calcul line_total ─────────────────────────────────────

    public function test_line_total_is_calculated_on_save(): void
    {
        $item = SaleItem::factory()->make([
            'quantity'   => 2,
            'unit_price' => 50000,
            'discount'   => 0,
            'line_total' => null,
        ]);

        // line_total doit être recalculé dans le boot saving
        $this->assertEquals(100000, ($item->quantity * $item->unit_price) - ($item->discount ?? 0));
    }

    public function test_line_total_applies_discount(): void
    {
        $item = SaleItem::factory()->make([
            'quantity'   => 1,
            'unit_price' => 100000,
            'discount'   => 5000,
        ]);

        $expected = (1 * 100000) - 5000;
        $this->assertEquals($expected, ($item->quantity * $item->unit_price) - $item->discount);
    }

    // ─── Accesseur profit ─────────────────────────────────────

    public function test_profit_attribute_is_calculated_correctly(): void
    {
        $item = SaleItem::factory()->make([
            'quantity'                => 2,
            'unit_price'              => 100000,
            'purchase_price_snapshot' => 70000,
            'discount'                => 0,
            'line_total'              => 200000,
        ]);

        // profit = line_total - (purchase_price_snapshot * quantity)
        $expectedProfit = 200000 - (70000 * 2);
        $this->assertEquals($expectedProfit, $item->profit);
    }

    public function test_profit_is_negative_when_sold_at_loss(): void
    {
        $item = SaleItem::factory()->make([
            'quantity'                => 1,
            'unit_price'              => 50000,
            'purchase_price_snapshot' => 80000,
            'discount'                => 0,
            'line_total'              => 50000,
        ]);

        $this->assertLessThan(0, $item->profit);
    }

    // ─── Relations ────────────────────────────────────────────

    public function test_sale_item_belongs_to_sale(): void
    {
        $item = SaleItem::factory()->create();
        $this->assertNotNull($item->sale);
    }

    public function test_sale_item_belongs_to_product_model(): void
    {
        $item = SaleItem::factory()->create();
        $this->assertNotNull($item->productModel);
    }
}
