<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    public $userId;

    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $role;

    public $image;

    public $existing_image;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email'.($this->userId ? ','.$this->userId : ''),
            'role' => 'required|in:admin,editor',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        if (! $this->userId) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'confirmed|min:6';
        }

        return $rules;
    }

    public function mount($userId = null)
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->existing_image = $user->image;
        }
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->userId) {
            // Update
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'password' => $this->password ? Hash::make($this->password) : $user->password,
            ]);
        } else {
            // Create
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'password' => Hash::make($validatedData['password']),
            ]);
            $this->userId = $user->id;
        }

        // Handle file upload
        if ($this->image) {
            if ($this->existing_image && \Storage::disk('public')->exists($this->existing_image)) {
                \Storage::disk('public')->delete($this->existing_image);
            }
            $path = $this->image->store('profile', 'public');
            $user->update(['image' => $path]);
            $this->existing_image = $path;
        }

        session()->flash('success', $this->userId ? 'User berhasil diupdate!' : 'User berhasil dibuat!');

        // ✅ Redirect sesuai role
        if ($user->role === 'admin') {
            return redirect()->route('user.index');
        }

        if ($user->role === 'editor') {
            return redirect()->route('dashboard');
        }
    }

    public function render()
    {
        return view('livewire.users.user-form')
            ->layout('layouts.admin');
    }
}
