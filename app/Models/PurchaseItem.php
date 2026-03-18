<?php

namespace App\Models;

use App\Enums\ProductCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_model_id',
        'product_id',
        'quantity',
        'unit_purchase_price',
        'unit_client_price',
        'unit_reseller_price',
        'line_total',
        'condition',
        'notes',
    ];

    protected $casts = [
        'quantity'            => 'integer',
        'unit_purchase_price' => 'decimal:2',
        'unit_client_price'   => 'decimal:2',
        'unit_reseller_price' => 'decimal:2',
        'line_total'          => 'decimal:2',
        'condition'           => ProductCondition::class,
    ];

    // ─── Boot ──────────────────────────────────────────────────
    protected static function booted(): void
    {
        static::saving(function (PurchaseItem $item) {
            if ($item->quantity <= 0) {
                throw new \Exception('La quantité doit être au moins 1.');
            }

            if ($item->unit_purchase_price <= 0) {
                throw new \Exception('Le prix unitaire doit être positif.');
            }

            // Auto-calculate line_total if needed
            if (!$item->line_total || $item->isDirty(['quantity', 'unit_purchase_price'])) {
                $item->line_total = $item->quantity * $item->unit_purchase_price;
            }
        });
    }

    // ─── Relations ────────────────────────────────────────────
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
