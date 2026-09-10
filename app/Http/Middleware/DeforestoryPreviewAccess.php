<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dua pintu menuju halaman pratinjau Deforestory.
 *
 * Editor yang sedang login CMS masuk begitu saja. Peninjau luar — narasumber,
 * penyunting lepas, mitra — masuk lewat tautan bertanda tangan yang dibuat
 * editor dan kedaluwarsa sendiri; mereka tidak punya akun, dan membuatkan akun
 * hanya untuk sekali baca jelas berlebihan.
 *
 * Yang tidak membawa keduanya ditolak, bukan diarahkan ke login: tautan
 * pratinjau yang sudah kedaluwarsa harus terbaca sebagai kedaluwarsa, bukan
 * sebagai halaman yang menuntut akun.
 */
class DeforestoryPreviewAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        if ($pengguna && in_array($pengguna->role, ['admin', 'editor'], true)) {
            return $next($request);
        }

        if ($request->hasValidSignature()) {
            return $next($request);
        }

        abort(403, 'Tautan pratinjau tidak berlaku atau sudah kedaluwarsa.');
    }
}
