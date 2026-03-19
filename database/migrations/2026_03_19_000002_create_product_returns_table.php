<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_returns', function (Blueprint $table) {
            $table->id();

            // Produit défectueux retourné par le client
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // Vente d'origine
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();

            // Produit de remplacement envoyé par le fournisseur (nullable au départ)
            $table->foreignId('replacement_product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();

            // Raison du retour
            $table->text('reason');

            // Notes internes
            $table->text('notes')->nullable();

            // Statut du retour
            // pending          → déclaré, pas encore envoyé au fournisseur
            // sent_to_supplier → envoyé au fournisseur
            // replacement_received → remplacement reçu et mis en stock
            $table->string('status')->default('pending');

            // Qui a déclaré le retour
            $table->foreignId('declared_by')
                  ->constrained('users');

            // Qui a enregistré la réception du remplacement
            $table->foreignId('replaced_by')
                  ->nullable()
                  ->constrained('users');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('replaced_at')->nullable();

            $table->timestamps();
        });

        // Contrainte CHECK sur le statut
        DB::statement("
            ALTER TABLE product_returns
            ADD CONSTRAINT product_returns_status_check
            CHECK (status IN ('pending', 'sent_to_supplier', 'replacement_received'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_returns');
    }
};
