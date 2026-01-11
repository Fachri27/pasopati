<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FellowshipSeeder extends Seeder
{
    public function run(): void
    {
        $fellowships = [
            [
                'title'     => 'Fellowship Jurnalisme Lingkungan',
                'sub_judul' => 'Mendorong Liputan Investigatif Berbasis Data',
                'excerpt'   => 'Program fellowship untuk jurnalis yang fokus pada isu lingkungan, iklim, dan keadilan ekologis.'
            ],
            [
                'title'     => 'Fellowship Riset Kebijakan Publik',
                'sub_judul' => 'Analisis Kritis atas Kebijakan Strategis',
                'excerpt'   => 'Fellowship ini mendukung peneliti muda dalam menganalisis kebijakan publik secara independen.'
            ],
            [
                'title'     => 'Fellowship Advokasi Masyarakat Adat',
                'sub_judul' => 'Memperkuat Suara Komunitas Lokal',
                'excerpt'   => 'Program pendampingan bagi aktivis yang bekerja bersama masyarakat adat.'
            ],
            [
                'title'     => 'Fellowship Media dan Demokrasi',
                'sub_judul' => 'Menjaga Ruang Publik yang Sehat',
                'excerpt'   => 'Fellowship untuk penguatan peran media dalam demokrasi dan kebebasan berekspresi.'
            ],
            [
                'title'     => 'Fellowship Ekonomi Politik',
                'sub_judul' => 'Membedah Relasi Kekuasaan dan Modal',
                'excerpt'   => 'Program ini menyoroti dinamika ekonomi politik dalam pembangunan.'
            ],
            [
                'title'     => 'Fellowship Literasi Digital',
                'sub_judul' => 'Melawan Disinformasi di Era Digital',
                'excerpt'   => 'Fellowship yang berfokus pada literasi digital dan penanggulangan hoaks.'
            ],
            [
                'title'     => 'Fellowship Keadilan Sosial',
                'sub_judul' => 'Mendorong Kesetaraan dan Inklusi',
                'excerpt'   => 'Program ini mendukung inisiatif yang memperjuangkan keadilan sosial.'
            ],
            [
                'title'     => 'Fellowship Penelitian Iklim',
                'sub_judul' => 'Mengungkap Dampak Nyata Krisis Iklim',
                'excerpt'   => 'Fellowship bagi peneliti yang meneliti dampak perubahan iklim.'
            ],
            [
                'title'     => 'Fellowship Pendidikan Kritis',
                'sub_judul' => 'Membangun Kesadaran melalui Pendidikan',
                'excerpt'   => 'Program untuk pendidik yang mengembangkan pendekatan pendidikan kritis.'
            ],
            [
                'title'     => 'Fellowship Seni dan Aktivisme',
                'sub_judul' => 'Ekspresi Kreatif untuk Perubahan Sosial',
                'excerpt'   => 'Fellowship yang menggabungkan seni, budaya, dan aktivisme sosial.'
            ],
        ];

        foreach ($fellowships as $index => $item) {

            $startDate = Carbon::now()->subDays(rand(30, 180));
            $endDate   = (clone $startDate)->addDays(rand(30, 90));

            // INSERT FELLOWSHIP
            $fellowshipId = DB::table('fellowships')->insertGetId([
                'slug'        => Str::slug($item['title']) . '-' . ($index + 1),
                'image'       => null,
                'meta_image'  => null,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'status'      => 'active',
                'user_id'     => 1, // pastikan user id ini ada
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // INSERT TRANSLATION (ID)
            DB::table('fellowship_translations')->insert([
                'fellowship_id' => $fellowshipId,
                'locale'        => 'id',
                'title'         => $item['title'],
                'sub_judul'     => $item['sub_judul'],
                'excerpt'       => $item['excerpt'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
