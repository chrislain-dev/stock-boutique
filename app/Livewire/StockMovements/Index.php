<?php

namespace App\Livewire\StockMovements;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.stock-movements.index')
            ->layout('layouts.app', ['title' => 'Mouvements']);
    }
}
