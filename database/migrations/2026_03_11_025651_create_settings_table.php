<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // ─── Clé / Valeur ─────────────────────────────────────
            $table->string('key')->unique()
                ->comment('Ex: boutique.nom, theme.primary');
            $table->text('value')->nullable()
                ->comment('Valeur du paramètre');
            $table->string('type')->default('string')
                ->comment('string, boolean, integer, color, image');

            // ─── Groupement pour l'interface ──────────────────────
            $table->string('group')->default('general')
                ->comment('general, theme, vente, notifications');

            // ─── Affichage dans l'interface ───────────────────────
            $table->string('label')
                ->comment('Libellé affiché dans les paramètres');
            $table->text('description')->nullable()
                ->comment('Explication du paramètre');
            $table->boolean('is_public')->default(false)
                ->comment('Visible par les vendeurs ou admin uniquement');

            $table->timestamps();

            // ─── Index ────────────────────────────────────────────
            $table->index('group');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
