<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ─── Supprimer l'ancien champ texte ───────────────────
            $table->dropColumn('supplier');

            // ─── Ajouter la clé étrangère ─────────────────────────
            $table->foreignId('supplier_id')->nullable()
                ->after('purchase_date')
                ->constrained()->nullOnDelete()
                ->comment('Fournisseur auprès de qui ce produit a été acheté');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->string('supplier')->nullable()->after('purchase_date');
        });
    }
};
