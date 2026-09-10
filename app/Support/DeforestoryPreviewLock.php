<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Kunci pratinjau Deforestory — satu password global untuk semua laporan.
 *
 * Satu password, bukan satu per laporan: tautan pratinjau dibagikan ke
 * peninjau luar yang sering menerima beberapa laporan sekaligus, dan meminta
 * mereka mengingat password berbeda untuk tiap tautan hanya akan berakhir
 * dengan password ditulis di badan email yang sama.
 *
 * Yang disimpan di sesi pembaca adalah sidik dari hash-nya, bukan
 * passwordnya. Efek sampingnya diinginkan: mengganti password global otomatis
 * membatalkan semua sesi yang sudah terbuka.
 */
class DeforestoryPreviewLock
{
    private const KUNCI_SESI = 'deforestory_preview_unlocked';

    public function hash(): ?string
    {
        return DB::table('deforestory_preview_settings')->where('id', 1)->value('password_hash');
    }

    public function sudahDiatur(): bool
    {
        return filled($this->hash());
    }

    /** Password yang sedang berlaku, untuk ditampilkan ke editor. */
    public function passwordTerbuka(): ?string
    {
        $terenkripsi = DB::table('deforestory_preview_settings')->where('id', 1)->value('password_encrypted');

        if (! filled($terenkripsi)) {
            return null;
        }

        try {
            return Crypt::decryptString($terenkripsi);
        } catch (\Throwable) {
            // APP_KEY berganti sejak password disimpan; hash-nya masih sah,
            // hanya tidak bisa lagi ditampilkan.
            return null;
        }
    }

    public function simpanPassword(string $password): void
    {
        DB::table('deforestory_preview_settings')->updateOrInsert(
            ['id' => 1],
            [
                'password_hash' => Hash::make($password),
                'password_encrypted' => Crypt::encryptString($password),
                'updated_at' => now(),
            ],
        );
    }

    public function hapusPassword(): void
    {
        DB::table('deforestory_preview_settings')->where('id', 1)->update([
            'password_hash' => null,
            'password_encrypted' => null,
            'updated_at' => now(),
        ]);
    }

    public function cocok(string $password): bool
    {
        $hash = $this->hash();

        return filled($hash) && Hash::check($password, $hash);
    }

    /**
     * Sudah boleh melihat isi laporan terkunci?
     *
     * Editor yang sedang login CMS selalu boleh — merekalah yang menyiapkan
     * laporannya, dan meminta mereka mengetik password untuk melihat
     * pekerjaannya sendiri tidak menambah pengamanan apa pun.
     */
    public function terbuka(Request $request): bool
    {
        if ($request->user()?->role && in_array($request->user()->role, ['admin', 'editor'], true)) {
            return true;
        }

        if (! $this->sudahDiatur()) {
            // Belum ada password yang bisa dicocokkan. Laporan terkunci tetap
            // tertutup — bukan terbuka untuk semua.
            return false;
        }

        $tersimpan = $request->session()->get(self::KUNCI_SESI);

        return is_string($tersimpan) && hash_equals($this->sidik(), $tersimpan);
    }

    public function tandaiTerbuka(Request $request): void
    {
        $request->session()->put(self::KUNCI_SESI, $this->sidik());
    }

    private function sidik(): string
    {
        return hash('sha256', (string) $this->hash());
    }
}
