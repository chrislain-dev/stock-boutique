<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── products.location ─────────────────────────────────────────────
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_location_check');
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_location_check
            CHECK (location::text = ANY (ARRAY[
                'store', 'transit', 'client', 'reseller',
                'repair_shop', 'reprise', 'supplier_return'
            ]::text[]))
        ");

        // ── products.state ────────────────────────────────────────────────
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_state_check');
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_state_check
            CHECK (state::text = ANY (ARRAY[
                'available', 'sold', 'reserved', 'returned',
                'defective', 'in_repair', 'returned_to_supplier'
            ]::text[]))
        ");
    }

    public function down(): void
    {
        // Ramener les locations inconnues de l'ancienne contrainte
        DB::table('products')
            ->where('location', 'supplier_return')
            ->update(['location' => 'store']);

        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_location_check');
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_location_check
            CHECK (location::text = ANY (ARRAY[
                'store', 'transit', 'client', 'reseller', 'repair_shop', 'reprise'
            ]::text[]))
        ");

        // Ramener les states inconnus de l'ancienne contrainte
        DB::table('products')
            ->where('state', 'returned_to_supplier')
            ->update(['state' => 'returned']);

        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_state_check');
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_state_check
            CHECK (state::text = ANY (ARRAY[
                'available', 'sold', 'reserved', 'returned', 'defective', 'in_repair'
            ]::text[]))
        ");
    }
};
