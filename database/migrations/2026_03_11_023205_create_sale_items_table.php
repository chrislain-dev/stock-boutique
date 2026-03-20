<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained()->cascadeOnDelete();

            $table->foreignId('product_model_id')
                ->constrained()->restrictOnDelete();

            // ─── Pour produits sérialisés (1 ligne = 1 unité) ────
            $table->foreignId('product_id')->nullable()
                ->constrained()->nullOnDelete();

            // ─── Pour accessoires (1 ligne = N unités) ───────────
            $table->unsignedInteger('quantity')->default(1);

            // ─── Prix au moment de la vente ───────────────────────
            // On snapshot les prix car ils peuvent changer après
            $table->unsignedBigInteger('unit_price')
                ->comment('Prix unitaire au moment de la vente');
            $table->unsignedBigInteger('purchase_price_snapshot')
                ->comment('Prix d\'achat au moment de la vente (pour calcul marge)');
            $table->unsignedBigInteger('discount')->default(0)
                ->comment('Remise accordée sur cette ligne');
            $table->unsignedBigInteger('line_total')
                ->comment('(unit_price - discount) * quantity');

            $table->timestamps();

            // ─── Index ────────────────────────────────────────────
            $table->index('sale_id');
            $table->index('product_id');
            $table->index('product_model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
