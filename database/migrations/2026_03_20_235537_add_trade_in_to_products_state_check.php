<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE products DROP CONSTRAINT products_state_check");
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_state_check CHECK (state::text = ANY (ARRAY['available','sold','reserved','returned','defective','in_repair','returned_to_supplier','trade_in']))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products DROP CONSTRAINT products_state_check");
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_state_check CHECK (state::text = ANY (ARRAY['available','sold','reserved','returned','defective','in_repair','returned_to_supplier']))");
    }
};
