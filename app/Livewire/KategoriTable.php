<?php

namespace App\Livewire;

use App\Models\Kategori;
use Livewire\Component;

class KategoriTable extends Component
{

    public $search = '';

    protected $listeners = ['search-updated' => 'setSearch'];
    public function render()
    {
        return view('livewire.kategori-table', [
            'kategori' => Kategori::with('translations')
            ->whereHas('translations', function($query){
                $query->where('kategori_name','like','%'. $this->search .'%');
            })->paginate(10),
        ])
        ->layout('layouts.admin');
    }

    public function delete(Kategori $kategori)
    {
        $kategori->delete();
        session()->flash('success','Data Kategori Berhasil di Hapus');
    }
}
