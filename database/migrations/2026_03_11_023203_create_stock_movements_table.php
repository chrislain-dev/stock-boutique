<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // ─── References ──────────────────────────────────────
            $table->foreignId('product_model_id')->constrained()->restrictOnDelete();

            // For serialized products (phones, PC, tablets)
            $table->foreignId('product_id')->nullable()
                ->constrained()->nullOnDelete();

            // ─── Movement type ───────────────────────────────────
            $table->enum('type', [
                'stock_in',           // Entrée stock fournisseur
                'sale_out',           // Sortie vente client/revendeur
                'client_return',      // Retour client
                'supplier_return',    // Retour fournisseur
                'transfer',           // Changement de localisation
                'adjustment',         // Correction manuelle (admin uniquement)
                'loss',               // Perte/vol
            ]);

            // ─── Quantity ────────────────────────────────────────
            // For accessories: actual quantity
            // For serialized: always 1
            $table->integer('quantity')->default(1);
            $table->integer('quantity_before')
                ->comment('Stock avant mouvement');
            $table->integer('quantity_after')
                ->comment('Stock après mouvement');

            // ─── Location tracking ───────────────────────────────
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();

            // ─── Polymorphic relation (sale, adjustment, etc.) ───
            $table->nullableMorphs('moveable');

            $table->text('notes')->nullable();

            // ─── Traceability (immutable) ────────────────────────
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at');

            // ─── Indexes ─────────────────────────────────────────
            $table->index(['product_model_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
