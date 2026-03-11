<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'mobile_number',
        'bank_name',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'payment_date'   => 'date',
    ];

    // ─── Boot — mettre à jour paid_amount sur la vente ────────
    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $sale = $payment->sale;
            $totalPaid = $sale->payments()->sum('amount');
            $sale->update(['paid_amount' => $totalPaid]);
        });

        static::deleted(function (Payment $payment) {
            $sale = $payment->sale;
            $totalPaid = $sale->payments()->sum('amount');
            $sale->update(['paid_amount' => $totalPaid]);
        });
    }

    // ─── Relations ────────────────────────────────────────────
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeByMethod($query, PaymentMethod $method)
    {
        return $query->where('payment_method', $method->value);
    }
}
