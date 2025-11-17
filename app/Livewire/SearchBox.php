<?php

namespace App\Livewire;

use Livewire\Component;

class SearchBox extends Component
{
    public $search = '';
    public $placeholder = 'Cari data...';
    public $delay = 300; // debounce delay

    public function updatingSearch($value)
    {
        // emit ke parent Livewire agar bisa di-listen dari luar
        $this->dispatch('searchUpdated', $value)->to('*');
    }
    public function render()
    {
        return view('livewire.search-box');
    }
}
