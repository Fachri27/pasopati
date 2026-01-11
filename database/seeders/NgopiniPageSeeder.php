<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NgopiniPageSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Pembangunan atau Pengorbanan Lingkungan?',
                'excerpt' => 'Pembangunan sering dijadikan alasan utama, namun lingkungan terus menjadi korban.'
            ],
            [
                'title' => 'Membaca Ulang Narasi Keberlanjutan Korporasi',
                'excerpt' => 'Narasi keberlanjutan kerap terdengar indah, namun perlu dibaca lebih kritis.'
            ],
            [
                'title' => 'Ketika Investasi Mengabaikan Keadilan Sosial',
                'excerpt' => 'Investasi besar tidak selalu berjalan seiring dengan keadilan sosial.'
            ],
            [
                'title' => 'Opini Publik dan Masa Depan Hutan Indonesia',
                'excerpt' => 'Opini publik memegang peranan penting dalam menjaga keberlanjutan hutan.'
            ],
            [
                'title' => 'Industrialisasi dan Krisis Iklim',
                'excerpt' => 'Industrialisasi tanpa kendali mempercepat krisis iklim global.'
            ],
            [
                'title' => 'Ekonomi Hijau: Solusi atau Ilusi?',
                'excerpt' => 'Konsep ekonomi hijau perlu diuji antara janji dan implementasi.'
            ],
            [
                'title' => 'Suara Masyarakat Lokal yang Terpinggirkan',
                'excerpt' => 'Masyarakat lokal seringkali tidak memiliki ruang dalam pengambilan keputusan.'
            ],
            [
                'title' => 'Regulasi Lemah dan Dampaknya bagi Lingkungan',
                'excerpt' => 'Lemahnya regulasi membuka ruang eksploitasi sumber daya alam.'
            ],
            [
                'title' => 'Tanggung Jawab Etis Dunia Usaha',
                'excerpt' => 'Etika bisnis seharusnya menjadi fondasi, bukan sekadar jargon.'
            ],
            [
                'title' => 'Masa Depan Pembangunan Berkelanjutan',
                'excerpt' => 'Pembangunan berkelanjutan membutuhkan komitmen nyata semua pihak.'
            ],
        ];

        foreach ($articles as $index => $article) {

            $publishedAt = Carbon::now()->subDays(rand(1, 90));

            // INSERT KE TABLE PAGES
            $pageId = DB::table('pages')->insertGetId([
                'slug'           => Str::slug($article['title']) . '-opini-' . ($index + 1),
                'type'           => 'default',
                'page_type'      => 'ngopini',
                'featured_image' => null,
                'published_at'   => $publishedAt,
                'status'         => 'active',
                'source_type'    => 'manual',
                'source_file'    => null,
                'user_id'        => 1, // pastikan user ada
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // INSERT TRANSLATION (ID)
            DB::table('page_translations')->insert([
                'page_id'    => $pageId,
                'locale'     => 'id',
                'title'      => $article['title'],
                'excerpt'    => $article['excerpt'],
                'content'    => '
                    <p>Artikel opini ini merupakan pandangan penulis terhadap isu lingkungan, sosial, dan kebijakan publik.</p>
                    <p>Seluruh isi mencerminkan sudut pandang personal yang bertujuan mendorong diskursus publik.</p>
                ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
