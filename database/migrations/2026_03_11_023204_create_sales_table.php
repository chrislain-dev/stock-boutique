<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // ─── Référence unique ─────────────────────────────────
            $table->string('reference')->unique()
                ->comment('Ex: VTE-2024-00001');

            // ─── Type de client ───────────────────────────────────
            $table->enum('customer_type', ['client', 'reseller']);

            // ─── Si revendeur ─────────────────────────────────────
            $table->foreignId('reseller_id')->nullable()
                ->constrained()->nullOnDelete();

            // ─── Si client anonyme ────────────────────────────────
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // ─── Montants ─────────────────────────────────────────
            $table->decimal('total_amount', 10, 2)
                ->comment('Montant total de la vente');
            $table->decimal('paid_amount', 10, 2)->default(0)
                ->comment('Montant déjà payé');

            // ─── Statut paiement ──────────────────────────────────
            $table->enum('payment_status', [
                'paid',         // Tout payé
                'partial',      // Acompte versé
                'unpaid',       // Rien payé (crédit total)
            ])->default('unpaid');

            // ─── Statut vente ─────────────────────────────────────
            $table->enum('sale_status', [
                'completed',
                'cancelled',
                'partial_return',
                'full_return',
            ])->default('completed');

            // ─── Troc ─────────────────────────────────────────────
            $table->boolean('is_trade_in')->default(false)
                ->comment('Vente avec échange de produit');
            $table->foreignId('trade_in_product_id')->nullable()
                ->constrained('products')->nullOnDelete()
                ->comment('Produit donné en échange par le client');
            $table->decimal('trade_in_value', 10, 2)->nullable()
                ->comment('Valeur estimée du produit échangé');
            $table->text('trade_in_notes')->nullable()
                ->comment('État et notes sur le produit échangé');

            // ─── Échéance paiement ────────────────────────────────
            $table->date('due_date')->nullable()
                ->comment('Date limite de paiement du reliquat');

            $table->text('notes')->nullable();

            // ─── Traçabilité ──────────────────────────────────────
            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ─── Index ────────────────────────────────────────────
            $table->index(['customer_type', 'payment_status']);
            $table->index('reseller_id');
            $table->index('sale_status');
            $table->index('created_at');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
