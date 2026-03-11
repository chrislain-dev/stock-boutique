<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_model_id',
        'product_id',
        'quantity',
        'unit_price',
        'purchase_price_snapshot',
        'discount',
        'line_total',
    ];

    protected $casts = [
        'quantity'                 => 'integer',
        'unit_price'               => 'decimal:2',
        'purchase_price_snapshot'  => 'decimal:2',
        'discount'                 => 'decimal:2',
        'line_total'               => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────
    public function getProfitAttribute(): float
    {
        return $this->line_total - ($this->purchase_price_snapshot * $this->quantity);
    }
}
