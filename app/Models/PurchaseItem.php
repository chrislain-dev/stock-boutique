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
