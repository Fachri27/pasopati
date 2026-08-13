<?php

namespace Database\Seeders;

use App\Enums\EventOrientation;
use App\Models\Event;
use App\Services\VideoThumbnailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Sepuluh kejadian karhutla contoh — pengisi halaman /{locale}/fire.
 *
 * FireController mengambil 10 event terbaru (urut event_date menurun) untuk
 * korsel "Berita terkini"; tanpa data, section 1 menampilkan rak kosong. Isi
 * di sini SEMUANYA CONTOH (judul karangan), tetapi lokasi dan koordinatnya
 * nyata supaya peta di CMS ikut masuk akal.
 *
 * Dua hal yang sengaja disesuaikan dengan cara FireController membaca data:
 *
 *  - `location` selalu memuat nama provinsi yang persis sama dengan kunci
 *    PROVINSI_KE_PULAU di FireController — dari situlah label pulau pada kartu
 *    ("Sumatra", "Kalimantan", …) disimpulkan. Menulis "Sumsel" alih-alih
 *    "Sumatera Selatan" membuat label pulau kosong.
 *  - `event_date` dihitung relatif terhadap hari ini, jadi kartu tetap
 *    terbaca "baru" kapan pun seeder dijalankan.
 *
 * Idempoten — event yang judulnya sudah ada dilewati. Jalankan:
 *
 *   php artisan db:seed --class=EventSeeder
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        $gambar = $this->siapkanGambar();
        $video = $this->siapkanVideo();
        $hariIni = today('Asia/Jakarta');

        $dibuat = 0;

        foreach ($this->kejadian() as $data) {
            if (Event::where('title_id', $data['title_id'])->exists()) {
                continue;
            }

            $pakaiVideo = ($data['video'] ?? false) && $video !== null;
            $poster = $gambar[$data['gambar']] ?? null;

            // Sama seperti EventController saat admin mengunggah video tanpa
            // gambar: image_id diisi frame hasil ffmpeg, dan frame itulah yang
            // dipakai kartu sebagai poster.
            if ($pakaiVideo) {
                $poster = app(VideoThumbnailService::class)->generate($video) ?? $poster;
            }

            Event::create([
                'title_id' => $data['title_id'],
                'title_en' => $data['title_en'],
                'event_date' => $hariIni->copy()->subDays($data['selang']),
                'location' => $data['location'],
                'location_lat' => $data['lat'],
                'location_lng' => $data['lng'],
                // Diisi lewat pencarian lokasi GeoServer/PostGIS di CMS, bukan
                // di sini — kolomnya memang nullable.
                'location_geojson' => null,
                'image_id' => $poster,
                'image_en' => $poster,
                'video' => $pakaiVideo ? $video : null,
                'orientation' => $data['orientation'],
            ]);

            $dibuat++;
        }

        $this->command->info("EventSeeder: {$dibuat} kejadian dibuat, ".(10 - $dibuat).' dilewati (sudah ada).');
    }

    /**
     * Salin foto contoh ke disk `public` supaya `image_id_url` (yang selalu
     * lewat Storage::disk('public')->url()) menemukannya. Berkas sumbernya
     * sudah ada di repo untuk halaman fire.
     *
     * Hanya tersedia tiga foto — Sumatra, Kalimantan, Jawa — jadi kejadian di
     * pulau lain memakai yang paling mendekati. Ganti lewat CMS bila sudah ada
     * foto aslinya.
     *
     * @return array<string, string> nama pendek => path relatif di disk public
     */
    private function siapkanGambar(): array
    {
        $sumber = [
            'sumatra' => public_path('assets/img/berita-sumatra.jpg'),
            'kalimantan' => public_path('assets/img/berita-kalimantan.jpg'),
            'jawa' => public_path('assets/img/berita-jawa.jpg'),
        ];

        $hasil = [];

        foreach ($sumber as $nama => $asal) {
            if (! File::exists($asal)) {
                $this->command->warn("Foto contoh tidak ditemukan: {$asal} — kejadian terkait dibuat tanpa gambar.");

                continue;
            }

            $tujuan = "events/contoh-{$nama}.jpg";

            if (! Storage::disk('public')->exists($tujuan)) {
                Storage::disk('public')->put($tujuan, File::get($asal));
            }

            $hasil[$nama] = $tujuan;
        }

        return $hasil;
    }

    /**
     * Satu kejadian contoh sengaja memakai video, bukan foto, supaya jalur
     * video di kartu korsel ikut terpakai. Klipnya dibuat di sini dengan
     * ffmpeg (gerak zoom pelan dari salah satu foto contoh) agar tidak perlu
     * menitipkan berkas biner di repo.
     *
     * Tanpa ffmpeg, fungsi ini mengembalikan null dan kejadian itu jatuh ke
     * foto diam seperti sembilan lainnya — seeder tetap jalan.
     *
     * @return string|null path relatif klip di disk public
     */
    private function siapkanVideo(): ?string
    {
        $tujuan = 'events/videos/contoh-karhutla.mp4';

        if (Storage::disk('public')->exists($tujuan)) {
            return $tujuan;
        }

        $asal = public_path('assets/img/berita-kalimantan.jpg');

        if (! File::exists($asal)) {
            return null;
        }

        Storage::disk('public')->makeDirectory('events/videos');

        $proses = new Process([
            (string) config('services.video.ffmpeg_path', 'ffmpeg'),
            '-y',
            '-loop', '1',
            '-i', $asal,
            '-t', '6',
            '-r', '25',
            '-vf', 'scale=2560:-2,zoompan=z=\'min(zoom+0.0009,1.20)\':d=150:'
                .'x=\'iw/2-(iw/zoom/2)\':y=\'ih/2-(ih/zoom/2)\':s=1200x800:fps=25,format=yuv420p',
            '-an',
            '-c:v', 'libx264',
            '-preset', 'slow',
            '-crf', '27',
            '-movflags', '+faststart',
            Storage::disk('public')->path($tujuan),
        ]);

        $proses->setTimeout(120);
        $proses->run();

        if (! $proses->isSuccessful() || ! Storage::disk('public')->exists($tujuan)) {
            $this->command->warn('ffmpeg tidak tersedia atau gagal — kejadian bervideo dibuat dengan foto diam.');

            return null;
        }

        return $tujuan;
    }

    /**
     * `selang` = berapa hari sebelum hari ini kejadian itu terjadi; nilainya
     * menaik supaya urutan kartu di korsel terbaca dari yang paling baru.
     *
     * @return array<int, array<string, mixed>>
     */
    private function kejadian(): array
    {
        return [
            [
                'selang' => 0,
                'title_id' => 'Karhutla di Bengkalis meluas, 42 hektare lahan gambut terbakar',
                'title_en' => 'Fire spreads in Bengkalis as 42 hectares of peatland burn',
                'location' => 'Kabupaten Bengkalis, Riau',
                'lat' => 1.4667000,
                'lng' => 102.1167000,
                'gambar' => 'sumatra',
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 1,
                'title_id' => 'Regu pemadam bertahan tiga hari di lahan gambut Pulang Pisau',
                'title_en' => 'Firefighters hold the line for three days on Pulang Pisau peatland',
                'location' => 'Kabupaten Pulang Pisau, Kalimantan Tengah',
                'lat' => -2.7000000,
                'lng' => 114.2833000,
                'gambar' => 'kalimantan',
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 2,
                'title_id' => 'Kabut asap selimuti Muaro Jambi, sekolah beralih ke pembelajaran daring',
                'title_en' => 'Haze blankets Muaro Jambi as schools move online',
                'location' => 'Kabupaten Muaro Jambi, Jambi',
                'lat' => -1.5000000,
                'lng' => 103.5000000,
                'gambar' => 'sumatra',
                'orientation' => EventOrientation::Horizontal,
            ],
            [
                'selang' => 3,
                'title_id' => '18 hektare lahan Perhutani di Sukabumi terbakar, diduga dari api unggun',
                'title_en' => '18 hectares of Perhutani land in Sukabumi burn, campfire suspected',
                'location' => 'Kabupaten Sukabumi, Jawa Barat',
                'lat' => -6.9277000,
                'lng' => 106.9300000,
                'gambar' => 'jawa',
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 5,
                'title_id' => 'Ketapang tetapkan siaga darurat karhutla jelang puncak kemarau',
                'title_en' => 'Ketapang declares a fire emergency alert ahead of peak dry season',
                'location' => 'Kabupaten Ketapang, Kalimantan Barat',
                'lat' => -1.8500000,
                'lng' => 109.9833000,
                'gambar' => 'kalimantan',
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 6,
                'title_id' => 'Titik panas di Ogan Komering Ilir naik dua kali lipat dalam sepekan',
                'title_en' => 'Hotspots in Ogan Komering Ilir double within a week',
                'location' => 'Kabupaten Ogan Komering Ilir, Sumatera Selatan',
                'lat' => -3.3928000,
                'lng' => 104.8386000,
                'gambar' => 'sumatra',
                'orientation' => EventOrientation::Horizontal,
            ],
            [
                'selang' => 8,
                'title_id' => 'Savana Tengger di Probolinggo terbakar, jalur pendakian ditutup sementara',
                'title_en' => 'Tengger savanna fire in Probolinggo closes hiking trails',
                'location' => 'Kabupaten Probolinggo, Jawa Timur',
                'lat' => -7.9425000,
                'lng' => 113.2160000,
                'gambar' => 'jawa',
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 10,
                'title_id' => 'Helikopter water bombing dikerahkan ke lahan terbakar dekat Banjarbaru',
                'title_en' => 'Water-bombing helicopters deployed to fires near Banjarbaru',
                'location' => 'Kota Banjarbaru, Kalimantan Selatan',
                'lat' => -3.4572000,
                'lng' => 114.8114000,
                'gambar' => 'kalimantan',
                'video' => true,
                'orientation' => EventOrientation::Landscape,
            ],
            [
                'selang' => 12,
                'title_id' => 'Padang sabana Sumba Timur terbakar, ternak warga kehilangan pakan',
                'title_en' => 'Savanna fire in East Sumba leaves livestock without forage',
                'location' => 'Kabupaten Sumba Timur, Nusa Tenggara Timur',
                'lat' => -9.6553000,
                'lng' => 120.2640000,
                'gambar' => 'jawa',
                'orientation' => EventOrientation::Horizontal,
            ],
            [
                'selang' => 15,
                'title_id' => 'Kebakaran lahan di kaki Gunung Bawakaraeng, Gowa, padam setelah dua hari',
                'title_en' => 'Land fire at the foot of Mount Bawakaraeng, Gowa, out after two days',
                'location' => 'Kabupaten Gowa, Sulawesi Selatan',
                'lat' => -5.2100000,
                'lng' => 119.4500000,
                'gambar' => 'jawa',
                'orientation' => EventOrientation::Landscape,
            ],
        ];
    }
}
