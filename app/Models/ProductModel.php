<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'brand_id',
        'model_number',
        'category',
        'condition',
        'description',
        'image_url',
        'is_serialized',
        'color',
        'ram_gb',
        'storage_gb',
        'storage_type',
        'network',
        'sim_type',
        'screen_size',
        'cpu',
        'cpu_generation',
        'gpu',
        'screen_size_pc',
        'screen_resolution',
        'os',
        'battery',
        'pc_type',
        'connectivity',
        'stylus_support',
        'accessory_type',
        'compatibility',
        'connector_type',
        'quantity_stock',
        'quantity_sold',
        'stock_minimum',
        'default_purchase_price',
        'default_client_price',
        'default_reseller_price',
        'is_active',
    ];

    protected $casts = [
        'category'               => ProductCategory::class,
        'condition'              => \App\Enums\ProductCondition::class,
        'is_serialized'          => 'boolean',
        'is_active'              => 'boolean',
        'ram_gb'                 => 'integer',
        'storage_gb'             => 'integer',
        'quantity_stock'         => 'integer',
        'quantity_sold'          => 'integer',
        'stock_minimum'          => 'integer',
        'default_purchase_price' => 'integer',
        'default_client_price'   => 'integer',
        'default_reseller_price' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_model_id', 'id');
    }

    // ─── Accesseurs ───────────────────────────────────────────

    // Nom complet avec specs — ex: "Apple iPhone 15 Pro 256GB Noir Titane"
    public function getFullNameAttribute(): string
    {
        return match ($this->category) {
            ProductCategory::TELEPHONE,
            ProductCategory::TABLET => implode(' ', array_filter([
                $this->brand?->name,
                $this->name,
                $this->storage_gb ? $this->storage_gb . 'GB' : null,
                $this->color,
            ])),
            ProductCategory::PC => implode(' ', array_filter([
                $this->brand?->name,
                $this->name,
                $this->ram_gb ? $this->ram_gb . 'GB RAM' : null,
                $this->storage_gb ? $this->storage_gb . 'GB ' . $this->storage_type : null,
            ])),
            ProductCategory::ACCESSORY => implode(' ', array_filter([
                $this->brand?->name,
                $this->name,
                $this->color,
            ])),
            default => $this->name,
        };
    }

    // Stock disponible (sérialisés ou accessoires)
    public function getAvailableStockAttribute(): int
    {
        if ($this->is_serialized) {
            return $this->products()
                ->where('state', 'available')
                ->count();
        }
        return $this->quantity_stock;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_stock <= $this->stock_minimum;
    }

    public function getDisplayLabelAttribute(): string
    {
        $parts = [];

        $parts[] = $this->brand->name . ' ' . $this->name;

        if ($this->storage_gb) {
            $parts[] = $this->storage_gb >= 1000
                ? ($this->storage_gb / 1000) . 'TB'
                : $this->storage_gb . 'GB';
        }

        if ($this->category->value === 'pc' && $this->ram_gb) {
            $parts[] = $this->ram_gb . 'GB RAM';
        }

        if ($this->color) {
            $parts[] = $this->color;
        }

        $label = implode(' ', $parts);

        if ($this->condition) {
            $label .= ' - ' . strtoupper($this->condition->label());
        }

        return $label;
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, ProductCategory $category)
    {
        return $query->where('category', $category->value);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_stock <= stock_minimum')
            ->where('is_serialized', false);
    }
}
