<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {

            // Insert ke tabel pages
            $pageId = DB::table('pages')->insertGetId([
                'slug' => 'artikel-' . $i,
                'type' => 'default',
                'page_type' => 'expose',
                'featured_image' => null,
                'published_at' => Carbon::now()->subDays(10 - $i),
                'status' => 'active',
                'source_type' => 'manual',
                'source_file' => null,
                'user_id' => 1, // pastikan user id ini ada
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Translation Indonesia
            DB::table('page_translations')->insert([
                'page_id' => $pageId,
                'locale' => 'id',
                'title' => 'Judul Artikel ' . $i,
                'excerpt' => 'Ini adalah ringkasan artikel ke-' . $i,
                'content' => '<p>Konten lengkap artikel ke-' . $i . ' dalam Bahasa Indonesia.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Translation English
            DB::table('page_translations')->insert([
                'page_id' => $pageId,
                'locale' => 'en',
                'title' => 'Article Title ' . $i,
                'excerpt' => 'This is the excerpt for article number ' . $i,
                'content' => '<p>Full content of article number ' . $i . ' in English.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
