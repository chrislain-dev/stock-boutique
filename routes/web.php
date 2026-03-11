<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Authentification ─────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // ─── Dashboard ────────────────────────────────────────────
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // ─── Catalogue ────────────────────────────────────────────
    Route::get('/products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/product-models', \App\Livewire\ProductModels\Index::class)->name('product-models.index');
    Route::get('/brands', \App\Livewire\Brands\Index::class)->name('brands.index');

    // ─── Ventes ───────────────────────────────────────────────
    Route::get('/sales', \App\Livewire\Sales\Index::class)->name('sales.index');
    Route::get('/sales/create', \App\Livewire\Sales\Create::class)->name('sales.create');
    Route::get('/resellers', \App\Livewire\Resellers\Index::class)->name('resellers.index');

    // ─── Stock ────────────────────────────────────────────────
    Route::get('/purchases', \App\Livewire\Purchases\Index::class)->name('purchases.index');
    Route::get('/stock-movements', \App\Livewire\StockMovements\Index::class)->name('stock-movements.index');
    Route::get('/suppliers', \App\Livewire\Suppliers\Index::class)->name('suppliers.index');

    // ─── Admin ────────────────────────────────────────────────
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/activity-logs', \App\Livewire\ActivityLogs\Index::class)->name('activity-logs.index');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
    Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings.index');

});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
