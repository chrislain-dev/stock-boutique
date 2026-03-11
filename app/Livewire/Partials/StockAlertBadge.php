<?php

namespace App\Livewire\Partials;

use App\Models\ProductModel;
use Livewire\Component;

class StockAlertBadge extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = ProductModel::query()
            ->where('is_serialized', false)
            ->where('is_active', true)
            ->whereRaw('quantity_stock <= stock_minimum')
            ->count();
    }

    public function render()
    {
        return view('livewire.partials.stock-alert-badge');
    }
}
