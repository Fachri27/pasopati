<?php

namespace Tests\Feature;

use App\Models\DeforestoryCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman publik /{locale}/deforestory/{slug} — khusus jalur PREVIEW: slug
 * terdaftar sebagai card (DeforestoryCard) tapi belum ada DeforestoryCase di CMS.
 * Halaman ini wajib tampil KOSONG (judul asli + pesan "belum ada laporan"),
 * tanpa konten dummy Mayawana hardcode.
 */
class DeforestoryCasePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_page_renders_empty_without_dummy_content(): void
    {
        // Card terdaftar (publish) tapi TANPA DeforestoryCase → jalur previewFromApi.
        DeforestoryCard::create([
            'slug' => 'bojong-3',
            'status' => 'publish',
            'title_id' => 'Bojong 3',
            'title_en' => 'Bojong 3',
            'sort' => 1,
        ]);

        $response = $this->get('/id/deforestory/bojong-3');

        $response->assertStatus(200);
        // Judul asli dari card tampil.
        $response->assertSee('Bojong 3', false);
        // Pesan empty-state tampil.
        $response->assertSeeText('Belum ada laporan untuk kasus ini');
        // Konten dummy Mayawana hardcode TIDAK boleh muncul lagi.
        $response->assertDontSeeText('Hutan Mayawana di radar rantai pasok');
        $response->assertDontSeeText('Angka di balik klaim keberlanjutan');
        $response->assertDontSeeText('Meski deforestasi nasional');
        $response->assertDontSee('photo-1542273917363-3b1817f69a2d');
    }

    public function test_preview_page_draft_card_redirects_or_404(): void
    {
        // Card DRAFT (tersembunyi) & tanpa CMS case → gak tampil di publik.
        DeforestoryCard::create([
            'slug' => 'bojong-3',
            'status' => 'draft',
            'title_id' => 'Bojong 3',
            'sort' => 1,
        ]);

        // cardBySlug filter 'publish' → null → findApiCard null → emptyArchiveResponse (200).
        $response = $this->get('/id/deforestory/bojong-3');
        $response->assertStatus(200);
        $response->assertSeeText('Arsip kasus ini belum dibuat');
    }
}