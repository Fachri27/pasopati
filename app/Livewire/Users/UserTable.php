<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class UserTable extends Component
{

    public $search = '';
    public function render()
    {
        return view('livewire.users.user-table', [
            'users' => User::where('name', 'like', '%' . $this->search . '%')->latest()->paginate(5), 
                    
        ])
        ->layout('layouts.admin');
    }

    public function delete(User $user)
    {
        $user->delete();
        session()->flash('success', 'User berhasil di hapus');
    }
}
