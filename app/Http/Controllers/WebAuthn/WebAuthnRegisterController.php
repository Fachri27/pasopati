<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnRegisterController
{
    /**
     * Tantangan untuk perangkat. Yang diminta adalah authenticator bawaan
     * perangkat ini (Touch ID, Face ID, Windows Hello) lewat
     * authenticatorAttachment=platform dan petunjuk client-device, supaya
     * peramban tidak menawarkan kunci keamanan USB atau ponsel lain lebih
     * dulu. Peramban tetap memutuskan tempat penyimpanannya (mis. Chrome bisa
     * menawarkan Google Password Manager); situs tidak bisa memaksa itu.
     * Verifikasi pengguna (sidik jari/wajah/PIN) selalu diminta. Kunci
     * disimpan di perangkat bila memungkinkan (residentKey=preferred).
     */
    public function options(AttestationRequest $request): Responsable
    {
        $opsi = $request->secureRegistration()->toCreate();

        $opsi->set('authenticatorSelection.authenticatorAttachment', 'platform');
        $opsi->set('hints', ['client-device']);

        return $opsi;
    }

    public function register(AttestedRequest $request): Response
    {
        $request->validate(['alias' => 'nullable|string|max:60']);

        $request->save(['alias' => $request->input('alias') ?: 'Perangkat saya']);

        return response()->noContent();
    }
}
