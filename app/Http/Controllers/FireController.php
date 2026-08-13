<?php

namespace App\Http\Controllers;

use App\Enums\EventOrientation;
use App\Models\Event;
use Illuminate\View\View;

class FireController extends Controller
{
    /**
     * Provinsi → pulau. Nilai pulau harus sama dengan `isi` pada PULAU_TAB di
     * public/js/peta.js (tab popup berita): Sumatra, Jawa, Bali-Nusa,
     * Kalimantan, Sulawesi, Maluku, Papua.
     */
    private const PROVINSI_KE_PULAU = [
        'Aceh' => 'Sumatra',
        'Sumatera Utara' => 'Sumatra',
        'Sumatra Utara' => 'Sumatra',
        'Sumatera Barat' => 'Sumatra',
        'Sumatra Barat' => 'Sumatra',
        'Riau' => 'Sumatra',
        'Kepulauan Riau' => 'Sumatra',
        'Jambi' => 'Sumatra',
        'Sumatera Selatan' => 'Sumatra',
        'Sumatra Selatan' => 'Sumatra',
        'Bangka Belitung' => 'Sumatra',
        'Bengkulu' => 'Sumatra',
        'Lampung' => 'Sumatra',
        'Banten' => 'Jawa',
        'DKI Jakarta' => 'Jawa',
        'Jakarta' => 'Jawa',
        'Jawa Barat' => 'Jawa',
        'Jawa Tengah' => 'Jawa',
        'DI Yogyakarta' => 'Jawa',
        'Yogyakarta' => 'Jawa',
        'Jawa Timur' => 'Jawa',
        'Kalimantan Barat' => 'Kalimantan',
        'Kalimantan Tengah' => 'Kalimantan',
        'Kalimantan Selatan' => 'Kalimantan',
        'Kalimantan Timur' => 'Kalimantan',
        'Kalimantan Utara' => 'Kalimantan',
        'Sulawesi Utara' => 'Sulawesi',
        'Gorontalo' => 'Sulawesi',
        'Sulawesi Tengah' => 'Sulawesi',
        'Sulawesi Barat' => 'Sulawesi',
        'Sulawesi Selatan' => 'Sulawesi',
        'Sulawesi Tenggara' => 'Sulawesi',
        'Bali' => 'Bali-Nusa',
        'Nusa Tenggara Barat' => 'Bali-Nusa',
        'Nusa Tenggara Timur' => 'Bali-Nusa',
        'Maluku Utara' => 'Maluku',
        'Maluku' => 'Maluku',
        'Papua Barat' => 'Papua',
        'Papua' => 'Papua',
    ];

    /**
     * Halaman /fire — korsel "Berita terkini" dan popup berita pada peta
     * diambil dari CMS Event/Kejadian. Bila belum ada event, $berita kosong
     * dan Blade menampilkan "rak kosong" menggantikan korsel.
     */
    public function index(): View
    {
        $events = Event::query()
            ->orderByDesc('event_date')
            ->take(10)
            ->get();

        $berita = $events->map(fn (Event $event) => [
            // Dipakai untuk menautkan kartu peta ke pop-up rincian yang sama.
            'id' => $event->id,
            'pulau' => $this->inferPulau($event->location),
            'tanggal' => $event->event_date?->locale('id')->translatedFormat('j F Y') ?? '',
            'judul' => $event->title_id,
            'gambar' => $event->image_id_url ?? asset('assets/img/berita-jawa.jpg'),
            'alt' => $event->title_id,
            // Bila event menyertakan video, kartu memutarnya alih-alih
            // menampilkan foto diam — `gambar` tetap dikirim dan dipakai
            // sebagai poster. Untuk event bervideo, EventController mengisi
            // image_id dengan thumbnail hasil ffmpeg, jadi posternya adalah
            // frame dari video itu sendiri.
            'video' => $event->video_url,
            // Dipakai pop-up rincian saat judul/gambar kartu diklik.
            'lokasi' => $this->rapikanLokasi($event->location),
            // Orientasi di CMS memilih varian kartu di beranda.blade.php:
            // "horizontal" → foto memenuhi bingkai kartu, judul & tanggal
            // menumpang putih di atasnya (varian `vertikal`); "landscape" →
            // kartu kaca putih, teks gelap di atas, foto 3:2 di bawah.
            'vertikal' => $event->orientation === EventOrientation::Horizontal,
        ])->all();

        // Dipakai keadaan "belum ada laporan" di section 1: cap tanggal pada
        // rak kosong, dan tautan ke CMS yang hanya muncul bagi peran yang
        // memang boleh menambah kejadian (lihat grup role:admin,editor di
        // routes/web.php) — pengunjung biasa tidak diberi tugas yang tak bisa
        // dikerjakannya.
        // config('app.timezone') masih UTC, sedangkan cap ini dibaca sebagai
        // "hari ini" oleh pembaca di Indonesia — tanpa konversi, tiap malam WIB
        // stempelnya mundur satu hari.
        $tanggalPantauan = now('Asia/Jakarta')->locale('id')->translatedFormat('j F Y');
        $bisaTambahKejadian = auth()->check()
            && in_array(auth()->user()->role, ['admin', 'editor'], true);

        return view('pasopati.index', compact('berita', 'tanggalPantauan', 'bisaTambahKejadian'));
    }

    /**
     * Cadangan bila nama provinsi tidak dikenali: pencarian lokasi GeoServer
     * mengembalikan nama berbahasa Inggris ("South Kalimantan", "West Java")
     * yang tidak ada di PROVINSI_KE_PULAU, tetapi rangkaiannya selalu memuat
     * nama pulau. Dicek setelah daftar provinsi supaya provinsi yang cocok
     * tetap menang.
     */
    private const PULAU_ALIAS = [
        'Jawa' => 'Jawa',
        'Java' => 'Jawa',
        'Sumatra' => 'Sumatra',
        'Sumatera' => 'Sumatra',
        'Kalimantan' => 'Kalimantan',
        'Borneo' => 'Kalimantan',
        'Sulawesi' => 'Sulawesi',
        'Celebes' => 'Sulawesi',
        'Maluku' => 'Maluku',
        'Moluccas' => 'Maluku',
        'Papua' => 'Papua',
        'Bali' => 'Bali-Nusa',
        'Nusa Tenggara' => 'Bali-Nusa',
        'Lesser Sunda Islands' => 'Bali-Nusa',
    ];

    /**
     * Lokasi untuk dibaca manusia di pop-up rincian.
     *
     * Pencarian lokasi GeoServer di CMS menyimpan rangkaian berkurung, mis.
     * "[Abung Jayo][South Abung][North Lampung][Lampung][Sumatra][Indonesia][77484]".
     * Bentuk itu benar sebagai data, tetapi tidak untuk dibaca — kurungnya
     * dilepas, dirangkai dengan koma, dan segmen yang hanya berupa angka (kode
     * pos) dibuang. Lokasi yang diketik bebas dilewatkan apa adanya.
     *
     * Hanya untuk tampilan: `inferPulau()` tetap membaca $event->location asli.
     */
    protected function rapikanLokasi(?string $lokasi): ?string
    {
        if ($lokasi === null || trim($lokasi) === '') {
            return null;
        }

        if (! preg_match_all('/\[([^\]]*)\]/', $lokasi, $cocok)) {
            return trim($lokasi);
        }

        $bagian = array_filter(
            array_map('trim', $cocok[1]),
            fn (string $b) => $b !== '' && ! ctype_digit($b)
        );

        return $bagian === [] ? trim($lokasi) : implode(', ', $bagian);
    }

    protected function inferPulau(?string $lokasi): ?string
    {
        if ($lokasi === null || trim($lokasi) === '') {
            return null;
        }

        $lokasi = mb_strtolower(trim($lokasi));
        $provinsi = array_keys(self::PROVINSI_KE_PULAU);

        // Nama provinsi lebih panjang dicek dulu (mis. "Papua Barat" sebelum
        // "Papua", "Maluku Utara" sebelum "Maluku").
        usort($provinsi, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($provinsi as $nama) {
            if (mb_strpos($lokasi, mb_strtolower($nama)) !== false) {
                return self::PROVINSI_KE_PULAU[$nama];
            }
        }

        $alias = array_keys(self::PULAU_ALIAS);
        usort($alias, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($alias as $nama) {
            if (mb_strpos($lokasi, mb_strtolower($nama)) !== false) {
                return self::PULAU_ALIAS[$nama];
            }
        }

        return null;
    }
}
