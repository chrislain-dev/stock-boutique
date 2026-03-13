<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CreanceEnRetard extends Notification
{
    use Queueable;

    public function __construct(public Sale $sale) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $client = $this->sale->customer_type === 'reseller'
            ? $this->sale->reseller?->name
            : ($this->sale->customer_name ?: 'Client anonyme');

        return [
            'title'   => 'Créance en retard',
            'message' => "{$client} — " . number_format($this->sale->remaining_amount, 0, ',', ' ') . " F en retard",
            'icon'    => 'o-clock',
            'color'   => 'error',
            'url'     => route('sales.show', $this->sale),
        ];
    }
}
