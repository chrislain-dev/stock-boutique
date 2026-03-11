<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    // ─── Immuable — pas d'updated_at ─────────────────────────
    const UPDATED_AT = null;

    protected $fillable = [
        'product_model_id',
        'product_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'location_from',
        'location_to',
        'moveable_type',
        'moveable_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'type'            => StockMovementType::class,
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
        'created_at'      => 'datetime',
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

    public function moveable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeByType($query, StockMovementType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
