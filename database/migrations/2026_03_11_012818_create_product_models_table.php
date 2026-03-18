<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_models', function (Blueprint $table) {
            $table->id();

            // ─── Identity ────────────────────────────────────────
            $table->string('name');
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('model_number')->nullable();
            $table->enum('category', ['telephone', 'pc', 'tablet', 'accessory', 'sextoys']);
            $table->enum('condition', [
                'sealed',        // Scellé (neuf jamais ouvert)
                'refurbished',   // Reconditionné
                'used',          // Occasion
                'defective',     // Défectueux
            ])->default('sealed');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();

            // ─── Stock management type ───────────────────────────
            $table->boolean('is_serialized')->default(true)
                ->comment('true = IMEI/serial | false = quantity (accessories)');

            // ─── Common specs ────────────────────────────────────
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->unsignedSmallInteger('storage_gb')->nullable();
            $table->string('storage_type')->nullable();     // SSD, HDD, eMMC, NVMe

            // ─── Phone specs ─────────────────────────────────────
            $table->string('network')->nullable();          // 5G, 4G, 3G
            $table->string('sim_type')->nullable();         // Nano, eSIM, Dual SIM
            $table->string('screen_size')->nullable();      // "6.1"

            // ─── PC specs ────────────────────────────────────────
            $table->string('cpu')->nullable();
            $table->string('cpu_generation')->nullable();
            $table->string('gpu')->nullable();
            $table->string('screen_size_pc')->nullable();
            $table->string('screen_resolution')->nullable(); // FHD, QHD, 4K
            $table->string('os')->nullable();
            $table->string('battery')->nullable();
            $table->enum('pc_type', ['laptop', 'desktop', 'all_in_one', 'mini_pc'])
                ->nullable();

            // ─── Tablet specs ────────────────────────────────────
            $table->string('connectivity')->nullable();     // WiFi, WiFi+4G...
            $table->string('stylus_support')->nullable();   // Apple Pencil, S-Pen, None

            // ─── Accessory specs ─────────────────────────────────
            $table->string('accessory_type')->nullable();   // Charger, Case...
            $table->string('compatibility')->nullable();    // iPhone 15, Universal...
            $table->string('connector_type')->nullable();   // USB-C, Lightning...

            // ─── Accessory stock (non-serialized) ────────────────
            $table->unsignedInteger('quantity_stock')->default(0);
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->unsignedInteger('stock_minimum')->default(0);

            // ─── Default prices ──────────────────────────────────
            $table->decimal('default_purchase_price', 10, 2)->nullable();
            $table->decimal('default_client_price', 10, 2)->nullable();
            $table->decimal('default_reseller_price', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // ─── Indexes ─────────────────────────────────────────
            $table->index(['category', 'is_active']);
            $table->index(['brand_id', 'category']);
            $table->index('is_serialized');
            $table->unique(['brand_id', 'name', 'storage_gb', 'ram_gb', 'condition'], 'unique_model_condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_models');
    }
};
