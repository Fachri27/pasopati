<?php

namespace App\Livewire\Fellowship;

use App\Models\Fellowship;
use Carbon\Carbon;
use Livewire\Component;

class FellowshipTable extends Component
{
    public $search = '';
    public $status;
    public $author;
    public $dataRange;

    protected $updatesQueryString = ['search', 'status', 'author', 'dataRange'];
    public function render()
    {
        $fellowship = Fellowship::with('translations')
        ->whereHas('translations', function($query){
            $query->where('title','like', '%'. $this->search .'%');

            if( $this->status && $this->status !== 'all'){
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

                            $query->whereBetween('start_date', [$start, $end]);
                        } catch (\Exception $e) {
                            \Log::error('Date filter error: '.$e->getMessage());
                        }
                    } elseif (count($dates) === 1) {
                        try {
                            $start = Carbon::parse($dates[0])->startOfDay();

                            $query->where('start_date', $start);
                        } catch (\Exception $e) {
                            \Log::error('Date filter error: '.$e->getMessage());
                        }
                    }
                }
        })->paginate(5);

        return view('livewire.fellowship.fellowship-table', compact('fellowship'))
        ->layout('layouts.admin');
    }

    public function delete(Fellowship $fellapp) {
        $fellapp->delete();
        session()->flash('success','Data Fellowship Berhasil di Hapus');
    }
}
