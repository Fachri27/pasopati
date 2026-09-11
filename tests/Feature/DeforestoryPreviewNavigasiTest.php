<?php

namespace Tests\Feature;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Support\DeforestoryPreviewLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menelusuri pratinjau sebagai peninjau LUAR — tanpa sesi CMS.
 *
 * Kelas kekeliruan yang ditutup di sini punya satu sifat menyebalkan: ia tak
 * terlihat dari kursi editor. Satu tautan yang lupa ditandatangani tetap
 * berfungsi bagi siapa pun yang sedang login CMS, dan baru gagal di tangan
 * orang yang tautannya justru dibuat untuk mereka. Karena itu setiap tautan
 * yang bisa ditekan di dalam pratinjau benar-benar DIIKUTI di sini, bukan
 * sekadar dicocokkan bentuknya.
 */
class DeforestoryPreviewNavigasiTest extends TestCase
{
    use RefreshDatabase;

    private DeforestoryCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->case = DeforestoryCase::create(['slug' => 'mayawana', 'status' => 'active', 'sort' => 1]);
        DeforestoryCaseTranslation::create([
            'case_id' => $this->case->id, 'locale' => 'id', 'title' => 'Mayawana', 'excerpt' => 'Kasus.',
        ]);

        $laporan = DeforestoryLaporan::create([
            'case_id' => $this->case->id, 'slug' => 'laporan-draft', 'status' => 'draft', 'sort' => 0,
        ]);
        foreach (['id' => 'Judul Indonesia', 'en' => 'English Title'] as $locale => $judul) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporan->id, 'locale' => $locale,
                'title' => $judul, 'excerpt' => 'Ringkasan.', 'content' => '<p>Isi laporan.</p>',
            ]);
        }
    }

    private function link(): DeforestoryPreviewLink
    {
        return app(DeforestoryPreviewLink::class);
    }

    /** Semua href yang bisa ditekan di dalam halaman pratinjau. */
    private function tautanDi(string $html): array
    {
        preg_match_all('/href="([^"]*preview[^"]*)"/', $html, $cocok);

        return array_values(array_unique(array_map('html_entity_decode', $cocok[1])));
    }

    public function test_pengalih_bahasa_di_arsip_tetap_terbuka(): void
    {
        $html = $this->get($this->link()->bagikan('mayawana', null, 'id'))->assertOk()->getContent();

        foreach ($this->tautanDi($html) as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_menelusuri_arsip_ke_laporan_lalu_kembali(): void
    {
        $arsip = $this->get($this->link()->bagikan('mayawana', null, 'id'))->assertOk();

        // Tautan judul laporan di daftar arsip.
        $keLaporan = collect($this->tautanDi($arsip->getContent()))
            ->first(fn ($url) => str_contains($url, 'laporan-draft'));
        $this->assertNotNull($keLaporan, 'tautan laporan tidak ada di arsip pratinjau');

        $laporan = $this->get($keLaporan)->assertOk();
        $laporan->assertSee('Judul Indonesia');

        // Dan setiap tautan di halaman laporan — termasuk "Kembali ke arsip
        // kasus" dan pengalih bahasanya.
        foreach ($this->tautanDi($laporan->getContent()) as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_ganti_ke_english_menampilkan_terjemahannya(): void
    {
        $html = $this->get($this->link()->bagikan('mayawana', 'laporan-draft', 'id'))
            ->assertOk()->assertSee('Judul Indonesia')->getContent();

        $keEn = collect($this->tautanDi($html))->first(fn ($url) => str_contains($url, '/en'));
        $this->assertNotNull($keEn, 'pengalih bahasa EN tidak ada');

        $this->get($keEn)->assertOk()->assertSee('English Title');
    }
}
