<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NouvelleVente extends Notification
{
    use Queueable;

    public function __construct(public Sale $sale) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Nouvelle vente',
            'message' => "Vente {$this->sale->reference} — " . number_format($this->sale->total_amount, 0, ',', ' ') . ' F',
            'icon'    => 'o-shopping-bag',
            'color'   => 'success',
            'url'     => route('sales.show', $this->sale),
        ];
    }
}
