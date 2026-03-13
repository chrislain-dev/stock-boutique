<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        $this->purchase = $purchase->load([
            'supplier',
            'createdBy',
            'items.productModel.brand',
            'items.product',
        ]);
    }

    public function render()
    {
        return view('livewire.purchases.show')
            ->layout('layouts.app', ['title' => 'Achat ' . $this->purchase->reference]);
    }
}
