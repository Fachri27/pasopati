<?php

namespace Database\Seeders;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Isi laporan dummy untuk kasus yang belum punya laporan (default:
 * `pulau-laut`). Idempoten — skip slug yang sudah ada per case. Jalankan:
 *
 *   php artisan db:seed --class=DeforestoryLaporanDummySeeder
 */
class DeforestoryLaporanDummySeeder extends Seeder
{
    public function run(): void
    {
        $case = DeforestoryCase::where('slug', 'pulau-laut')->where('status', 'active')->first();

        if (! $case) {
            $this->command->warn('Kasus "pulau-laut" (active) tidak ditemukan — skip.');

            return;
        }

        $laporans = [
            [
                'slug' => 'jejak-sawit-pulau-laut',
                'sort' => 1,
                'published_at' => '2024-09-18 00:00:00',
                'id' => [
                    'title' => 'Jejak sawit di Pulau Laut',
                    'excerpt' => 'Analisis spasial perluasan kebun sawit di Pulau Laut, Kalimantan Selatan, dan keterkaitannya dengan rantai pasok CPO grup korporasi besar.',
                    'content' => '<h2>Latar belakang</h2><p><strong>Pulau Laut</strong> di Kabupaten Kotabaru, Kalimantan Selatan, merupakan salah satu titik pusat perluasan kebun sawit di pesisir selatan Kalimantan. Citra satelit menunjukkan konversi tutupan lahan hutan dan lahan masyarakat menjadi blok-blok kebun monokultur sejak awal 2010-an.</p><p>Analisis spasial Auriga memetakan luasan, pola, dan waktu perluasan, lalu menelusurinya ke rantai pasok kelapa sawit (CPO) milik sejumlah grup korporasi.</p><h2>Temuan utama</h2><ul><li>Perluasan kebun sawit terkonsentrasi di pesisir timur dan utara pulau.</li><li>Pola bukaan lahan menunjukkan alih fungsi bertahap: hutan galeri → lahan masyarakat → kebun sawit.</li><li>Sebagian blok berbatasan langsung dengan ekosistem mangrove pesisir.</li></ul>',
                ],
                'en' => [
                    'title' => 'The palm oil trail on Pulau Laut',
                    'excerpt' => 'Spatial analysis of oil palm expansion on Pulau Laut, South Kalimantan, and its links to the CPO supply chain of major corporate groups.',
                    'content' => '<h2>Background</h2><p><strong>Pulau Laut</strong> in Kotabaru Regency, South Kalimantan, is one of the focal points of oil palm expansion along the southern Kalimantan coast. Satellite imagery shows the conversion of forest and community land into monoculture plantation blocks since the early 2010s.</p><p>Auriga\'s spatial analysis maps the extent, pattern, and timing of the expansion, then traces it into the crude palm oil (CPO) supply chain of several corporate groups.</p><h2>Key findings</h2><ul><li>Oil palm expansion is concentrated on the eastern and northern coasts of the island.</li><li>Land-clearing patterns show a staged conversion: gallery forest → community land → oil palm.</li><li>Several blocks border directly on coastal mangrove ecosystems.</li></ul>',
                ],
            ],
            [
                'slug' => 'konflik-lahan-petani-pesisir',
                'sort' => 2,
                'published_at' => '2025-01-22 00:00:00',
                'id' => [
                    'title' => 'Konflik lahan dan petani pesisir',
                    'excerpt' => 'Catatan lapangan sengketa lahan antara masyarakat adat dan perkebunan sawit di Pulau Laut, lengkap dengan kronologi dan dokumentasi citra.',
                    'content' => '<h2>Titik panas konflik</h2><p>Sengketa lahan di Pulau Laut berpusat pada klaim tumpang-tindih antara tanah ulayat masyarakat dan izin lokasi perkebunan. Catatan lapangan Auriga merekam sedikitnya tiga desa yang mengalami sengketa aktif sejak 2018.</p><h2>Kronologi</h2><ul><li><strong>2018</strong> — penandaan batas kebun dinilai merintangi ladang masyarakat.</li><li><strong>2021</strong> — aksi unjuk rasa ditindak dengan laporan polisi.</li><li><strong>2024</strong> — mediasi pemerintah daerah berjalan tanpa kesimpulan.</li></ul><p>Dokumentasi citra sebelum-sesudah memperlihatkan perubahan tutupan lahan di lokasi yang dipersengketkan.</p>',
                ],
                'en' => [
                    'title' => 'Land conflict and coastal farmers',
                    'excerpt' => 'Field notes on land disputes between indigenous communities and oil palm plantations on Pulau Laut, with chronology and imagery documentation.',
                    'content' => '<h2>Conflict hotspots</h2><p>Land disputes on Pulau Laut centre on overlapping claims between community customary land and plantation location permits. Auriga\'s field notes record at least three villages with active disputes since 2018.</p><h2>Chronology</h2><ul><li><strong>2018</strong> — plantation boundary marking judged to encroach on community farms.</li><li><strong>2021</strong> — protests met with police reports.</li><li><strong>2024</strong> — regional-government mediation proceeds without conclusion.</li></ul><p>Before-and-after imagery documents land-cover change at the disputed locations.</p>',
                ],
            ],
            [
                'slug' => 'hilangnya-hutan-mangrove-pesisir',
                'sort' => 3,
                'published_at' => '2025-06-03 00:00:00',
                'id' => [
                    'title' => 'Hilangnya hutan mangrove pesisir',
                    'excerpt' => 'Kerentanan ekosistem mangrove Pulau Laut akibat konversi kebun sawit dan tambak, serta dampaknya bagi perlindungan pesisir.',
                    'content' => '<h2>Kerentanan ekosistem</h2><p>Mangrove di pesisir Pulau Laut berperan sebagai penahan abrasi dan habitat perikanan pesisir. Citra satelit Auriga mencatat penyusutan ketebalan penutupan mangrove di teluk-teluk sempit sejak 2015.</p><h2>Pendorong kehilangan</h2><ul><li>Konversi menjadi tambak udang skala kecil-menengah.</li><li>Alih fungsi tepi ke kebun sawit.</li><li>Penebangan untuk kayu bakar dan arang.</li></ul><p>Kehilangan mangrove meningkatkan risiko banjir rob bagi pemukiman pesisir terdekat.</p>',
                ],
                'en' => [
                    'title' => 'The loss of coastal mangrove forests',
                    'excerpt' => 'Vulnerability of Pulau Laut\'s mangrove ecosystem due to oil palm and pond conversion, and its impact on coastal protection.',
                    'content' => '<h2>Ecosystem vulnerability</h2><p>Mangroves on Pulau Laut\'s coast act as a buffer against abrasion and as habitat for coastal fisheries. Auriga\'s satellite imagery records thinning mangrove cover in narrow bays since 2015.</p><h2>Drivers of loss</h2><ul><li>Conversion to small- and medium-scale shrimp ponds.</li><li>Edge conversion into oil palm plantations.</li><li>Felling for fuelwood and charcoal.</li></ul><p>Mangrove loss raises the risk of tidal flooding for the nearest coastal settlements.</p>',
                ],
            ],
        ];

        foreach ($laporans as $data) {
            $existing = DeforestoryLaporan::where('case_id', $case->id)->where('slug', $data['slug'])->exists();
            if ($existing) {
                $this->command->line("  skip (sudah ada): {$data['slug']}");

                continue;
            }

            $laporan = DeforestoryLaporan::create([
                'case_id' => $case->id,
                'slug' => $data['slug'],
                'image' => null,
                'sort' => $data['sort'],
                'status' => 'active',
                'published_at' => $data['published_at'],
            ]);

            foreach (['id', 'en'] as $locale) {
                $laporan->translations()->create([
                    'locale' => $locale,
                    'title' => $data[$locale]['title'],
                    'excerpt' => $data[$locale]['excerpt'],
                    'content' => $data[$locale]['content'],
                ]);
            }

            $this->command->info("  buat: {$data['slug']} ({$data['en']['title']})");
        }

        $this->command->info('Laporan dummy untuk pulau-laut selesai.');
    }
}