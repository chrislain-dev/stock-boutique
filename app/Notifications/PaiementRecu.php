<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaiementRecu extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Paiement reçu',
            'message' => "Paiement de " . number_format($this->payment->amount, 0, ',', ' ') . " F — {$this->payment->sale->reference}",
            'icon'    => 'o-banknotes',
            'color'   => 'success',
            'url'     => route('sales.show', $this->payment->sale_id),
        ];
    }
}
