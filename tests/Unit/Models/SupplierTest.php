<?php

namespace Tests\Unit\Models;

use App\Models\Supplier;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    public function test_supplier_is_created_with_required_fields(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertNotEmpty($supplier->name);
    }

    public function test_scope_active_returns_only_active_suppliers(): void
    {
        Supplier::factory()->create(['is_active' => true]);
        Supplier::factory()->create(['is_active' => false]);

        $this->assertCount(1, Supplier::active()->get());
    }

    public function test_supplier_is_soft_deleted(): void
    {
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $this->assertSoftDeleted($supplier);
        $this->assertNull(Supplier::find($supplier->id));
        $this->assertNotNull(Supplier::withTrashed()->find($supplier->id));
    }

    public function test_supplier_has_many_products(): void
    {
        $supplier = Supplier::factory()->create();
        $this->assertCount(0, $supplier->products);
    }
}
