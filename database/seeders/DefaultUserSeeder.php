<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek kalau belum ada user dengan email ini
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Fachri',
                'email' => 'admin@example.com',
                'password' => Hash::make('123123'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
