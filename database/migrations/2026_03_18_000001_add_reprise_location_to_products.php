<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne contrainte
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_location_check');

        // Recréer avec 'reprise' ajouté
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_location_check
            CHECK (location::text = ANY (ARRAY[
                'store',
                'transit',
                'client',
                'reseller',
                'repair_shop',
                'reprise'
            ]::text[]))
        ");
    }

    public function down(): void
    {
        // Restaurer l'ancienne contrainte sans 'reprise'
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_location_check');

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_location_check
            CHECK (location::text = ANY (ARRAY[
                'store',
                'transit',
                'client',
                'reseller',
                'repair_shop'
            ]::text[]))
        ");
    }
};
