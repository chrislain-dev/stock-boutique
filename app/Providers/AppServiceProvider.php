<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Observers\PaymentObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Purchase::observe(PurchaseObserver::class);
        Sale::observe(SaleObserver::class);
        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
