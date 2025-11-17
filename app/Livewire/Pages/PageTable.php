<?php

namespace App\Livewire\Pages;

use App\Models\Page;
use Carbon\Carbon;
use Livewire\Component;

class PageTable extends Component
{
    public $search = '';

    public $status;

    public $author;

    public $start_date;

    public $end_date;

    public $dataRange;

    protected $updatesQueryString = ['search', 'status', 'author', 'start_date', 'end_date', 'dataRange'];


    public function render()
    {
        $pages = Page::with('translations')
            ->whereHas('translations', function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');

                if ($this->status && $this->status !== 'all') {
                    $query->where('status', $this->status);
                }

                if ($this->author === 'me') {
                    $query->where('user_id', auth()->id());
                }

                if ($this->dataRange) {
                    $dates = array_map('trim', explode('to', $this->dataRange));

                    if (count($dates) === 2) {
                        try {
                            $start = Carbon::parse($dates[0])->startOfDay();
                            $end = Carbon::parse($dates[1])->endOfDay();

                            $query->whereBetween('published_at', [$start, $end]);
                        } catch (\Exception $e) {
                            \Log::error('Date filter error: '.$e->getMessage());
                        }
                    } elseif (count($dates) === 1) {
                        try {
                            $start = Carbon::parse($dates[0])->startOfDay();

                            $query->where('published_at', $start);
                        } catch (\Exception $e) {
                            \Log::error('Date filter error: '.$e->getMessage());
                        }
                    }
                }

            })->paginate(5);

        return view('livewire.pages.page-table', compact('pages'))
            ->layout('layouts.admin');
    }

    public function delete(Page $page)
    {
        $page->delete();
        session()->flash('success', 'Page berhasil di hapus');
    }
}
