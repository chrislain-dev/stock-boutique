<?php

namespace App\Models;

use App\Enums\ProductState;
use App\Enums\ProductLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_model_id',
        'imei',
        'serial_number',
        'state',
        'location',
        'defects',
        'purchase_price',
        'client_price',
        'reseller_price',
        'purchase_date',
        'supplier_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'state'          => ProductState::class,
        'location'       => ProductLocation::class,
        'purchase_price' => 'integer',
        'client_price'   => 'integer',
        'reseller_price' => 'integer',
        'purchase_date'  => 'date',
    ];

    // ─── Boot events ───────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion if product is sold or unavailable
        static::deleting(function ($product) {
            if ($product->state !== ProductState::AVAILABLE) {
                throw new \Exception(
                    "Impossible de supprimer un produit qui n'est pas disponible (État: {$product->state->value})"
                );
            }
        });

        // Validate prices on save
        static::saving(function ($product) {
            if ($product->purchase_price < 0 || $product->client_price < 0 || $product->reseller_price < 0) {
                throw new \Exception('Les prix doivent être positifs.');
            }

            // Les produits en reprise / troc ont des prix de revente à définir
            // lors du reconditionnement — on saute la vérification de cohérence.
            $location = $product->location instanceof ProductLocation
                ? $product->location
                : ProductLocation::tryFrom($product->location);

            if ($location === ProductLocation::REPRISE) {
                return;
            }

            if ($product->client_price < $product->purchase_price) {
                throw new \Exception('Le prix client doit être >= au prix d\'achat.');
            }
        });

        // Validate state transition on update
        static::updating(function ($product) {
            $originalState = $product->getOriginal('state');
            $newState = $product->state;

            if ($originalState instanceof ProductState) {
                $originalStateEnum = $originalState;
            } else {
                $originalStateEnum = ProductState::tryFrom($originalState);
            }

            if ($originalStateEnum && $originalStateEnum !== $newState && !$originalStateEnum->canTransitionTo($newState)) {
                throw new \Exception(
                    "Transition d'état impossible : {$originalState} → {$newState->value}"
                );
            }
        });
    }

    // ─── Relations ────────────────────────────────────────────
    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_id', 'id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────
    public function getIsAvailableAttribute(): bool
    {
        return $this->state === ProductState::AVAILABLE;
    }

    public function getIsSoldAttribute(): bool
    {
        return $this->state === ProductState::SOLD;
    }

    public function getMarginAttribute(): int
    {
        return $this->client_price - $this->purchase_price;
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->purchase_price == 0) return 0;
        return round(($this->margin / $this->purchase_price) * 100, 2);
    }

    public function getIdentifierAttribute(): string
    {
        return $this->imei ?? $this->serial_number ?? 'N/A';
    }

    public function getConditionAttribute(): ?\App\Enums\ProductCondition
    {
        return $this->productModel?->condition;
    }

    // ─── Méthodes métier ──────────────────────────────────────
    public function transitionTo(ProductState $newState): void
    {
        if (!$this->state->canTransitionTo($newState)) {
            throw new \Exception(
                "Transition impossible : {$this->state->label()} → {$newState->label()}"
            );
        }
        $this->update(['state' => $newState]);
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('state', ProductState::AVAILABLE->value);
    }

    public function scopeByState($query, ProductState $state)
    {
        return $query->where('state', $state->value);
    }

    public function scopeByLocation($query, ProductLocation $location)
    {
        return $query->where('location', $location->value);
    }
}
