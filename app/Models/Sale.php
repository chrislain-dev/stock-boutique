<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'customer_type',
        'reseller_id',
        'customer_name',
        'customer_phone',
        'total_amount',
        'paid_amount',
        'payment_status',
        'sale_status',
        'is_trade_in',
        'trade_in_product_id',
        'trade_in_value',
        'trade_in_notes',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount'    => 'integer',
        'paid_amount'     => 'integer',
        'trade_in_value'  => 'integer',
        'payment_status'  => PaymentStatus::class,
        'is_trade_in'     => 'boolean',
        'due_date'        => 'date',
    ];

    // ─── Boot — auto-générer la référence ─────────────────────
    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            $sale->reference = static::generateReference();
            // Calcul automatique du statut paiement initial
            $sale->payment_status = static::computePaymentStatus(
                $sale->paid_amount,
                $sale->total_amount
            );
        });

        static::updating(function (Sale $sale) {
            // Validation: paid_amount must not exceed total_amount
            if ($sale->paid_amount > $sale->total_amount) {
                throw new \Exception(
                    "Le montant payé ({$sale->paid_amount}) ne peut pas dépasser le montant total ({$sale->total_amount})."
                );
            }

            // Validation: paid_amount must be non-negative
            if ($sale->paid_amount < 0) {
                throw new \Exception('Le montant payé doit être positif ou zéro.');
            }

            // Validation: total_amount must be positive
            if ($sale->total_amount <= 0) {
                throw new \Exception('Le montant total doit être positif.');
            }

            $sale->payment_status = static::computePaymentStatus(
                $sale->paid_amount,
                $sale->total_amount
            );

            // Mettre à jour le solde dû du revendeur
            if ($sale->isDirty('paid_amount') && $sale->reseller_id) {
                $sale->reseller->update([
                    'solde_du' => $sale->reseller->sales()
                        ->where('payment_status', '!=', PaymentStatus::PAID->value)
                        ->sum(DB::raw('total_amount - paid_amount')),
                ]);
            }
        });

        static::saving(function (Sale $sale) {
            // Validation on both create and update
            if ($sale->paid_amount > $sale->total_amount) {
                throw new \Exception(
                    "Le montant payé ({$sale->paid_amount}) ne peut pas dépasser le montant total ({$sale->total_amount})."
                );
            }

            if ($sale->paid_amount < 0) {
                throw new \Exception('Le montant payé doit être positif ou zéro.');
            }

            if ($sale->total_amount <= 0) {
                throw new \Exception('Le montant total doit être positif.');
            }
        });
    }

    // ─── Relations ────────────────────────────────────────────
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function tradeInProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'trade_in_product_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'moveable');
    }

    // ─── Accesseurs ───────────────────────────────────────────
    public function getRemainingAmountAttribute(): int
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !$this->is_fully_paid;
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', PaymentStatus::PAID->value);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('payment_status', '!=', PaymentStatus::PAID->value);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // ─── Méthodes statiques ───────────────────────────────────
    public static function generateReference(): string
    {
        $prefix = config('boutique.prefixes.vente', 'VTE');
        $year   = now()->year;
        $count  = static::withTrashed()
            ->whereYear('created_at', $year)
            ->count() + 1;
        return "{$prefix}-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public static function computePaymentStatus(
        int $paid,
        int $total
    ): PaymentStatus {
        if ($paid >= $total) return PaymentStatus::PAID;
        if ($paid > 0)       return PaymentStatus::PARTIAL;
        return PaymentStatus::UNPAID;
    }
}
