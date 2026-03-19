<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReturn extends Model
{
    protected $fillable = [
        'product_id',
        'sale_id',
        'replacement_product_id',
        'reason',
        'notes',
        'status',
        'declared_by',
        'replaced_by',
        'sent_at',
        'replaced_at',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'replaced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    public function declaredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declared_by');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }

    public function isPending(): bool      { return $this->status === 'pending'; }
    public function isSent(): bool         { return $this->status === 'sent_to_supplier'; }
    public function isReplaced(): bool     { return $this->status === 'replacement_received'; }
}
