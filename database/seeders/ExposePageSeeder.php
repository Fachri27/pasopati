<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExposePageSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Ekspansi Industri Pulp dan Dampaknya terhadap Lingkungan',
                'excerpt' => 'Investigasi mendalam mengenai ekspansi industri pulp serta implikasinya terhadap ekosistem dan masyarakat sekitar.'
            ],
            [
                'title' => 'Konflik Lahan antara Korporasi dan Masyarakat Adat',
                'excerpt' => 'Laporan khusus mengenai konflik agraria yang melibatkan perusahaan besar dan komunitas adat.'
            ],
            [
                'title' => 'Jejak Emisi Karbon di Balik Produksi Kertas',
                'excerpt' => 'Produksi kertas menyumbang emisi karbon signifikan yang jarang dibahas secara terbuka.'
            ],
            [
                'title' => 'Deforestasi Terstruktur dalam Industri Kehutanan',
                'excerpt' => 'Pola deforestasi yang terjadi secara sistematis dan berulang di kawasan hutan produksi.'
            ],
            [
                'title' => 'Monopoli Lahan dan Krisis Ekologi',
                'excerpt' => 'Penguasaan lahan skala besar berkontribusi terhadap krisis ekologi yang semakin nyata.'
            ],
            [
                'title' => 'Rantai Pasok Industri Pulp: Siapa Diuntungkan?',
                'excerpt' => 'Menelusuri rantai pasok industri pulp dan pihak-pihak yang paling diuntungkan.'
            ],
            [
                'title' => 'Korporasi, Regulasi, dan Celah Pengawasan',
                'excerpt' => 'Celah regulasi memungkinkan praktik industri yang merugikan lingkungan terus berlangsung.'
            ],
            [
                'title' => 'Kehilangan Keanekaragaman Hayati Akibat HTI',
                'excerpt' => 'Hutan tanaman industri mengubah lanskap dan mengancam keanekaragaman hayati.'
            ],
            [
                'title' => 'Masyarakat Lokal di Tengah Tekanan Industri',
                'excerpt' => 'Tekanan sosial dan ekonomi yang dihadapi masyarakat lokal akibat ekspansi industri.'
            ],
            [
                'title' => 'Janji Keberlanjutan dan Realitas di Lapangan',
                'excerpt' => 'Mengulas kesenjangan antara klaim keberlanjutan perusahaan dan kondisi nyata.'
            ],
        ];

        foreach ($articles as $index => $article) {

            $publishedAt = Carbon::now()->subDays(rand(1, 120));

            // INSERT PAGE
            $pageId = DB::table('pages')->insertGetId([
                'slug'           => Str::slug($article['title']) . '-' . ($index + 1),
                'type'           => 'default',
                'page_type'      => 'expose',
                'featured_image' => null, // bisa diisi nanti
                'published_at'   => $publishedAt,
                'status'         => 'active',
                'source_type'    => 'manual',
                'source_file'    => null,
                'user_id'        => 1, // pastikan user id 1 ada
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // INSERT TRANSLATION (ID)
            DB::table('page_translations')->insert([
                'page_id'    => $pageId,
                'locale'     => 'id',
                'title'      => $article['title'],
                'excerpt'    => $article['excerpt'],
                'content'    => '<p>Konten lengkap artikel expose ini membahas isu lingkungan, sosial, dan kebijakan industri secara mendalam.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
