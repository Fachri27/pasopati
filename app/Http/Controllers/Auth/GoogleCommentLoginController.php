<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Login Google untuk kolom komentar (Laravel Socialite).
 *
 * Tujuan: commenter login pakai Google supaya nama + avatar otomatis terisi
 * saat berkomentar (form komentar mendeteksi auth()->check()). Tamu tetap
 * boleh komentar tanpa login — ini opsi tambahan.
 *
 * Alur:
 *   1. GET /comment/login/google?intended=<url halaman>  → simpan intended,
 *      redirect ke Google consent.
 *   2. Google callback → /comment/login/google/callback → ambil user Google,
 *      cari/buat User role 'commenter', Auth::login, balik ke intended.
 *   3. GET /comment/logout?intended=<url> → logout, balik ke halaman asal.
 *
 * Keamanan:
 * - User Google disimpan role 'commenter' (BUKAN 'editor') supaya tidak
 *   dapat akses route admin (middleware role:admin,editor).
 * - Akun yang sudah ada (email sama) di-link google_id-nya, bukan dibuat
 *   duplikat. Aman karena Google memverifikasi kepemilikan email.
 */
class GoogleCommentLoginController extends Controller
{
    /** Step 1: simpan URL halaman asal, lalu redirect ke Google. */
    public function redirectToGoogle(Request $request)
    {
        redirect()->setIntendedUrl(
            $this->intendedAman($request, $request->query('intended', url()->previous('/')))
        );

        return Socialite::driver('google')->redirect();
    }

    /** Step 2: callback Google — cari/buat user, login, balik ke intended. */
    public function handleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            // `state` di callback tidak cocok dengan yang tersimpan di sesi.
            // Penyebab lazimnya bukan serangan: sesi kedaluwarsa, callback-nya
            // dimuat ulang/ditekan tombol kembali, atau alur login dimulai di
            // host yang berbeda dengan GOOGLE_REDIRECT_URI — cookie sesi
            // terikat host (localhost dan 127.0.0.1 dihitung berbeda), jadi
            // state-nya tidak pernah ikut terkirim.
            //
            // Dipulangkan ke halaman asal, bukan dibiarkan jadi galat 500:
            // yang gagal cuma satu percobaan masuk, dan mencoba lagi dari awal
            // biasanya langsung berhasil.
            Log::warning('Login Google batal: state tidak cocok.', [
                'host' => $request->getHost(),
                'redirect_uri' => config('services.google.redirect'),
            ]);

            return redirect()
                ->to($this->intendedAman($request, $request->session()->pull('url.intended', '/')))
                ->with('error', 'Sesi masuk kedaluwarsa. Silakan coba masuk lagi.');
        }

        // 1. Cari by google_id (akun yang sudah pernah login Google).
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. Cari by email — link google_id supaya tidak duplikat akun.
        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'image' => $user->image ?: $googleUser->getAvatar(),
                ]);
            }
        }

        // 3. Buat baru sebagai commenter.
        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'image' => $googleUser->getAvatar(),
                'role' => 'commenter',
                'password' => null,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /** Logout commenter, balik ke halaman asal (bukan /login). */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($this->intendedAman($request, $request->query('intended', '/')));
    }

    /**
     * URL kembali yang boleh dipakai setelah login/logout.
     *
     * Dulu pembandingnya config('app.url'). Itu meleset begitu host yang
     * dibuka berbeda dari APP_URL — server dev, staging, akses lewat IP, atau
     * http vs https — sehingga URL asalnya dibuang dan pengguna terlempar ke
     * beranda alih-alih kembali ke halaman tempat ia menekan "Masuk". Yang
     * dibandingkan sekarang host permintaan ini sendiri, jadi selalu tepat
     * tanpa bergantung pada konfigurasi.
     *
     * Tetap menolak tujuan ke luar (open redirect), termasuk bentuk "//host"
     * yang dibaca peramban sebagai URL berskema-sama.
     */
    private function intendedAman(Request $request, ?string $intended): string
    {
        $intended = trim((string) $intended);

        if ($intended === '' || str_starts_with($intended, '//')) {
            return '/';
        }

        if (str_starts_with($intended, '/')) {
            return $intended;
        }

        $host = parse_url($intended, PHP_URL_HOST);

        return $host !== null && strcasecmp($host, $request->getHost()) === 0
            ? $intended
            : '/';
    }
}
