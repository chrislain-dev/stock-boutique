<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', function () {
        ActivityLogService::log(
            action: 'logout',
            description: 'Déconnexion — ' . Auth::user()->name,
            model: Auth::user(),
        );
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // ─── Dashboard ────────────────────────────────────────────
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // ─── Produits ─────────────────────────────────────────────
    Route::get('/products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Products\Create::class)->name('products.create');
    Route::get('/products/{product}', \App\Livewire\Products\Show::class)->name('products.show');
    Route::get('/products/{product}/edit', \App\Livewire\Products\Edit::class)->name('products.edit');

    Route::get('/product-models', \App\Livewire\ProductModels\Index::class)->name('product-models.index');
    Route::get('/brands', \App\Livewire\Brands\Index::class)->name('brands.index');

    // ─── Ventes ───────────────────────────────────────────────
    Route::get('/sales', \App\Livewire\Sales\Index::class)->name('sales.index');
    Route::get('/sales/create', \App\Livewire\Sales\Create::class)->name('sales.create');
    Route::get('/sales/{sale}', \App\Livewire\Sales\Show::class)->name('sales.show');
    Route::get('/resellers', \App\Livewire\Resellers\Index::class)->name('resellers.index');

    Route::get('/sales/{sale}/receipt', function (\App\Models\Sale $sale) {
        // Optionnel : vérifier que l'utilisateur a accès à cette vente
        abort_if(
            ! Auth::check(),
            403
        );

        $sale->load(['items.productModel', 'items.product', 'payments', 'reseller', 'tradeInProduct', 'createdBy']);

        return view('livewire.sales.receipt', compact('sale'));
    })->name('sales.receipt');

    // ─── Stock ────────────────────────────────────────────────
    Route::get('/purchases', \App\Livewire\Purchases\Index::class)->name('purchases.index');
    Route::get('/purchases/create', \App\Livewire\Purchases\Create::class)->name('purchases.create');
    Route::get('/purchases/{purchase}', \App\Livewire\Purchases\Show::class)->name('purchases.show');
    Route::get('/purchases/{purchase}/edit', \App\Livewire\Purchases\Edit::class)->name('purchases.edit');

    Route::get('/stock-movements', \App\Livewire\StockMovements\Index::class)->name('stock-movements.index');
    Route::get('/suppliers', \App\Livewire\Suppliers\Index::class)->name('suppliers.index');

    // ─── Admin ────────────────────────────────────────────────
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/reports/print/{from}/{to}', \App\Http\Controllers\ReportPrintController::class)->name('reports.print');
    Route::get('/reports/excel/{from}/{to}', [\App\Http\Controllers\ReportController::class, 'excel'])->name('reports.excel');
    Route::get('/reports/pdf/{from}/{to}',   [\App\Http\Controllers\ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/activity-logs', \App\Livewire\ActivityLogs\Index::class)->name('activity-logs.index');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
    Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings.index');

    Route::get('/reprises', \App\Livewire\Reprises\Index::class)->name('reprises.index');

    Route::get('/retours-fournisseur', \App\Livewire\SupplierReturns\Index::class)->name('supplier-returns.index');
});

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : view('welcome');
});
