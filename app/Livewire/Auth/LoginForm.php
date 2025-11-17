<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $load = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function login()
    {
        $this->validate();
        $this->load = true;

        if(Auth::attempt(['email' => $this->email,'password'=> $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('dashboard')
                             ->with('success', 'Login berhasil!');
        }

        $this->loading = false;
        $this->addError('email', 'Email atau password salah.');
    }
    public function render()
    {
        return view('livewire.auth.login-form')->layout('layouts.auth');
    }
}
