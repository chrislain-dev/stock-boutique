<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // ─── Référence unique ─────────────────────────────────
            $table->string('reference')->unique()
                ->comment('Ex: ACH-2024-00001');

            // ─── Fournisseur ──────────────────────────────────────
            $table->foreignId('supplier_id')
                ->constrained()->restrictOnDelete();

            // ─── Montants ─────────────────────────────────────────
            $table->decimal('total_amount', 10, 2)
                ->comment('Montant total de la commande');
            $table->decimal('paid_amount', 10, 2)->default(0)
                ->comment('Montant déjà payé au fournisseur');

            // ─── Statut paiement fournisseur ──────────────────────
            $table->enum('payment_status', [
                'paid',       // Tout payé
                'partial',    // Partiellement payé
                'unpaid',     // Pas encore payé
            ])->default('unpaid');

            // ─── Statut de la commande ────────────────────────────
            $table->enum('status', [
                'received',     // Reçu en stock
                'pending',      // En attente de réception
                'cancelled',    // Annulé
            ])->default('received');

            // ─── Mode de paiement fournisseur ─────────────────────
            $table->enum('payment_method', [
                'cash',
                'mobile_money',
                'bank_transfer',
                'cheque',
            ])->nullable();

            $table->string('transaction_reference')->nullable()
                ->comment('Référence paiement fournisseur');

            // ─── Dates ────────────────────────────────────────────
            $table->date('purchase_date')
                ->comment('Date d\'achat/réception');
            $table->date('due_date')->nullable()
                ->comment('Date limite paiement fournisseur');

            $table->text('notes')->nullable();

            // ─── Traçabilité ──────────────────────────────────────
            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ─── Index ────────────────────────────────────────────
            $table->index('supplier_id');
            $table->index('payment_status');
            $table->index('status');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
