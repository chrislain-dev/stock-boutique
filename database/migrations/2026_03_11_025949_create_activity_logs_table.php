<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // ─── Qui a fait l'action ──────────────────────────────
            $table->foreignId('user_id')
                ->constrained()->cascadeOnDelete();

            // ─── Type d'action ────────────────────────────────────
            $table->enum('action', [
                'create',    // Création
                'update',    // Modification
                'delete',    // Suppression
                'restore',   // Restauration (soft delete)
                'login',     // Connexion
                'logout',    // Déconnexion
                'export',    // Téléchargement/export
            ]);

            // ─── Sur quel modèle ──────────────────────────────────
            $table->string('model_type')->nullable()
                ->comment('Ex: App\Models\Sale, App\Models\Product');
            $table->unsignedBigInteger('model_id')->nullable()
                ->comment('ID de l\'enregistrement concerné');

            // ─── Détail de l'action ───────────────────────────────
            $table->string('description')
                ->comment('Ex: Vente VTE-2024-00001 créée');
            $table->json('old_values')->nullable()
                ->comment('Valeurs avant modification');
            $table->json('new_values')->nullable()
                ->comment('Valeurs après modification');

            // ─── Contexte technique ───────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // ─── Immuable — jamais de updated_at ─────────────────
            $table->timestamp('created_at');

            // ─── Index ────────────────────────────────────────────
            $table->index('user_id');
            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
