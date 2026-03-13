<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\User;
use App\Notifications\CreanceEnRetard;
use App\Enums\PaymentStatus;
use Illuminate\Console\Command;

class CheckCreancesEnRetard extends Command
{
    protected $signature   = 'boutique:check-creances';
    protected $description = 'Notifie les admins des créances en retard';

    public function handle(): void
    {
        $ventes = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('sale_status', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['reseller'])
            ->get();

        if ($ventes->isEmpty()) {
            $this->info('Aucune créance en retard.');
            return;
        }

        $admins = User::admins()->get();

        foreach ($ventes as $vente) {
            foreach ($admins as $admin) {
                // Éviter les doublons — ne notifier qu'une fois par jour
                $alreadyNotified = $admin->notifications()
                    ->where('type', CreanceEnRetard::class)
                    ->where('data->url', 'like', "%{$vente->id}%")
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadyNotified) {
                    $admin->notify(new CreanceEnRetard($vente));
                }
            }
        }

        $this->info("Notifications envoyées pour {$ventes->count()} créance(s).");
    }
}
