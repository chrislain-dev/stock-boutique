<?php

namespace App\Livewire\Resellers;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.resellers.index')
            ->layout('layouts.app', ['title' => 'Revendeurs']);
    }
}
