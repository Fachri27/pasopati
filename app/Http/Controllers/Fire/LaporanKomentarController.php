<?php

namespace App\Http\Controllers\Fire;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Event;
use App\Services\ProfanityFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Komentar pada satu laporan karhutla, untuk kolom komentar di pop-up rincian
 * halaman /fire.
 *
 * Kenapa JSON, bukan komponen Livewire CommentSection yang sudah ada: pop-up
 * itu digerakkan Alpine dan berpindah laporan tanpa memuat ulang halaman,
 * sedangkan komponen Livewire harus di-mount dari server dan tidak bisa hidup
 * kembali setelah dibongkar-pasang x-if. Memasang Livewire di layout pasopati
 * juga berarti menukar sumber Alpine halaman ini (Livewire membawa Alpine
 * sendiri) — perubahan yang menyentuh korsel, peta, nav, dan parallax
 * sekaligus. Endpoint kecil ini menghindari keduanya.
 *
 * Yang tetap dipakai bersama: tabel + model Comment yang sama (polymorphic),
 * ProfanityFilter yang sama, dan login Google yang sama
 * (GoogleCommentLoginController), jadi komentar di sini satu kumpulan dengan
 * komentar di halaman lain.
 *
 * Berkomentar WAJIB masuk lewat Google — nama dan foto diambil dari akun,
 * jadi tidak ada isian nama yang bisa dipalsukan.
 *
 * Balasan didukung satu tingkat tampilan, seperti rujukannya: balasan sedalam
 * apa pun dikumpulkan datar di bawah komentar akarnya, dengan @sebutan ke
 * lawan bicaranya. Reaksi/suka tidak ada di sini.
 */
class LaporanKomentarController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        return response()->json([
            'komentar' => $this->daftar($event),
        ]);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        if (! $this->turnstileSah($request)) {
            return response()->json([
                'message' => 'Verifikasi captcha gagal. Coba lagi.',
            ], 422);
        }

        // `website` adalah umpan jebakan (honeypot) yang disembunyikan di form;
        // hanya bot yang mengisinya. Ditolak diam-diam, bukan dengan galat,
        // supaya tidak memberi petunjuk.
        if (filled($request->input('website'))) {
            return response()->json(['komentar' => $this->daftar($event)], 201);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'isi' => ['required', 'string', 'max:2000'],
            'balas_ke' => ['nullable', 'integer'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'isi.required' => 'Komentar wajib diisi.',
            'isi.max' => 'Komentar maksimal 2000 karakter.',
        ]);

        // Induk dicari dengan syarat laporan yang sama: tanpa ini sebuah
        // balasan bisa ditempelkan ke komentar milik laporan lain lewat id
        // yang ditebak.
        $induk = blank($data['balas_ke'] ?? null) ? null : Comment::query()
            ->where('commentable_type', Event::class)
            ->where('commentable_id', $event->id)
            ->where('is_approved', true)
            ->find($data['balas_ke']);

        Comment::query()->create([
            'page_id' => null,
            'commentable_type' => Event::class,
            'commentable_id' => $event->id,
            'name' => $data['nama'],
            'email' => $data['email'],
            'body' => app(ProfanityFilter::class)->filter($data['isi']),
            'ip_address' => $request->ip(),
            'parent_id' => $induk?->id,
            'mention_name' => $induk?->name,
        ]);

        return response()->json(['komentar' => $this->daftar($event)], 201);
    }

    /**
     * Komentar tampil untuk satu laporan: hanya yang lolos moderasi.
     *
     * Akar diurutkan terbaru dulu (sama seperti bawaan CommentSection),
     * sedangkan balasan kronologis — terlama di atas, supaya percakapannya
     * terbaca berurutan.
     *
     * Balasan sedalam apa pun dikumpulkan datar di bawah akarnya, bukan
     * bersarang berlapis-lapis: rel pop-up ini sempit, dan @sebutan sudah
     * cukup menerangkan siapa membalas siapa — sama seperti rujukannya.
     *
     * @return array<int, array<string, mixed>>
     */
    private function daftar(Event $event): array
    {
        $semua = Comment::query()
            ->where('commentable_type', Event::class)
            ->where('commentable_id', $event->id)
            ->where('is_approved', true)
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        $anak = $semua->groupBy(fn (Comment $k) => $k->parent_id ?? 0);

        $kumpulkan = function (int $indukId) use (&$kumpulkan, $anak): array {
            $hasil = [];

            foreach ($anak->get($indukId, collect()) as $balasan) {
                $hasil[] = $balasan;
                $hasil = array_merge($hasil, $kumpulkan($balasan->id));
            }

            return $hasil;
        };

        return $anak->get(0, collect())
            ->sortByDesc('created_at')
            ->values()
            ->map(function (Comment $akar) use ($kumpulkan) {
                $balasan = collect($kumpulkan($akar->id))
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn (Comment $k) => $this->bentuk($k))
                    ->all();

                return $this->bentuk($akar) + ['balasan' => $balasan];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function bentuk(Comment $komentar): array
    {
        return [
            'id' => $komentar->id,
            'nama' => $komentar->name,
            'isi' => $komentar->body,
            // Rute ini di luar grup {locale}, jadi middleware setlocale tidak
            // jalan dan diffForHumans() akan berbahasa Inggris. Halaman
            // pasopati dipatok <html lang="id">, jadi waktunya ikut dipatok.
            'waktu' => $komentar->created_at?->locale('id')->diffForHumans() ?? '',
            'avatar' => $this->avatar($komentar),
            // Setiap balasan membawa sebutan, termasuk saat membalas komentar
            // sendiri: balasan ditampilkan datar dalam satu utas, jadi sebutan
            // inilah satu-satunya penanda komentar mana yang sedang dijawab.
            'sebutan' => $komentar->mention_name,
        ];
    }

    /**
     * Cloudflare Turnstile — captcha yang sama dengan kolom komentar di
     * halaman lain (lihat CommentSection::verifyTurnstile).
     *
     * Dilewati bila secret-nya belum dikonfigurasi: tanpa itu widget-nya juga
     * tidak dirender, dan memaksa verifikasi hanya akan membuat komentar mustahil
     * dikirim di environment yang memang belum memasang Turnstile.
     */
    private function turnstileSah(Request $request): bool
    {
        $secret = (string) config('services.turnstile.secret_key');

        if ($secret === '') {
            return true;
        }

        $token = (string) $request->input('captcha_token');

        if ($token === '') {
            return false;
        }

        return Http::asForm()
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ])
            ->json('success') === true;
    }

    /**
     * Foto profil dari akun Google, bila ada. Socialite menyimpan URL penuh,
     * sedangkan foto yang diunggah sendiri tersimpan sebagai path di disk
     * public — keduanya ditangani, sama seperti di comment-section.blade.php.
     */
    private function avatar(Comment $komentar): ?string
    {
        $gambar = $komentar->user?->image;

        if (blank($gambar)) {
            return null;
        }

        return Str::startsWith($gambar, ['http://', 'https://'])
            ? $gambar
            : asset('storage/'.$gambar);
    }
}
