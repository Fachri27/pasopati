<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);


        // Cek email & password valid
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard')
                             ->with('success', 'Login berhasil!');
        }

        // Kalau gagal login
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                         ->with('success', 'Anda sudah logout.');
    }
}
