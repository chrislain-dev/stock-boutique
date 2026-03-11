<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'supplier_id',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'payment_method',
        'transaction_reference',
        'purchase_date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount'   => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'purchase_date'  => 'date',
        'due_date'       => 'date',
    ];

    // ─── Boot — auto-générer la référence ─────────────────────
    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            $purchase->reference = static::generateReference();
        });
    }

    // ─── Relations ────────────────────────────────────────────
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'moveable');
    }

    // ─── Accesseurs ───────────────────────────────────────────
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    // ─── Méthodes statiques ───────────────────────────────────
    public static function generateReference(): string
    {
        $prefix = config('boutique.prefixes.achat', 'ACH');
        $year   = now()->year;
        $count  = static::withTrashed()
            ->whereYear('created_at', $year)
            ->count() + 1;
        return "{$prefix}-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeToday($query)
    {
        return $query->whereDate('purchase_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year);
    }
}
