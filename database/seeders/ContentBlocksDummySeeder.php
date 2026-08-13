<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentBlocksDummySeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'simulasi-dampak-hti-versus-ekologi';

        $pageId = DB::table('pages')->insertGetId([
            'slug' => $slug,
            'type' => 'default',
            'page_type' => 'expose',
            'featured_image' => null,
            'published_at' => Carbon::now()->subDays(3),
            'status' => 'active',
            'source_type' => 'manual',
            'source_file' => null,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ID translation
        DB::table('page_translations')->insert([
            'page_id' => $pageId,
            'locale' => 'id',
            'title' => 'Simulasi Dampak HTI versus Ekologi',
            'excerpt' => 'Bagaimana ekspansi hutan tanaman industri mengubah lanskap, mengancam biodiversitas, dan memicu konflik agraria di berbagai daerah.',
            'content' => '<p>Konten paragraf lama — tidak dipakai karena content_blocks sudah tersedia.</p>',
            'content_blocks' => json_encode([
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Ekspansi <b>Hutan Tanaman Industri</b> (HTI) di Indonesia terus meluas. Dalam dua dekade terakhir, luas konsesi HTI telah mencapai lebih dari <strong>14 juta hektar</strong>—setara dengan luas Pulau Jawa. Namun, di balik angka produksi kayu yang menggiurkan, ada ongkos ekologis yang jarang dihitung.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Konsesi HTI di Kalimantan Barat terlihat dari citra satelit. Garis-garis lurus menandakan plantasi monokultur yang menggantikan hutan alam.',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Penelitian terbaru menunjukkan bahwa konversi hutan alam menjadi HTI menyebabkan <b>penurunan keanekaragaman hayati hingga 60%</b>. Spesies endemik seperti orangutan, harimau sumatra, dan burung rangkong kehilangan habitatnya. Lebih jauh lagi, masyarakat adat yang selama bergantung pada hutan kehilangan akses terhadap sumber daya alam.</p>',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'Hutan bukan sekadar kumpulan pohon yang bisa ditebang dan ditanam ulang. Ia adalah sistem kehidupan yang membutuhkan waktu ribuan tahun untuk terbentuk.',
                        'source' => 'Dr. Rina Wulandari, Ekolog Hutan Tropis',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Dalam rangka membahas temuan ini secara lebih mendalam, <b>Pasopati</b> menggelar diskusi publik yang menghadirkan para ahli ekologi, perwakilan masyarakat adat, dan peneliti kebijakan kehutanan.</p>',
                    ],
                ],
                [
                    'type' => 'event_info_box',
                    'data' => [
                        'format' => 'Hybrid (Online + Offline)',
                        'date' => '2026-07-15',
                        'time' => '13:00 – 16:00 WIB',
                        'venue' => 'Auditorium Sjahrir, Jakarta & Zoom Meeting',
                        'registration_links' => [
                            ['day' => 'Hari 1 (Offline)', 'url' => 'https://forms.example.com/daftar-offline'],
                            ['day' => 'Hari 1 (Online)',  'url' => 'https://zoom.us/webinar/register/xxx'],
                        ],
                        'notes' => 'Pendaftaran ditutup H-1. Peserta online akan menerima link Zoom H+2 jam sebelum acara.',
                    ],
                ],
                [
                    'type' => 'agenda_day',
                    'data' => [
                        'day' => '2026-07-15',
                        'sessions' => [
                            [
                                'time' => '13:00 – 13:30',
                                'title' => 'Pembukaan & Paparan Riset: Dampak HTI terhadap Biodiversitas',
                                'description' => 'Pemaparan hasil riset lapangan di tiga provinsi: Kalbar, Kaltim, dan Riau.',
                                'moderator' => 'Aulia Rahman',
                                'commentator' => 'Dr. Fitriani Hasan',
                                'speakers' => 'Prof. Bambang Setiadi, Dr. Dewi Sartika',
                            ],
                            [
                                'time' => '13:30 – 14:15',
                                'title' => 'Panel 1: Masyarakat Adat di Tengah Ekspansi HTI',
                                'description' => 'Kesaksian dari perwakilan komunitas adat yang wilayahnya terkena konsesi.',
                                'moderator' => 'Sari Dewi',
                                'commentator' => '',
                                'speakers' => 'Mama Yosepha Alor, Bapak Dominggus Rumere',
                            ],
                            [
                                'time' => '14:15 – 15:00',
                                'title' => 'Panel 2: Celah Regulasi dan Pengawasan',
                                'description' => 'Diskusi tentang kelemahan tata kelola izin HTI dan rekomendasi kebijakan.',
                                'moderator' => 'Aulia Rahman',
                                'commentator' => 'Dr. Fitriani Hasan',
                                'speakers' => 'M. Rizky Hidayat, SH, Yulius Pratama',
                            ],
                            [
                                'time' => '15:00 – 15:45',
                                'title' => 'Diskusi Terbuka & Tanya Jawab',
                                'description' => 'Sesi interaktif dengan audiens — baik offline maupun online.',
                                'moderator' => 'Sari Dewi',
                                'commentator' => '',
                                'speakers' => '',
                            ],
                            [
                                'time' => '15:45 – 16:00',
                                'title' => 'Penutup & Rilis Pernyataan Bersama',
                                'description' => 'Pembacaan rekomendasi dan kesimpulan diskusi.',
                                'moderator' => 'Aulia Rahman',
                                'commentator' => '',
                                'speakers' => '',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Berikut adalah para panelis yang akan membagikan perspektif mereka dalam diskusi nanti.</p>',
                    ],
                ],
                [
                    'type' => 'speaker_bio',
                    'data' => [
                        'photo' => 'pages/default-user.jpg',
                        'name' => 'Prof. Bambang Setiadi',
                        'title' => 'Guru Besar Ekologi Hutan, Universitas Gadjah Mada',
                        'bio' => 'Peneliti utama perubahan tutupan hutan di Indonesia dengan lebih dari 25 tahun pengalaman riset di Sumatera, Kalimantan, dan Papua.',
                    ],
                ],
                [
                    'type' => 'speaker_bio',
                    'data' => [
                        'photo' => 'pages/default-user.jpg',
                        'name' => 'Mama Yosepha Alor',
                        'title' => 'Ketua Adat Masyarakat Atoin Meto, NTT',
                        'bio' => 'Aktivis hak masyarakat adat yang wilayahnya berhadapan langsung dengan konsesi HTI. Penerima penghargaan Nusantara Award 2025.',
                    ],
                ],
                [
                    'type' => 'speaker_bio',
                    'data' => [
                        'photo' => 'pages/default-user.jpg',
                        'name' => 'Dr. Dewi Sartika',
                        'title' => 'Peneliti Kebijakan Lingkungan, Auriga Nusantara',
                        'bio' => 'Fokus pada riset kebijakan kehutanan, tata kelola lahan, dan rantai pasok industri pulp & kertas.',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Suasana diskusi publik Pasopati edisi sebelumnya.',
                        'alignment' => 'full',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'Tidak ada keberlanjutan tanpa keadilan. Selama masyarakat adat masih terusir dari tanah leluhurnya, klaim "hijau" industri pulp hanyalah pencitraan.',
                        'source' => 'Mama Yosepha Alor',
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // EN translation
        DB::table('page_translations')->insert([
            'page_id' => $pageId,
            'locale' => 'en',
            'title' => 'Simulating the Impact of Industrial Plantations on Ecology',
            'excerpt' => 'How industrial forest plantation expansion transforms landscapes, threatens biodiversity, and triggers agrarian conflicts across regions.',
            'content' => '<p>Legacy paragraph content — not used because content_blocks are present.</p>',
            'content_blocks' => json_encode([
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>The expansion of <b>Industrial Forest Plantations</b> (HTI) in Indonesia continues to grow. Over the past two decades, HTI concession areas have reached more than <strong>14 million hectares</strong>—equivalent to the size of Java Island. But behind the promising timber production numbers, there is an ecological cost rarely accounted for.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'HTI concession in West Kalimantan seen from satellite imagery. Straight lines indicate monoculture plantations replacing natural forests.',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'A forest is not merely a collection of trees to be cut down and replanted. It is a living system that takes thousands of years to form.',
                        'source' => 'Dr. Rina Wulandari, Tropical Forest Ecologist',
                    ],
                ],
                [
                    'type' => 'event_info_box',
                    'data' => [
                        'format' => 'Hybrid (Online + Offline)',
                        'date' => '2026-07-15',
                        'time' => '13:00 – 16:00 WIB',
                        'venue' => 'Sjahrir Auditorium, Jakarta & Zoom Meeting',
                        'registration_links' => [
                            ['day' => 'Day 1 (Offline)', 'url' => 'https://forms.example.com/register-offline'],
                            ['day' => 'Day 1 (Online)',  'url' => 'https://zoom.us/webinar/register/xxx'],
                        ],
                        'notes' => 'Registration closes D-1. Online participants will receive Zoom link 2 hours before the event.',
                    ],
                ],
                [
                    'type' => 'agenda_day',
                    'data' => [
                        'day' => '2026-07-15',
                        'sessions' => [
                            [
                                'time' => '13:00 – 13:30',
                                'title' => 'Opening & Research Presentation: HTI Impact on Biodiversity',
                                'description' => 'Field research findings from three provinces.',
                                'moderator' => 'Aulia Rahman',
                                'commentator' => 'Dr. Fitriani Hasan',
                                'speakers' => 'Prof. Bambang Setiadi, Dr. Dewi Sartika',
                            ],
                            [
                                'time' => '13:30 – 14:15',
                                'title' => 'Panel 1: Indigenous Communities Amidst HTI Expansion',
                                'description' => 'Testimonies from indigenous community representatives.',
                                'moderator' => 'Sari Dewi',
                                'commentator' => '',
                                'speakers' => 'Mama Yosepha Alor, Mr. Dominggus Rumere',
                            ],
                            [
                                'time' => '15:45 – 16:00',
                                'title' => 'Closing & Joint Statement',
                                'description' => 'Reading of recommendations and conclusions.',
                                'moderator' => 'Aulia Rahman',
                                'commentator' => '',
                                'speakers' => '',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'speaker_bio',
                    'data' => [
                        'photo' => 'pages/default-user.jpg',
                        'name' => 'Prof. Bambang Setiadi',
                        'title' => 'Professor of Forest Ecology, Gadjah Mada University',
                        'bio' => 'Lead researcher on forest cover change in Indonesia with over 25 years of field experience.',
                    ],
                ],
                [
                    'type' => 'speaker_bio',
                    'data' => [
                        'photo' => 'pages/default-user.jpg',
                        'name' => 'Mama Yosepha Alor',
                        'title' => 'Indigenous Community Leader, Atoin Meto, NTT',
                        'bio' => 'Human rights defender whose community faces direct HTI concession conflicts.',
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Dummy content blocks article created: /id/expose/{$slug}");
    }
}
