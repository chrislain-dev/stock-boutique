<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockBas extends Notification
{
    use Queueable;

    public function __construct(
        public string $productName,
        public int    $qty,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Stock bas',
            'message' => "{$this->productName} — plus que {$this->qty} en stock",
            'icon'    => 'o-exclamation-triangle',
            'color'   => 'warning',
            'url'     => route('products.index'),
        ];
    }
}
