<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    const UPDATED_AT = null;

    protected $table = 'price_history';

    protected $fillable = [
        'product_model_id',
        'product_id',
        'old_purchase_price',
        'old_client_price',
        'old_reseller_price',
        'new_purchase_price',
        'new_client_price',
        'new_reseller_price',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'old_purchase_price'  => 'decimal:2',
        'old_client_price'    => 'decimal:2',
        'old_reseller_price'  => 'decimal:2',
        'new_purchase_price'  => 'decimal:2',
        'new_client_price'    => 'decimal:2',
        'new_reseller_price'  => 'decimal:2',
        'created_at'          => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
