<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained()->cascadeOnDelete();

            // ─── Montant ──────────────────────────────────────────
            $table->unsignedBigInteger('amount')
                ->comment('Montant de ce paiement');

            // ─── Mode de paiement ─────────────────────────────────
            $table->enum('payment_method', [
                'cash',           // Espèces
                'mobile_money',   // MTN, Moov, etc.
                'bank_transfer',  // Virement bancaire
                'cheque',         // Chèque
                'card',           // Carte bancaire
                'trade_in',       // Troc (valeur du produit échangé)
            ]);

            // ─── Détails mobile money / virement ─────────────────
            $table->string('transaction_reference')->nullable()
                ->comment('Référence de la transaction mobile money ou virement');
            $table->string('mobile_number')->nullable()
                ->comment('Numéro mobile money');
            $table->string('bank_name')->nullable()
                ->comment('Nom de la banque si virement ou chèque');

            // ─── Date et notes ────────────────────────────────────
            $table->date('payment_date')
                ->comment('Date du paiement');
            $table->text('notes')->nullable();

            // ─── Traçabilité ──────────────────────────────────────
            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();

            $table->timestamps();

            // ─── Index ────────────────────────────────────────────
            $table->index(['sale_id', 'payment_date']);
            $table->index('payment_method');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
