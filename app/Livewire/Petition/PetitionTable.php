<?php

namespace App\Livewire\Petition;

use App\Models\Petition;
use Livewire\Component;
use Livewire\WithPagination;

class PetitionTable extends Component
{
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    protected $queryString = ['search', 'filterStatus'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $petition = Petition::findOrFail($id);
        $petition->signatures()->delete();
        $petition->translations()->delete();
        $petition->delete();

        session()->flash('success', 'Petisi berhasil dihapus.');
    }

    public function render()
    {
        $query = Petition::with('translations');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('target_name', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($t) use ($search) {
                        $t->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $petitions = $query->latest()->paginate(10);

        $totalActive = Petition::where('status', 'active')->count();
        $totalSignaturesThisMonth = \App\Models\PetitionSignature::where('is_verified', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $totalSignaturesAll = \App\Models\PetitionSignature::where('is_verified', true)->count();

        return view('livewire.petition.petition-table', [
            'petitions' => $petitions,
            'totalActive' => $totalActive,
            'totalSignaturesThisMonth' => $totalSignaturesThisMonth,
            'totalSignaturesAll' => $totalSignaturesAll,
        ])->layout('layouts.admin');
    }
}
