<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    // Terkunci: properti publik Livewire bisa dipalsukan dari browser, dan
    // save() memakainya untuk memilih akun yang diperbarui.
    #[Locked]
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

    /**
     * Admin boleh mengelola semua akun; editor hanya profilnya sendiri.
     * Tanpa ini editor bisa membuka /user/{id}/edit akun mana pun, lalu
     * mengganti kata sandi atau perannya (termasuk menjadikan diri admin).
     */
    private function izinkanAkses(?int $targetId): void
    {
        $pengguna = auth()->user();

        if ($pengguna?->role === 'admin') {
            return;
        }

        abort_unless($targetId !== null && $pengguna && $pengguna->id === $targetId, 403);
    }

    public function mount($userId = null)
    {
        $this->izinkanAkses($userId ? (int) $userId : null);

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
        $this->izinkanAkses($this->userId ? (int) $this->userId : null);

        $validatedData = $this->validate();

        // Peran hanya boleh diubah admin; editor yang menyunting profilnya
        // sendiri tetap pada perannya.
        if (auth()->user()->role !== 'admin') {
            $validatedData['role'] = auth()->user()->role;
        }

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
