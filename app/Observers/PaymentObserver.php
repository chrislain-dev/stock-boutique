<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaiementRecu;
use App\Services\ActivityLogService;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $payment->load('sale');

        ActivityLogService::log(
            action: 'create',
            description: "Paiement de " . number_format($payment->amount, 0, ',', ' ') . " F reçu — {$payment->sale->reference}",
            model: $payment,
        );

        User::admins()->each(fn($admin) => $admin->notify(new PaiementRecu($payment)));
    }
}
