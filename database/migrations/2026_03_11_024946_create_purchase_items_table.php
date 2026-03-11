<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained()->cascadeOnDelete();

            $table->foreignId('product_model_id')
                ->constrained()->restrictOnDelete();

            // ─── Pour produits sérialisés (1 ligne = 1 unité) ────
            $table->foreignId('product_id')->nullable()
                ->constrained()->nullOnDelete();

            // ─── Pour accessoires (1 ligne = N unités) ───────────
            $table->unsignedInteger('quantity')->default(1);

            // ─── Prix ─────────────────────────────────────────────
            $table->decimal('unit_purchase_price', 10, 2)
                ->comment('Prix d\'achat unitaire');
            $table->decimal('unit_client_price', 10, 2)
                ->comment('Prix de vente client fixé à l\'achat');
            $table->decimal('unit_reseller_price', 10, 2)
                ->comment('Prix de vente revendeur fixé à l\'achat');
            $table->decimal('line_total', 10, 2)
                ->comment('unit_purchase_price * quantity');

            // ─── Condition à l'entrée stock ───────────────────────
            $table->enum('condition', [
                'sealed',       // Scellé
                'refurbished',  // Reconditionné
                'used',         // Occasion
                'defective',    // Défectueux
            ])->default('sealed');

            $table->text('notes')->nullable();
            $table->timestamps();

            // ─── Index ────────────────────────────────────────────
            $table->index('purchase_id');
            $table->index('product_model_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
