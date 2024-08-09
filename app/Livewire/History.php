<?php

namespace App\Livewire;

use Livewire\Component;

class History extends Component
{
    public function render()
    {
        $title = "History";
        return view('livewire.history')->layout('components.layouts.app', compact('title'));
    }
}
