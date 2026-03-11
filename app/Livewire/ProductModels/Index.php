<?php

namespace App\Livewire\ProductModels;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.product-models.index')
            ->layout('layouts.app', ['title' => 'Modèles']);
    }
}
