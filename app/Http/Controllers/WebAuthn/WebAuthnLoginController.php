<?php

namespace App\Http\Controllers\WebAuthn;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

class WebAuthnLoginController
{
    /**
     * Tantangan masuk. Tanpa email, perangkat sendiri yang memilihkan kunci
     * (passkey) tersimpan untuk situs ini. Kalau email diisi, tantangan
     * dibatasi pada kunci milik akun itu — dibutuhkan untuk kunci yang hanya
     * tersimpan di perangkat (tidak bisa ditemukan tanpa tahu akunnya).
     */
    public function options(AssertionRequest $request): Responsable
    {
        $email = $request->validate(['email' => 'nullable|email|string'])['email'] ?? null;

        return $request->toVerify($email ? ['email' => $email] : null);
    }

    /**
     * Sama seperti masuk dengan kata sandi, hanya akun CMS (admin/editor)
     * yang boleh masuk; akun komentar pembaca tidak punya akses ke sini.
     */
    public function login(AssertedRequest $request): Response
    {
        $berhasil = $request->login(callbacks: fn (User $pengguna) => in_array($pengguna->role, ['admin', 'editor'], true));

        return response()->noContent($berhasil ? 204 : 422);
    }
}
