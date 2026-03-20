<?php

namespace App\Jobs;

use App\Services\WhatsAppErrorNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $message,
        public string $key = ''
    ) {}

    public function handle(): void
    {
        WhatsAppErrorNotifier::send($this->message, $this->key);
    }
}
