<?php

namespace Database\Seeders;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Database\Seeder;

class DeforestoryCaseSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'mayawana';

        $case = DeforestoryCase::updateOrCreate(
            ['slug' => $slug],
            [
                'status' => 'active',
                'featured_image' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=1200&q=80',
                'category' => 'pulp',
                'year' => '2021–2025',
                'sort' => 0,
                'user_id' => null,
            ]
        );

        // Identitas kasus (judul + excerpt) per-locale. Isi laporan sekarang
        // ada di entitas DeforestoryLaporan terpisah (tiap laporan slug sendiri).
        DeforestoryCaseTranslation::updateOrCreate(
            ['case_id' => $case->id, 'locale' => 'id'],
            [
                'title' => 'Mayawana: jejak deforestasi di radius rantai pasok RGE',
                'intro' => null,
                'excerpt' => 'Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.',
                'laporan_content' => null,
                'chapters' => null,
            ]
        );

        DeforestoryCaseTranslation::updateOrCreate(
            ['case_id' => $case->id, 'locale' => 'en'],
            [
                'title' => 'Mayawana: deforestation in the RGE supply-chain radius',
                'intro' => null,
                'excerpt' => 'Spatial analysis of deforestation in Mayawana and its links to the RGE group supply chain.',
                'laporan_content' => null,
                'chapters' => null,
            ]
        );

        $this->seedLaporans($case);
    }

    protected function seedLaporans(DeforestoryCase $case): void
    {
        $laporanContentId = '<h2>Latar belakang</h2><p><strong>Mayawana</strong> adalah nama yang mulai muncul dalam dokumen-dokumen rantai pasok kayu tropis Indonesia. Daerah ini, yang sebelumnya masuk dalam peta tutupan hutan primer Kalimantan Barat, kini menunjukkan pola pembukaan lahan yang signifikan dalam kurun beberapa tahun terakhir.</p><p>Investigasi ini berangkat dari temuan citra satelit: adanya pembukaan lahan berpola geometris di sejumlah konsesi yang berada di dalam radius pengumpulan dan pengolahan milik pemasok yang diketahui mengirim bahan baku ke fasilitas grup RGE.</p><h2>Bukti spasial</h2><p>Menggunakan analisis <strong>Global Forest Watch</strong> dan citra resolusi tinggi, tim menemukan bahwa pembukaan hutan primer di sekitar Mayawana terjadi bertahap namun konsisten. Polanya tidak seperti kebakaran akibat El Niño, melainkan pembukaan sistematis dengan jalan akses dan pematangan lahan.</p><p>Dalam kurun 2021–2024, diperkirakan ±3.200 hektar hutan dibuka di dalam radius lima konsesi terkait.</p><h2>Jejak pemasok</h2><p>Grup RGE tidak beroperasi langsung di Mayawana. Namun, setidaknya tiga pemasok bahan baku kayu yang diketahui mengirim ke pabrik pulp RGE memiliki konsesi di radius 50 kilometer dari area pembukaan hutan tersebut.</p><blockquote>"Kami tidak memiliki hubungan dengan lokasi tersebut." — Pernyataan resmi RGE, diverifikasi terhadap data pemetaan pasok.</blockquote><h2>Tindak lanjut</h2><p>Kasus Mayawana menunjukkan bahwa komitmen nol deforestasi di atas kertas tidak cukup. Diperlukan transparansi rantai pasok, verifikasi spasial independen, dan tanggung jawab perusahaan terhadap pemasok tingkat ketiga.</p>';

        $laporanContentEn = '<h2>Background</h2><p><strong>Mayawana</strong> is a name increasingly appearing in tropical timber supply-chain documents. The area, previously part of the primary forest cover map of West Kalimantan, now shows a significant land-clearing pattern in recent years.</p><p>This investigation began with satellite imagery findings: geometric land clearing in several concessions within the collection and processing radius of suppliers known to feed raw material to RGE group facilities.</p><h2>Spatial evidence</h2><p>Using <strong>Global Forest Watch</strong> analysis and high-resolution imagery, the team found that primary forest clearing around Mayawana occurred gradually but consistently. The pattern is not El Niño-style fire, but systematic clearing with access roads and land maturation. Between 2021 and 2024, an estimated ±3,200 hectares of forest were cleared within the radius of five related concessions.</p><h2>The supplier trail</h2><p>The RGE group does not operate directly in Mayawana. However, at least three raw timber suppliers known to ship to RGE pulp mills hold concessions within a 50-kilometre radius of the clearing area.</p><blockquote>"We have no relationship with that location." — Official RGE statement, verified against supply-mapping data.</blockquote><h2>Follow-up</h2><p>The Mayawana case shows that paper zero-deforestation commitments are not enough. Supply-chain transparency, independent spatial verification, and corporate accountability toward third-tier suppliers are required.</p>';

        $dampakId = '<h2>Dampak di luar peta</h2><p>Deforestasi di Mayawana bukan sekadar hilangnya tutupan hijau. Area tersebut berada di dalam koridor penting yang menghubungkan beberapa blok hutan yang menjadi habitat orangutan Kalimantan dan satwa lainnya.</p><ul><li>Habitat satwa terfragmentasi dan koridor berpindah terputus.</li><li>Masyarakat lokal kehilangan akses ke sumber mata pencarian hutan.</li><li>Risiko kebakaran meningkat karena lahan gambut yang terbuka.</li></ul><h2>Catatan lapangan</h2><p>Warga di sekitar konsesi melaporkan perubahan tata air dan hilangnya akses ke area yang dahulu dipakai untuk berburu dan mengumpulkan hasil hutan bukan kayu. Citra satelit memperkuat kesaksian ini dengan menunjukkan kanal pengeringan baru.</p>';

        $dampakEn = '<h2>Losses beyond the map</h2><p>Deforestation in Mayawana is not just the loss of green cover. The area lies within a key corridor connecting several forest blocks that are habitat for Bornean orangutans and other wildlife.</p><ul><li>Wildlife habitat is fragmented and migration corridors are severed.</li><li>Local communities lose access to forest-based livelihoods.</li><li>Fire risk rises as peatland is exposed.</li></ul><h2>Field notes</h2><p>Residents near the concession report changes in the water table and the loss of access to areas once used for hunting and collecting non-timber forest products. Satellite imagery reinforces these accounts by showing new drainage canals.</p>';

        $laporans = [
            [
                'slug' => 'jejak-deforestasi-mayawana',
                'sort' => 1,
                'image' => null,
                'published_at' => '2024-11-12',
                'id' => [
                    'title' => 'Jejak deforestasi di Mayawana',
                    'excerpt' => 'Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.',
                    'content' => $laporanContentId,
                ],
                'en' => [
                    'title' => 'The deforestation trail in Mayawana',
                    'excerpt' => 'Spatial analysis of deforestation in Mayawana and its links to the RGE group supply chain.',
                    'content' => $laporanContentEn,
                ],
            ],
            [
                'slug' => 'dampak-di-luar-peta',
                'sort' => 2,
                'image' => null,
                'published_at' => '2025-06-03',
                'id' => [
                    'title' => 'Dampak di luar peta',
                    'excerpt' => 'Koridor habitat yang terputus, akses masyarakat yang hilang, dan risiko kebakaran lahan gambut.',
                    'content' => $dampakId,
                ],
                'en' => [
                    'title' => 'Losses beyond the map',
                    'excerpt' => 'Severed habitat corridors, lost community access, and rising peat-fire risk.',
                    'content' => $dampakEn,
                ],
            ],
        ];

        foreach ($laporans as $data) {
            $laporan = DeforestoryLaporan::updateOrCreate(
                ['case_id' => $case->id, 'slug' => $data['slug']],
                [
                    'image' => $data['image'],
                    'sort' => $data['sort'],
                    'status' => 'active',
                    'published_at' => $data['published_at'],
                ]
            );

            foreach (['id', 'en'] as $locale) {
                DeforestoryLaporanTranslation::updateOrCreate(
                    ['laporan_id' => $laporan->id, 'locale' => $locale],
                    [
                        'title' => $data[$locale]['title'],
                        'excerpt' => $data[$locale]['excerpt'],
                        'content' => $data[$locale]['content'],
                    ]
                );
            }
        }
    }
}
