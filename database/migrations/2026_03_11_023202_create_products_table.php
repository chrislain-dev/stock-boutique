<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_model_id')->constrained()->restrictOnDelete();

            // ─── Identification (serialized products only) ───────
            $table->string('imei')->unique()->nullable()
                ->comment('IMEI pour les téléphones');
            $table->string('serial_number')->unique()->nullable()
                ->comment('Numéro de série pour PC/tablettes');

            // ─── State (finite state machine) ────────────────────
            $table->enum('state', [
                'available',
                'sold',
                'reserved',
                'returned',
                'defective',
                'in_repair',
            ])->default('available');

            $table->enum('location', [
                'store',
                'transit',
                'client',
                'reseller',
                'repair_shop',
            ])->default('store');

            // ─── Physical condition ──────────────────────────────────
            $table->text('defects')->nullable()
                ->comment('Défauts constatés sur cette unité spécifique');
            $table->text('notes')->nullable();

            // ─── Prices (specific to this unit) ──────────────────
            $table->unsignedBigInteger('purchase_price')
                ->comment('Prix d\'achat réel de cette unité');
            $table->unsignedBigInteger('client_price')
                ->comment('Prix de vente pour les clients');
            $table->unsignedBigInteger('reseller_price')
                ->comment('Prix de vente pour les revendeurs');

            // ─── Purchase info ───────────────────────────────────
            $table->date('purchase_date')->nullable();
            $table->foreignId('supplier_id')->nullable()
                ->constrained('suppliers')->nullOnDelete();

            // ─── Traceability ────────────────────────────────────
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ─── Indexes ─────────────────────────────────────────
            $table->index(['product_model_id', 'state', 'location']);
            $table->index('state');
            $table->index('location');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
