<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FellowshipSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: bersihkan relasi lama dulu agar re-run tidak bentrok
        // constraint unik (slug, [fellowship_id+locale], [fellowship_id+kategori_id]).
        // MySQL menolak truncate tabel yang dirujuk FK, jadi matikan
        // cek FK sementara selama proses pembersihan.
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('kategori_fellowships')->truncate();
        DB::table('fellowship_translations')->truncate();
        DB::table('fellowships')->truncate();
        DB::table('kategori_translations')->truncate();
        DB::table('kategoris')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // --- Kategori (dipakai sebagai relasi many-to-many pada Fellowship) ---
        $kategoris = [
            [
                'id' => 'Lingkungan & Iklim',
                'en' => 'Environment & Climate',
            ],
            [
                'id' => 'Kebijakan Publik',
                'en' => 'Public Policy',
            ],
            [
                'id' => 'Media & Demokrasi',
                'en' => 'Media & Democracy',
            ],
            [
                'id' => 'Keadilan Sosial',
                'en' => 'Social Justice',
            ],
            [
                'id' => 'Pendidikan & Literasi',
                'en' => 'Education & Literacy',
            ],
        ];

        $kategoriIds = [];
        foreach ($kategoris as $kategori) {
            $kategoriId = DB::table('kategoris')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kategori_translations')->insert([
                'kategori_id' => $kategoriId,
                'locale' => 'id',
                'kategori_name' => $kategori['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kategori_translations')->insert([
                'kategori_id' => $kategoriId,
                'locale' => 'en',
                'kategori_name' => $kategori['en'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $kategoriIds[] = $kategoriId;
        }

        // --- Fellowships (dengan terjemahan id + en) ---
        $fellowships = [
            [
                'id' => [
                    'title' => 'Fellowship Jurnalisme Lingkungan',
                    'sub_judul' => 'Mendorong Liputan Investigatif Berbasis Data',
                    'excerpt' => 'Program fellowship untuk jurnalis yang fokus pada isu lingkungan, iklim, dan keadilan ekologis.',
                ],
                'en' => [
                    'title' => 'Environmental Journalism Fellowship',
                    'sub_judul' => 'Encouraging Data-Driven Investigative Reporting',
                    'excerpt' => 'A fellowship program for journalists focusing on environmental, climate, and ecological justice issues.',
                ],
                'kategori_index' => [0], // Lingkungan & Iklim
            ],
            [
                'id' => [
                    'title' => 'Fellowship Riset Kebijakan Publik',
                    'sub_judul' => 'Analisis Kritis atas Kebijakan Strategis',
                    'excerpt' => 'Fellowship ini mendukung peneliti muda dalam menganalisis kebijakan publik secara independen.',
                ],
                'en' => [
                    'title' => 'Public Policy Research Fellowship',
                    'sub_judul' => 'Critical Analysis of Strategic Policy',
                    'excerpt' => 'This fellowship supports young researchers in independently analyzing public policy.',
                ],
                'kategori_index' => [1], // Kebijakan Publik
            ],
            [
                'id' => [
                    'title' => 'Fellowship Advokasi Masyarakat Adat',
                    'sub_judul' => 'Memperkuat Suara Komunitas Lokal',
                    'excerpt' => 'Program pendampingan bagi aktivis yang bekerja bersama masyarakat adat.',
                ],
                'en' => [
                    'title' => 'Indigenous Advocacy Fellowship',
                    'sub_judul' => 'Amplifying Local Community Voices',
                    'excerpt' => 'An accompaniment program for activists working alongside indigenous communities.',
                ],
                'kategori_index' => [3], // Keadilan Sosial
            ],
            [
                'id' => [
                    'title' => 'Fellowship Media dan Demokrasi',
                    'sub_judul' => 'Menjaga Ruang Publik yang Sehat',
                    'excerpt' => 'Fellowship untuk penguatan peran media dalam demokrasi dan kebebasan berekspresi.',
                ],
                'en' => [
                    'title' => 'Media and Democracy Fellowship',
                    'sub_judul' => 'Safeguarding a Healthy Public Sphere',
                    'excerpt' => 'A fellowship strengthening the role of media in democracy and freedom of expression.',
                ],
                'kategori_index' => [2], // Media & Demokrasi
            ],
            [
                'id' => [
                    'title' => 'Fellowship Ekonomi Politik',
                    'sub_judul' => 'Membedah Relasi Kekuasaan dan Modal',
                    'excerpt' => 'Program ini menyoroti dinamika ekonomi politik dalam pembangunan.',
                ],
                'en' => [
                    'title' => 'Political Economy Fellowship',
                    'sub_judul' => 'Unpacking Power and Capital Relations',
                    'excerpt' => 'This program highlights political economy dynamics in development.',
                ],
                'kategori_index' => [1, 2], // Kebijakan Publik + Media & Demokrasi
            ],
            [
                'id' => [
                    'title' => 'Fellowship Literasi Digital',
                    'sub_judul' => 'Melawan Disinformasi di Era Digital',
                    'excerpt' => 'Fellowship yang berfokus pada literasi digital dan penanggulangan hoaks.',
                ],
                'en' => [
                    'title' => 'Digital Literacy Fellowship',
                    'sub_judul' => 'Countering Disinformation in the Digital Age',
                    'excerpt' => 'A fellowship focused on digital literacy and countering hoaxes.',
                ],
                'kategori_index' => [4], // Pendidikan & Literasi
            ],
            [
                'id' => [
                    'title' => 'Fellowship Keadilan Sosial',
                    'sub_judul' => 'Mendorong Kesetaraan dan Inklusi',
                    'excerpt' => 'Program ini mendukung inisiatif yang memperjuangkan keadilan sosial.',
                ],
                'en' => [
                    'title' => 'Social Justice Fellowship',
                    'sub_judul' => 'Advancing Equality and Inclusion',
                    'excerpt' => 'This program supports initiatives advocating for social justice.',
                ],
                'kategori_index' => [3], // Keadilan Sosial
            ],
            [
                'id' => [
                    'title' => 'Fellowship Penelitian Iklim',
                    'sub_judul' => 'Mengungkap Dampak Nyata Krisis Iklim',
                    'excerpt' => 'Fellowship bagi peneliti yang meneliti dampak perubahan iklim.',
                ],
                'en' => [
                    'title' => 'Climate Research Fellowship',
                    'sub_judul' => 'Uncovering the Real Impacts of the Climate Crisis',
                    'excerpt' => 'A fellowship for researchers studying the impacts of climate change.',
                ],
                'kategori_index' => [0], // Lingkungan & Iklim
            ],
            [
                'id' => [
                    'title' => 'Fellowship Pendidikan Kritis',
                    'sub_judul' => 'Membangun Kesadaran melalui Pendidikan',
                    'excerpt' => 'Program untuk pendidik yang mengembangkan pendekatan pendidikan kritis.',
                ],
                'en' => [
                    'title' => 'Critical Education Fellowship',
                    'sub_judul' => 'Building Awareness through Education',
                    'excerpt' => 'A program for educators developing critical pedagogy approaches.',
                ],
                'kategori_index' => [4], // Pendidikan & Literasi
            ],
            [
                'id' => [
                    'title' => 'Fellowship Seni dan Aktivisme',
                    'sub_judul' => 'Ekspresi Kreatif untuk Perubahan Sosial',
                    'excerpt' => 'Fellowship yang menggabungkan seni, budaya, dan aktivisme sosial.',
                ],
                'en' => [
                    'title' => 'Arts and Activism Fellowship',
                    'sub_judul' => 'Creative Expression for Social Change',
                    'excerpt' => 'A fellowship combining art, culture, and social activism.',
                ],
                'kategori_index' => [3], // Keadilan Sosial
            ],
        ];

        foreach ($fellowships as $index => $item) {

            $startDate = Carbon::now()->subDays(rand(30, 180));
            $endDate = (clone $startDate)->addDays(rand(30, 90));

            // INSERT FELLOWSHIP
            $fellowshipId = DB::table('fellowships')->insertGetId([
                'slug' => Str::slug($item['id']['title']).'-'.($index + 1),
                'image' => null,
                'meta_image' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'user_id' => 1, // pastikan user id ini ada
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // INSERT TRANSLATION (ID)
            DB::table('fellowship_translations')->insert([
                'fellowship_id' => $fellowshipId,
                'locale' => 'id',
                'title' => $item['id']['title'],
                'sub_judul' => $item['id']['sub_judul'],
                'excerpt' => $item['id']['excerpt'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // INSERT TRANSLATION (EN)
            DB::table('fellowship_translations')->insert([
                'fellowship_id' => $fellowshipId,
                'locale' => 'en',
                'title' => $item['en']['title'],
                'sub_judul' => $item['en']['sub_judul'],
                'excerpt' => $item['en']['excerpt'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ATTACH KATEGORI (pivot) + content HTML (id & en)
            [$contentId, $contentEn] = $this->buildContent($item);

            foreach ($item['kategori_index'] as $ki) {
                DB::table('kategori_fellowships')->insert([
                    'fellowship_id' => $fellowshipId,
                    'kategori_id' => $kategoriIds[$ki],
                    'content_id' => $contentId,
                    'content_en' => $contentEn,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Bangun body HTML (id & en) untuk content pivot kategori.
     * Konten dipakai ulang untuk setiap kategori yang dikaitkan pada
     * fellowship bersangkutan.
     */
    private function buildContent(array $item): array
    {
        $id = $item['id'];
        $en = $item['en'];

        $contentId = '<h2>'.e($id['title']).'</h2>'
            .'<p><strong>'.e($id['sub_judul']).'</strong></p>'
            .'<p>'.e($id['excerpt']).'</p>'
            .'<h3>Latar Belakang</h3>'
            .'<p>Program ini lahir dari kebutuhan untuk memperkuat kapasitas para '
            .'praktisi dan peneliti independen dalam menjawab tantangan kontemporer '
            .'yang relevan dengan tema '.e($id['title']).'. '
            .'Melalui pendampingan, pelatihan, dan dukungan produksi karya, '
            .'para fellow diharapkan mampu menghasilkan liputan atau karya yang '
            .'berbasis data, kritis, dan berdampak pada publik.</p>'
            .'<h3>Tujuan</h3>'
            .'<ul>'
            .'<li>Memperkuat kapasitas individu melalui pendampingan terstruktur.</li>'
            .'<li>Memperluas jaringan antar fellow, mentor, dan komunitas terkait.</li>'
            .'<li>Mendorong lahirnya karya yang berbasis data dan berpijak pada konteks lokal.</li>'
            .'<li>Menyuarakan isu yang selama ini kurang mendapat perhatian publik.</li>'
            .'</ul>'
            .'<h3>Linimasa</h3>'
            .'<p>Program berlangsung selama beberapa bulan, mencakup sesi orientasi, '
            .'pendampingan berkala dengan mentor, serta publikasi karya akhir fellow. '
            .'Detail jadwal akan diumumkan kepada peserta terpilih.</p>'
            .'<h3>Cara Mendaftar</h3>'
            .'<p>Pendaftaran dibuka secara daring melalui formulir yang tersedia pada '
            .'periode pendaftaran. Pastikan kamu menyiapkan proposal singkat, portofolio, '
            .'dan motivasi mengikuti program ini sebelum mengirimkan lamaran.</p>';

        $contentEn = '<h2>'.e($en['title']).'</h2>'
            .'<p><strong>'.e($en['sub_judul']).'</strong></p>'
            .'<p>'.e($en['excerpt']).'</p>'
            .'<h3>Background</h3>'
            .'<p>This program was created to strengthen the capacity of independent '
            .'practitioners and researchers in addressing contemporary challenges '
            .'relevant to the '.e($en['title']).' theme. '
            .'Through mentorship, training, and production support, fellows are '
            .'expected to produce data-driven, critical, and publicly impactful work.</p>'
            .'<h3>Objectives</h3>'
            .'<ul>'
            .'<li>Strengthen individual capacity through structured mentorship.</li>'
            .'<li>Expand networks among fellows, mentors, and related communities.</li>'
            .'<li>Encourage data-driven work grounded in local context.</li>'
            .'<li>Amplify issues that have long received little public attention.</li>'
            .'</ul>'
            .'<h3>Timeline</h3>'
            .'<p>The program runs over several months, covering an orientation session, '
            .'regular mentoring with mentors, and the publication of the fellows&rsquo; '
            .'final work. Detailed schedules will be announced to selected participants.</p>'
            .'<h3>How to Apply</h3>'
            .'<p>Applications open online via the form available during the application '
            .'period. Please prepare a brief proposal, portfolio, and your motivation '
            .'for joining the program before submitting your application.</p>';

        return [$contentId, $contentEn];
    }
}
