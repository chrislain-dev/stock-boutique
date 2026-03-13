<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class Receipt extends Component
{
    public Sale $sale;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load([
            'reseller',
            'createdBy',
            'items.productModel.brand',
            'items.product',
            'payments',
            'tradeInProduct.productModel',
        ]);
    }

    public function render()
    {
        return view('livewire.sales.receipt')
            ->layout('layouts.receipt');
    }
}
