<?php
// ══════════════════════════════════════════════════════════════════════════
// FICHIER 1 : database/migrations/2026_03_19_000001_add_supplier_return_to_location_checks.php
// ══════════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── products.location ────────────────────────────────────────────
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
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_location_check');
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_location_check
            CHECK (location::text = ANY (ARRAY[
                'store', 'transit', 'client', 'reseller', 'repair_shop', 'reprise'
            ]::text[]))
        ");

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
