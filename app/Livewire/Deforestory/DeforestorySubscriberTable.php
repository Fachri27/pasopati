<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestorySubscriber;
use Livewire\Component;
use Livewire\WithPagination;

class DeforestorySubscriberTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    protected $updatesQueryString = ['search', 'status'];

    public function render()
    {
        $subscribers = DeforestorySubscriber::query()
            ->with('case.translations')
            ->when($this->search, function ($query) {
                $query->where('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('active', $this->status === 'active');
            })
            ->orderByDesc('subscribed_at')
            ->paginate(20);

        return view('livewire.deforestory.deforestory-subscriber-table', [
            'subscribers' => $subscribers,
            'total' => DeforestorySubscriber::count(),
            'activeCount' => DeforestorySubscriber::active()->count(),
        ])->layout('layouts.admin');
    }

    public function toggle(DeforestorySubscriber $subscriber): void
    {
        $subscriber->update(['active' => ! $subscriber->active]);
        session()->flash('success', 'Status subscriber diperbarui.');
    }

    public function delete(DeforestorySubscriber $subscriber): void
    {
        $subscriber->delete();
        session()->flash('success', 'Subscriber berhasil dihapus.');
    }
}
