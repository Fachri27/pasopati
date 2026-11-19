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
     * Tantangan masuk tanpa email: perangkat sendiri yang memilihkan kunci
     * (passkey) yang tersimpan untuk situs ini.
     */
    public function options(AssertionRequest $request): Responsable
    {
        return $request->toVerify();
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
