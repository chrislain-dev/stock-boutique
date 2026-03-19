<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS stock_movements_type_check');

        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT stock_movements_type_check
            CHECK (type::text = ANY (ARRAY[
                'stock_in',
                'sale_out',
                'client_return',
                'supplier_return',
                'transfer',
                'adjustment',
                'loss',
                'trade_in'
            ]::text[]))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS stock_movements_type_check');

        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT stock_movements_type_check
            CHECK (type::text = ANY (ARRAY[
                'stock_in',
                'sale_out',
                'client_return',
                'supplier_return',
                'transfer',
                'adjustment',
                'loss'
            ]::text[]))
        ");
    }
};
