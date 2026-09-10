<?php

namespace Database\Seeders;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Laporan contoh yang memakai SEMUA blok dari editor TinyMCE baru: galeri
 * GLightbox, pembanding Before/After, grafik Data & Grafik, gambar tunggal,
 * video, border, dan stopper.
 *
 * Isinya ditulis sebagai HTML yang identik dengan yang disisipkan tombol-tombol
 * editor — bukan versi sederhananya. Itu penting: yang ingin diperiksa justru
 * apakah markup asli dari editor benar-benar tampil dan berfungsi di halaman
 * pembaca, termasuk penggeser Before/After dan tombol geser galerinya.
 *
 * Idempoten: bisa dijalankan berulang kali. Untuk membersihkan, jalankan
 * dengan --class=DeforestoryBlokCeritaDummySeeder setelah menghapus laporan
 * ber-slug `contoh-blok-cerita` dari CMS.
 *
 *   php artisan db:seed --class=DeforestoryBlokCeritaDummySeeder
 */
class DeforestoryBlokCeritaDummySeeder extends Seeder
{
    private const SLUG = 'contoh-blok-cerita';

    public function run(): void
    {
        $case = DeforestoryCase::where('status', 'active')->orderBy('id')->first();

        if (! $case) {
            $this->command->warn('Tidak ada DeforestoryCase aktif — jalankan DeforestoryCaseSeeder dulu.');

            return;
        }

        $gambar = $this->siapkanGambar();
        $grafikId = $this->siapkanGrafik();

        $laporan = DeforestoryLaporan::updateOrCreate(
            ['case_id' => $case->id, 'slug' => self::SLUG],
            [
                'image' => $gambar['sorotan'],
                'sort' => 0,
                'status' => 'active',
                'published_at' => now()->toDateString(),
            ],
        );

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::updateOrCreate(
                ['laporan_id' => $laporan->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id'
                        ? 'Contoh Blok Cerita — semua elemen editor baru'
                        : 'Story Block Sampler — every new editor block',
                    'excerpt' => $locale === 'id'
                        ? 'Laporan uji coba yang memuat galeri GLightbox, pembanding Before/After, grafik interaktif, gambar tunggal, video, border, dan stopper.'
                        : 'A test report containing the GLightbox gallery, Before/After comparison, interactive chart, single image, video, border, and stopper.',
                    'content' => $this->isi($locale, $gambar, $grafikId),
                    'image' => $gambar['sorotan'],
                ],
            );
        }

        $this->command->info("Laporan dummy siap: /id/deforestory/{$case->slug}/".self::SLUG);
    }

    /**
     * Gambar contoh dibuat di sini, bukan diunduh, supaya seeder ini jalan
     * tanpa jaringan dan tidak menarik berkas pihak ketiga ke repo.
     *
     * @return array<string, string> nama => jalur relatif di disk public
     */
    private function siapkanGambar(): array
    {
        // Label sengaja ASCII saja: imagestring() memakai font bitmap bawaan GD
        // yang tidak punya karakter di luar Latin-1, dan em-dash di sini muncul
        // sebagai sampah di tengah gambar.
        $resep = [
            'hutan-2019' => ['2019 - tutupan rapat', [34, 84, 61], [72, 141, 105]],
            'hutan-2025' => ['2025 - bukaan kanal', [138, 106, 62], [186, 158, 104]],
            'galeri-1' => ['Galeri 1 - kanal primer', [46, 74, 96], [96, 134, 164]],
            'galeri-2' => ['Galeri 2 - tumpukan kayu', [92, 58, 52], [148, 102, 88]],
            'galeri-3' => ['Galeri 3 - sekat bakar', [58, 82, 48], [110, 142, 92]],
            'sorotan' => ['Sorotan udara', [40, 62, 74], [88, 122, 138]],
        ];

        $jalur = [];

        foreach ($resep as $nama => [$teks, $atas, $bawah]) {
            $relatif = "references/dummy-{$nama}.jpg";
            $jalur[$nama] = $relatif;

            if (! Storage::disk('public')->exists($relatif)) {
                Storage::disk('public')->put($relatif, $this->gambarJpeg($teks, $atas, $bawah));
            }

            DB::table('reference_images')->updateOrInsert(
                ['image_path' => $relatif],
                [
                    'title' => $teks,
                    'alt_text' => $teks,
                    'disk' => 'public',
                    'original_name' => basename($relatif),
                    'mime_type' => 'image/jpeg',
                    'file_size' => Storage::disk('public')->size($relatif),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        return $jalur;
    }

    /** Gradien 16:9 dengan label, cukup untuk membedakan satu gambar dari yang lain. */
    private function gambarJpeg(string $teks, array $atas, array $bawah): string
    {
        [$lebar, $tinggi] = [1280, 720];
        $kanvas = imagecreatetruecolor($lebar, $tinggi);

        for ($y = 0; $y < $tinggi; $y++) {
            $rasio = $y / ($tinggi - 1);
            $warna = imagecolorallocate(
                $kanvas,
                (int) round($atas[0] + ($bawah[0] - $atas[0]) * $rasio),
                (int) round($atas[1] + ($bawah[1] - $atas[1]) * $rasio),
                (int) round($atas[2] + ($bawah[2] - $atas[2]) * $rasio),
            );
            imageline($kanvas, 0, $y, $lebar, $y, $warna);
        }

        $putih = imagecolorallocate($kanvas, 255, 255, 255);
        $bayang = imagecolorallocatealpha($kanvas, 0, 0, 0, 70);
        // imagestring hanya punya font bitmap kecil; diperbesar dengan
        // menggambar ke kanvas kecil lalu di-scale supaya tetap terbaca.
        $label = imagecreatetruecolor(imagefontwidth(5) * strlen($teks) + 8, imagefontheight(5) + 6);
        imagefill($label, 0, 0, imagecolorallocate($label, 0, 0, 0));
        imagecolortransparent($label, imagecolorallocate($label, 0, 0, 0));
        imagestring($label, 5, 4, 3, $teks, imagecolorallocate($label, 255, 255, 255));

        $skala = 3;
        $lebarLabel = imagesx($label) * $skala;
        $tinggiLabel = imagesy($label) * $skala;
        $x = (int) (($lebar - $lebarLabel) / 2);
        $y = (int) (($tinggi - $tinggiLabel) / 2);

        imagefilledrectangle($kanvas, $x - 24, $y - 18, $x + $lebarLabel + 24, $y + $tinggiLabel + 18, $bayang);
        imagecopyresampled($kanvas, $label, $x, $y, 0, 0, $lebarLabel, $tinggiLabel, imagesx($label), imagesy($label));
        imagerectangle($kanvas, 24, 24, $lebar - 25, $tinggi - 25, $putih);

        ob_start();
        imagejpeg($kanvas, null, 82);
        $biner = ob_get_clean();

        imagedestroy($label);
        imagedestroy($kanvas);

        return $biner;
    }

    /** Grafik aktif supaya <iframe> blok Data & Grafik punya isi sungguhan. */
    private function siapkanGrafik(): int
    {
        $judul = 'Contoh — laju kehilangan tutupan hutan';
        $ada = DB::table('data_visualizations')->where('title', $judul)->first();

        $nilai = [
            'title' => $judul,
            'description' => 'Angka contoh untuk menguji blok Data & Grafik.',
            'provider' => 'internal',
            'chart_type' => 'column',
            'chart_data' => json_encode([
                'columns' => ['Tahun', 'Hektare'],
                'rows' => [['2021', '180'], ['2022', '240'], ['2023', '395'], ['2024', '410'], ['2025', '520']],
                'top_text' => 'Laju kehilangan tutupan hutan',
                'top_align' => 'center',
                'top_font_size' => 18,
                'top_font_weight' => 'bold',
                'top_font_family' => 'Poppins',
                'top_italic' => false,
                'bottom_text' => 'Sumber: data contoh, bukan angka sebenarnya.',
                'bottom_align' => 'center',
                'bottom_font_size' => 13,
                'bottom_font_weight' => 'normal',
                'bottom_font_family' => 'Poppins',
                'bottom_italic' => false,
                'show_legend' => true,
                'legend_position' => 'bottom',
                'value_axis_ticks' => [],
                'show_value_axis_line' => true,
            ]),
            'embed_url' => null,
            'source_url' => null,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($ada) {
            DB::table('data_visualizations')->where('id', $ada->id)->update($nilai);

            return (int) $ada->id;
        }

        return (int) DB::table('data_visualizations')->insertGetId($nilai + ['created_at' => now()]);
    }

    private function isi(string $locale, array $gambar, int $grafikId): string
    {
        $id = $locale === 'id';
        // Host-relatif, sama seperti yang ditanam pemilih Reference ke dalam
        // konten. Penting di seeder: dijalankan dari CLI, url() akan memakai
        // APP_URL — dan begitu APP_URL berbeda dari host yang dipakai
        // (mis. artisan serve di 127.0.0.1), semua gambarnya mati.
        $url = fn (string $nama) => Storage::disk('public')->url($gambar[$nama]);
        $embed = route('data-visualizations.embed', $grafikId, false);

        $galeri = collect([
            ['galeri-1', $id ? 'Kanal primer yang dibuka sepanjang blok utara.' : 'Primary canal cut along the northern block.'],
            ['galeri-2', $id ? 'Tumpukan kayu di tepi jalan angkut.' : 'Log piles beside the haul road.'],
            ['galeri-3', $id ? 'Sekat bakar yang belum selesai dikerjakan.' : 'An unfinished firebreak.'],
        ])->map(function (array $item) use ($url) {
            [$nama, $kapsi] = $item;

            return '<figure class="story-content-figure" style="width: 100%; margin: 0; padding: 0; box-sizing: border-box;">'
                .'<a class="glightbox2 gbox" href="'.$url($nama).'" data-gallery="galeri-contoh" data-glightbox="description: '.e($kapsi).'" style="display: block; width: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; overflow: hidden;">'
                .'<img src="'.$url($nama).'" alt="'.e($kapsi).'" title="'.e($kapsi).'" style="display: block; width: 100%; height: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; object-fit: cover; object-position: center; cursor: zoom-in;"></a>'
                .'<figcaption class="story-content-caption" style="margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">'.e($kapsi).'</figcaption>'
                .'</figure>';
        })->implode('');

        $stopper = '<span class="story-inline-stopper" data-story-inline-stopper="true" contenteditable="false" aria-hidden="true" style="position:relative;top:1px;display:inline-block;flex:0 0 8px;width:8px;height:8px;margin-left:1px;border-radius:0;background:#d71920;vertical-align:middle;font-size:0;line-height:0;">&nbsp;</span>';

        $kapsiBanding = $id
            ? '<strong>2019 &rarr; 2025.</strong> Geser pemisahnya untuk membandingkan tutupan hutan sebelum dan sesudah kanal dibuka.'
            : '<strong>2019 &rarr; 2025.</strong> Drag the divider to compare forest cover before and after the canals were opened.';

        $blok = [];

        $blok[] = '<h2>'.($id ? 'Apa yang terlihat dari udara' : 'What the aerial view shows').'</h2>';
        $blok[] = '<p>'.($id
            ? 'Laporan ini sengaja dibuat sebagai contoh: seluruh blok yang bisa disisipkan editor baru dipakai di sini, satu per satu, supaya bisa diperiksa tampilannya di halaman pembaca.'
            : 'This report exists as a sampler: every block the new editor can insert appears here, one after another, so the reader-facing rendering can be checked.'
        ).$stopper.'</p>';

        // + Image
        $blok[] = '<figure class="media-caption story-single-image-figure" style="width: 100%; margin: 24px 0; padding: 0; box-sizing: border-box;">'
            .'<div class="story-single-image" style="display: block; width: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; overflow: hidden; background: #e5e7eb;">'
            .'<img src="'.$url('sorotan').'" alt="'.($id ? 'Sorotan udara' : 'Aerial highlight').'" title="'.($id ? 'Sorotan udara' : 'Aerial highlight').'" style="display: block; width: 100%; height: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; object-fit: cover; object-position: center;"></div>'
            .'<figcaption class="media-caption-text story-content-caption" style="margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">'
            .($id ? 'Gambar tunggal dari tombol + Image.' : 'A single image from the + Image button.').'</figcaption></figure>';

        // + Before/After
        $blok[] = '<h2>'.($id ? 'Sebelum dan sesudah' : 'Before and after').'</h2>';
        $blok[] = '<figure class="story-before-after-figure" data-before-after-caption="'.e(strip_tags($kapsiBanding)).'" contenteditable="false" data-mce-contenteditable="false" draggable="false" style="width: 100%; margin: 24px 0; padding: 0;">'
            .'<div class="story-before-after" data-story-before-after style="--before-after-position: 50%; position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden;">'
            .'<img class="story-before-after-image story-before-after-image--before" src="'.$url('hutan-2019').'" alt="2019" draggable="false" style="display: block; width: 100%; height: 100%; object-fit: cover;">'
            .'<div class="story-before-after-after" data-before-after-after style="position: absolute; inset: 0; clip-path: inset(0 0 0 var(--before-after-position));">'
            .'<img class="story-before-after-image story-before-after-image--after" src="'.$url('hutan-2025').'" alt="2025" draggable="false" style="display: block; width: 100%; height: 100%; object-fit: cover;"></div>'
            .'<span class="story-before-after-divider" aria-hidden="true"></span>'
            .'<span class="story-before-after-handle" aria-hidden="true">&#8596;</span>'
            .'<input class="story-before-after-range" data-before-after-range type="range" min="0" max="100" value="50" draggable="false" aria-label="Geser perbandingan gambar Before dan After"></div>'
            .'<figcaption class="story-before-after-caption" style="display: flex; flex-wrap: wrap; gap: 4px 12px; margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">'.$kapsiBanding.'</figcaption></figure>';

        // + GLightbox
        $blok[] = '<h2>'.($id ? 'Galeri lapangan' : 'Field gallery').'</h2>';
        $blok[] = '<div class="story-content-gallery story-lightbox-gallery" data-story-gallery data-story-lightbox-gallery="galeri-contoh" contenteditable="false" data-mce-contenteditable="false" style="width: 100%; margin: 24px 0; padding: 0; box-sizing: border-box;">'.$galeri.'</div>';

        // + Data/Grafik
        $blok[] = '<h2>'.($id ? 'Angkanya' : 'The numbers').'</h2>';
        $blok[] = '<figure class="story-data-visualization" data-story-data-visualization data-visualization-id="'.$grafikId.'" contenteditable="false" style="display:block;width:100%;margin:24px 0;padding:0;">'
            .'<div style="position:relative;width:100%;aspect-ratio:16 / 9;overflow:hidden;background:#fff;">'
            .'<iframe src="'.$embed.'" title="'.($id ? 'Laju kehilangan tutupan hutan' : 'Forest cover loss rate').'" width="100%" height="100%" frameborder="0" scrolling="no" loading="lazy" style="position:absolute;inset:0;display:block;width:100%;height:100%;border:0;overflow:hidden;" referrerpolicy="strict-origin-when-cross-origin"></iframe></div></figure>';

        // + Border
        $blok[] = "<div style='border: 1px solid red; padding: 20px;'>"
            .($id ? 'Kotak catatan dari tombol + Border — dipakai untuk keterangan metode atau sanggahan.' : 'A note box from the + Border button — used for method notes or right of reply.')
            .'</div>';

        $blok[] = '<p>'.($id
            ? 'Titik merah kecil di ujung kalimat ini adalah stopper.'
            : 'The small red square at the end of this sentence is the stopper.'
        ).$stopper.'</p>';

        return implode("\n", $blok);
    }
}
