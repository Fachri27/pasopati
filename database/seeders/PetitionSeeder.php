<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PetitionSeeder extends Seeder
{
    public function run(): void
    {
        $petitions = [
            [
                'title_id' => 'Hentikan Deforestasi di Kalimantan',
                'title_en' => 'Stop Deforestation in Kalimantan',
                'target_name' => 'Presiden Republik Indonesia',
                'description_id' => 'Kami mendesak pemerintah untuk segera menghentikan praktik deforestasi di wilayah Kalimantan yang telah merusak habitat alami dan mengancam kehidupan masyarakat adat. Data menunjukkan bahwa luas hutan Kalimantan telah berkurang drastis dalam satu dekade terakhir.',
                'description_en' => 'We urge the government to immediately stop deforestation practices in Kalimantan that have destroyed natural habitats and threatened indigenous communities. Data shows that Kalimantan\'s forest area has decreased drastically in the last decade.',
                'demands' => ['Moratorium izin baru pembukaan lahan hutan', 'Penegakan hukum terhadap pelaku illegal logging', 'Rehabilitasi lahan kritis seluas 2 juta hektar', 'Pengakuan hak kelola hutan masyarakat adat'],
                'goal_count' => 10000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title_id' => 'Tolak Tambang Emas di Pulau Sulawesi',
                'title_en' => 'Reject Gold Mining in Sulawesi',
                'target_name' => 'Menteri Energi dan Sumber Daya Mineral',
                'description_id' => 'Rencana pembukaan tambang emas di kawasan karst Sulawesi mengancam sumber air bersih bagi jutaan penduduk. Kawasan karst memiliki fungsi vital sebagai reservoir air alami yang tidak bisa dipulihkan jika rusak.',
                'description_en' => 'The plan to open gold mines in the karst area of Sulawesi threatens clean water sources for millions of residents. Karst areas have a vital function as natural water reservoirs that cannot be restored if damaged.',
                'demands' => ['Pembatalan izin tambang di kawasan karst', 'Kajian akademik independen dampak lingkungan', 'Pelibatan masyarakat dalam setiap keputusan tata ruang'],
                'goal_count' => 15000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title_id' => 'Lindungi Hutan Adat dari Kebakaran',
                'title_en' => 'Protect Indigenous Forests from Fires',
                'target_name' => 'Menteri Lingkungan Hidup dan Kehutanan',
                'description_id' => 'Kebakaran hutan yang terjadi setiap tahun di wilayah hutan adat membutuhkan penanganan serius. Masyarakat adat sebagai garda terdepan pelindung hutan harus diperkuat dan didukung penuh oleh negara.',
                'description_en' => 'Forest fires that occur every year in indigenous forest areas require serious handling. Indigenous communities as the front line of forest protection must be strengthened and fully supported by the state.',
                'demands' => ['Pembentukan satgas pengendali kebakaran hutan adat', 'Anggaran khusus perlindungan hutan adat', 'Sanksi tegas bagi korporasi penyebab kebakaran'],
                'goal_count' => 7500,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(7),
            ],
            [
                'title_id' => 'Kampanye Udara Bersih untuk Jakarta',
                'title_en' => 'Clean Air Campaign for Jakarta',
                'target_name' => 'Gubernur DKI Jakarta',
                'description_id' => 'Kualitas udara Jakarta terus memburuk dan melebihi batas amar. Kami mendesak pemerintah provinsi untuk mengambil langkah konkret mengatasi polusi udara yang mengancam kesehatan 10 juta warga Jakarta.',
                'description_en' => 'Jakarta\'s air quality continues to deteriorate and exceeds safe limits. We urge the provincial government to take concrete steps to address air pollution that threatens the health of 10 million Jakarta residents.',
                'demands' => ['Penerapan kebijakan ganjil-genap 24 jam', 'Percepatan elektrifikasi transportasi umum', 'Pengawasan ketat emisi industri', 'Penambahan ruang terbuka hijau 30%'],
                'goal_count' => 20000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(1),
            ],
            [
                'title_id' => 'Selamatkan Terumbu Karang Raja Ampat',
                'title_en' => 'Save Raja Ampat Coral Reefs',
                'target_name' => 'Menteri Kelautan dan Perikanan',
                'description_id' => 'Terumbu karang Raja Ampat yang menjadi warisan dunia terancam oleh penangkapan ikan destruktif dan perubahan iklim. Diperlukan aksi nyata untuk melindungi ekosistem laut paling kaya biodiversitas di dunia ini.',
                'description_en' => 'Raja Ampat\'s coral reefs, a world heritage site, are threatened by destructive fishing and climate change. Concrete action is needed to protect this most biodiverse marine ecosystem in the world.',
                'demands' => ['Perluasan kawasan konservasi laut', 'Larangan penangkapan ikan destruktif', 'Program restorasi karang berbasis komunitas', 'Penguatan pengawasan wilayah perairan'],
                'goal_count' => 12000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'title_id' => 'Hentikan Pencemaran Sungai Citarum',
                'title_en' => 'Stop Citarum River Pollution',
                'target_name' => 'Pemerintah Provinsi Jawa Barat',
                'description_id' => 'Sungai Citarum yang disebut sebagai sungai terkotor di dunia membutuhkan penanganan serius dan berkelanjutan. Ribuan ton limbah industri dan domestik setiap hari dibuang ke sungai ini.',
                'description_en' => 'The Citarum River, called the dirtiest river in the world, needs serious and sustainable handling. Thousands of tons of industrial and domestic waste are dumped into this river every day.',
                'demands' => ['Penutupan pabrik pembuang limbah ilegal', 'Pembangunan IPAL terpadu di sepanjang DAS Citarum', 'Program bersih sungai berbasis partisipasi warga', 'Sanksi pidana bagi korporasi pencemar'],
                'goal_count' => 25000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title_id' => 'Petisi Perlindungan Orangutan Sumatera',
                'title_en' => 'Sumatran Orangutan Protection Petition',
                'target_name' => 'Kementerian Lingkungan Hidup dan Kehutanan',
                'description_id' => 'Populasi orangutan sumatera terus menurun drastis akibat hilangnya habitat dan perburuan. Spesies kritis ini terancam punah jika tidak ada tindakan perlindungan yang lebih ketat.',
                'description_en' => 'The Sumatran orangutan population continues to decline drastically due to habitat loss and poaching. This critically endangered species faces extinction without stricter protection measures.',
                'demands' => ['Koridor satwa yang terhubung antar kawasan konservasi', 'Peningkatan sanksi perburuan satwa dilindungi', 'Moratorium konversi hutan orangutan', 'Program rehabilitasi dan reintroduksi'],
                'goal_count' => 8000,
                'status' => 'active',
                'published_at' => Carbon::now()->subDays(15),
            ],
            [
                'title_id' => 'Dorong Energi Terbarukan di Indonesia',
                'title_en' => 'Promote Renewable Energy in Indonesia',
                'target_name' => 'Presiden Republik Indonesia',
                'description_id' => 'Indonesia masih sangat bergantung pada energi fosil. Kami mendesak transisi energi yang lebih cepat menuju energi terbarukan untuk mencapai target net zero emission dan masa depan yang berkelanjutan.',
                'description_en' => 'Indonesia is still heavily dependent on fossil fuels. We urge a faster energy transition towards renewable energy to achieve net zero emission targets and a sustainable future.',
                'demands' => ['Penghapusan bertahap PLTU batu bara', 'Insentif investasi energi surya dan angin', 'Pengembangan infrastruktur grid cerdas', 'Target 50% energi terbarukan tahun 2035'],
                'goal_count' => 18000,
                'status' => 'draft',
                'published_at' => null,
            ],
            [
                'title_id' => 'Lindungi Wilayah Pesisir dari Abrasi',
                'title_en' => 'Protect Coastal Areas from Abrasion',
                'target_name' => 'Menteri Pekerjaan Umum',
                'description_id' => 'Abrasi pesisir telah mengancam pemukiman warga di berbagai wilayah Indonesia. Diperlukan penanganan terpadu dengan pendekatan infrastruktur alami dan rekayasa teknik.',
                'description_en' => 'Coastal abrasion has threatened residential areas in various regions of Indonesia. Integrated handling with natural infrastructure and engineering approaches is needed.',
                'demands' => ['Pembangunan infrastruktur pelindung pantai', 'Penanaman mangrove di zona abrasi', 'Relokasi terencana untuk warga terdampak', 'Larangan penambangan pasir laut'],
                'goal_count' => 6000,
                'status' => 'draft',
                'published_at' => null,
            ],
            [
                'title_id' => 'Hentikan Perburuan Satwa Liar di Indonesia',
                'title_en' => 'Stop Wildlife Poaching in Indonesia',
                'target_name' => 'Kepala Kepolisian Republik Indonesia',
                'description_id' => 'Perburuan satwa liar di Indonesia masih marak dan mengancam keberlangsungan spesies langka. Jaringan perdagangan satwa ilegal harus dibongkar hingga ke akarnya.',
                'description_en' => 'Wildlife poaching in Indonesia is still rampant and threatens the survival of rare species. Illegal wildlife trade networks must be dismantled to the root.',
                'demands' => ['Pembentukan satgas anti-perburuan nasional', 'Penguatan hukuman bagi pelaku perdagangan satwa', 'Kerjasama internasional pemberantasan jaringan global', 'Edukasi konservasi di masyarakat lokal'],
                'goal_count' => 5000,
                'status' => 'closed',
                'published_at' => Carbon::now()->subDays(30),
            ],
        ];

        $userIds = DB::table('users')->pluck('id')->toArray();
        $defaultUserId = $userIds[0] ?? 1;

        foreach ($petitions as $i => $data) {
            $publishedAt = $data['published_at'];

            $petitionId = DB::table('petitions')->insertGetId([
                'slug' => Str::slug($data['title_id']).'-'.($i + 1),
                'target_name' => $data['target_name'],
                'demands' => json_encode($data['demands']),
                'cover_image' => null,
                'goal_count' => $data['goal_count'],
                'status' => $data['status'],
                'published_at' => $publishedAt,
                'user_id' => $defaultUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ID translation
            DB::table('petition_translations')->insert([
                'petition_id' => $petitionId,
                'locale' => 'id',
                'title' => $data['title_id'],
                'description' => $data['description_id'],
            ]);

            // EN translation
            DB::table('petition_translations')->insert([
                'petition_id' => $petitionId,
                'locale' => 'en',
                'title' => $data['title_en'],
                'description' => $data['description_en'],
            ]);

            // Dummy signatures for active/draft/closed petitions
            if ($data['status'] === 'active' || $data['status'] === 'closed') {
                $daysActive = $publishedAt ? Carbon::now()->diffInDays($publishedAt) : 1;
                $numVerified = rand(50, min(500, (int) ($data['goal_count'] * 0.15)));
                $numUnverified = rand(5, 20);

                for ($s = 1; $s <= $numVerified; $s++) {
                    DB::table('petition_signatures')->insert([
                        'petition_id' => $petitionId,
                        'name' => fake()->name('id_ID'),
                        'email' => fake()->safeEmail(),
                        'city' => fake()->city(),
                        'comment' => $s % 4 === 0 ? fake()->sentence() : null,
                        'is_verified' => true,
                        'verification_token' => null,
                        'ip_address' => fake()->ipv4(),
                        'created_at' => Carbon::now()->subDays(rand(0, $daysActive)),
                    ]);
                }

                for ($s = 1; $s <= $numUnverified; $s++) {
                    DB::table('petition_signatures')->insert([
                        'petition_id' => $petitionId,
                        'name' => fake()->name('id_ID'),
                        'email' => fake()->safeEmail(),
                        'city' => fake()->city(),
                        'comment' => null,
                        'is_verified' => false,
                        'verification_token' => Str::random(64),
                        'ip_address' => fake()->ipv4(),
                        'created_at' => Carbon::now()->subHours(rand(1, 72)),
                    ]);
                }

                $this->command->info("  [{$data['status']}] {$data['title_id']}: {$numVerified} verified, {$numUnverified} unverified signatures");
            }
        }

        $this->command->info('Petisi seeder selesai!');
    }
}
