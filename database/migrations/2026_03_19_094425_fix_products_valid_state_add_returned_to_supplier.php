<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_valid_state');

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_valid_state
            CHECK (state IN (
                'available',
                'sold',
                'reserved',
                'returned',
                'returned_to_supplier',
                'defective',
                'in_repair',
                'trade_in',
                'lost'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_valid_state');

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_valid_state
            CHECK (state IN (
                'available',
                'sold',
                'reserved',
                'returned',
                'defective',
                'in_repair'
            ))
        ");
    }
};
