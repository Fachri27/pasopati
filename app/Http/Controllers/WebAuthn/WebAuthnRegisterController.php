<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnRegisterController
{
    /**
     * Tantangan untuk perangkat. userless(): kuncinya disimpan di perangkat
     * (discoverable), jadi nanti bisa masuk tanpa mengetik email, dan
     * verifikasi pengguna (sidik jari/wajah/PIN perangkat) selalu diminta.
     * Perangkat yang sama tidak bisa didaftarkan dua kali.
     */
    public function options(AttestationRequest $request): Responsable
    {
        return $request->userless()->toCreate();
    }

    public function register(AttestedRequest $request): Response
    {
        $request->validate(['alias' => 'nullable|string|max:60']);

        $request->save(['alias' => $request->input('alias') ?: 'Perangkat saya']);

        return response()->noContent();
    }
}
