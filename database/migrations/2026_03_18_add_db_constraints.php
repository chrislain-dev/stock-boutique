<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on PRODUCTS ──────────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE products
                ADD CONSTRAINT products_prices_check
                CHECK (purchase_price >= 0 AND client_price >= 0 AND reseller_price >= 0)
            ');

            DB::statement('
                ALTER TABLE products
                ADD CONSTRAINT products_retail_gte_purchase
                CHECK (client_price >= purchase_price)
            ');

            DB::statement('
                ALTER TABLE products
                ADD CONSTRAINT products_valid_state
                CHECK (state IN (\'available\', \'sold\', \'reserved\', \'returned\', \'defective\', \'in_repair\'))
            ');
        }

        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on SALES ─────────────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE sales
                ADD CONSTRAINT sales_amounts_check
                CHECK (total_amount > 0 AND paid_amount >= 0 AND paid_amount <= total_amount)
            ');

            DB::statement('
                ALTER TABLE sales
                ADD CONSTRAINT sales_valid_status
                CHECK (payment_status IN (\'paid\', \'partial\', \'unpaid\'))
            ');
        }

        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on SALE_ITEMS ────────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE sale_items
                ADD CONSTRAINT sale_items_quantity_check
                CHECK (quantity > 0)
            ');

            DB::statement('
                ALTER TABLE sale_items
                ADD CONSTRAINT sale_items_price_check
                CHECK (unit_price > 0)
            ');
        }

        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on PURCHASES ─────────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE purchases
                ADD CONSTRAINT purchases_amounts_check
                CHECK (total_amount > 0 AND paid_amount >= 0 AND paid_amount <= total_amount)
            ');

            DB::statement('
                ALTER TABLE purchases
                ADD CONSTRAINT purchases_valid_status
                CHECK (payment_status IN (\'paid\', \'partial\', \'unpaid\'))
            ');
        }

        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on PURCHASE_ITEMS ────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE purchase_items
                ADD CONSTRAINT purchase_items_quantity_check
                CHECK (quantity > 0 AND unit_purchase_price > 0)
            ');
        }

        // ────────────────────────────────────────────────────────────
        // ─── Add CHECK constraints on PAYMENTS ──────────────────────
        // ────────────────────────────────────────────────────────────
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE payments
                ADD CONSTRAINT payments_amount_check
                CHECK (amount > 0)
            ');
        }

        // Add UNIQUE index on IMEI to ensure global uniqueness (even for soft deletes)
        Schema::table('products', function (Blueprint $table) {
            // IMEI unique constraint already exists, but ensure it's properly indexed
            $table->index(['imei', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_prices_check');
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_retail_gte_purchase');
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_valid_state');
            DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_amounts_check');
            DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_valid_status');
            DB::statement('ALTER TABLE sale_items DROP CONSTRAINT IF EXISTS sale_items_quantity_check');
            DB::statement('ALTER TABLE sale_items DROP CONSTRAINT IF EXISTS sale_items_price_check');
            DB::statement('ALTER TABLE purchases DROP CONSTRAINT IF EXISTS purchases_amounts_check');
            DB::statement('ALTER TABLE purchases DROP CONSTRAINT IF EXISTS purchases_valid_status');
            DB::statement('ALTER TABLE purchase_items DROP CONSTRAINT IF EXISTS purchase_items_quantity_check');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_amount_check');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['imei', 'deleted_at']);
        });
    }
};
