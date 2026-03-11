<?php

namespace App\Livewire\Brands;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.brands.index')
            ->layout('layouts.app', ['title' => 'Marques']);
    }
}
