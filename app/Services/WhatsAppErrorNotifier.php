<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppErrorNotifier
{
    public static function send(string $message, ?string $key = null): void
    {
        $phone  = config('services.callmebot.phone');
        $apikey = config('services.callmebot.apikey');

        if (!$phone || !$apikey) return;

        // Déduplication — même erreur max 1 fois par 10 minutes
        if ($key) {
            $cacheKey = 'whatsapp_notif_' . md5($key);
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) return;
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(10));
        }

        try {
            Http::timeout(5)->get('https://api.callmebot.com/whatsapp.php', [
                'phone'  => $phone,
                'text'   => $message,
                'apikey' => $apikey,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp notifier failed: ' . $e->getMessage());
        }
    }
}
