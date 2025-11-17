<?php

namespace App\Livewire;

use App\Models\Kategori;
use App\Models\KategoriTranslation;
use Livewire\Component;

class KategoriForm extends Component
{
    public $kategori_id;
    public $kategori_name_id;
    public $kategori_name_en;

    public $isEdit = false;

    public function mount($kategoriId = null) {
        if($kategoriId) {
            $this->kategori_id = $kategoriId;
            $this->isEdit = true;

            $kategori = Kategori::with('translations')->findOrFail($kategoriId);

            $this->kategori_name_id = optional(
                $kategori->translations->firstWhere('locale', 'id')
            )->kategori_name;

            $this->kategori_name_en = optional(
                $kategori->translations->firstWhere('locale', 'en')
            )->kategori_name;
        }
    }

    public function save()
    {
        $this->validate([
            'kategori_name_id' => 'required|string|max:100',
            'kategori_name_en' => 'required|string|max:100',
        ]);

         if ($this->isEdit) {
            // UPDATE
            $kategori = Kategori::findOrFail($this->kategori_id);
        } else {
            // CREATE
            $kategori = Kategori::create();
        }

        foreach (['id', 'en'] as $locale) {
            KategoriTranslation::updateOrCreate(
                ['kategori_id' => $kategori->id, 'locale' => $locale],
                ['kategori_name' => $locale === 'id'
                    ? $this->kategori_name_id
                    : $this->kategori_name_en]
            );
        }

            if($this->kategori_name_en === null && $this->kategori_name_id === null) {
                session()->flash('error','Form belum diisi!!');
            }
            
            session()->flash('success', $this->isEdit
                ? 'Kategori berhasil diperbarui.'
                : 'Kategori berhasil ditambahkan.');

        return redirect()->route('kategori.index');
    }
    public function render()
    {
        return view('livewire.kategori-form')
        ->layout('layouts.admin');
    }

    
}
