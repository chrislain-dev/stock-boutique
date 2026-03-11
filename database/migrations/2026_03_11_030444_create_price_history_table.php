<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();

            // ─── Produit concerné ─────────────────────────────────
            // Peut être un product_model (prix de référence)
            // ou un product individuel (prix spécifique à l'unité)
            $table->foreignId('product_model_id')->nullable()
                ->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()
                ->constrained()->cascadeOnDelete();

            // ─── Anciens prix ─────────────────────────────────────
            $table->decimal('old_purchase_price', 10, 2)->nullable()
                ->comment('Ancien prix d\'achat');
            $table->decimal('old_client_price', 10, 2)->nullable()
                ->comment('Ancien prix de vente client');
            $table->decimal('old_reseller_price', 10, 2)->nullable()
                ->comment('Ancien prix de vente revendeur');

            // ─── Nouveaux prix ────────────────────────────────────
            $table->decimal('new_purchase_price', 10, 2)->nullable()
                ->comment('Nouveau prix d\'achat');
            $table->decimal('new_client_price', 10, 2)->nullable()
                ->comment('Nouveau prix de vente client');
            $table->decimal('new_reseller_price', 10, 2)->nullable()
                ->comment('Nouveau prix de vente revendeur');

            // ─── Raison du changement ─────────────────────────────
            $table->string('reason')->nullable()
                ->comment('Ex: Réajustement marché, Promotion, Erreur corrigée');

            // ─── Traçabilité (immuable) ───────────────────────────
            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at');

            // ─── Index ────────────────────────────────────────────
            $table->index(['product_model_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_history');
    }
};
