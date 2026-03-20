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
        'amount'         => 'integer',
        'payment_method' => PaymentMethod::class,
        'payment_date'   => 'date',
    ];

    // ─── Boot — mettre à jour paid_amount sur la vente ────────
    protected static function booted(): void
    {
        static::saving(function (Payment $payment) {
            if ($payment->amount <= 0) {
                throw new \Exception('Le montant du paiement doit être positif.');
            }

            // Verify that total paid on this sale won't exceed total_amount
            $sale = $payment->sale;
            $currentTotalPaid = $sale->payments()->sum('amount');

            // If updating existing payment, exclude current payment from total
            if ($payment->exists) {
                $currentTotalPaid -= $payment->getOriginal('amount');
            }

            $newTotalPaid = $currentTotalPaid + $payment->amount;

            if ($newTotalPaid > $sale->total_amount) {
                throw new \Exception(
                    "Le total des paiements ({$newTotalPaid}) dépasse le montant de la vente ({$sale->total_amount})."
                );
            }
        });

        static::created(function (Payment $payment) {
            $sale = $payment->sale;
            $totalPaid = $sale->payments()->sum('amount');
            $sale->update(['paid_amount' => $totalPaid]);
        });

        static::updated(function (Payment $payment) {
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
