<?php

namespace App\Livewire\Petition;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PetitionSignatureList extends Component
{
    use WithPagination;

    public Petition $petition;

    public $search = '';

    public $filterVerified = '';

    protected $queryString = ['search', 'filterVerified'];

    public function mount($petitionId)
    {
        $this->petition = Petition::with('translations')->findOrFail($petitionId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterVerified()
    {
        $this->resetPage();
    }

    public function deleteComment($signatureId)
    {
        $signature = PetitionSignature::where('petition_id', $this->petition->id)
            ->findOrFail($signatureId);

        $signature->update(['comment' => null]);

        session()->flash('success', 'Komentar berhasil dihapus.');
    }

    public function exportCsv()
    {
        $signatures = PetitionSignature::where('petition_id', $this->petition->id)
            ->where('is_verified', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $locale = app()->getLocale();
        $title = $this->petition->translation($locale)?->title ?? $this->petition->slug;
        $filename = Str::slug($title).'-tanda-tangan.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($signatures) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nama', 'Email', 'Kota', 'Tanggal']);

            foreach ($signatures as $s) {
                fputcsv($handle, [
                    $s->name,
                    $s->email,
                    $s->city ?? '',
                    $s->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function render()
    {
        $query = PetitionSignature::where('petition_id', $this->petition->id);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($this->filterVerified === 'verified') {
            $query->where('is_verified', true);
        } elseif ($this->filterVerified === 'unverified') {
            $query->where('is_verified', false);
        }

        $signatures = $query->latest('created_at')->paginate(20);

        return view('livewire.petition.petition-signature-list', [
            'signatures' => $signatures,
        ])->layout('layouts.admin');
    }
}
