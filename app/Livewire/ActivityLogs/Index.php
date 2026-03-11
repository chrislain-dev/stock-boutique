<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.activity-logs.index')
            ->layout('layouts.app', ['title' => 'Activités']);
    }
}
