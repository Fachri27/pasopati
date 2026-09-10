<?php

namespace Tests\Feature;

use App\Models\DeforestoryCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Editor TinyMCE laporan Deforestory beserta dua modul yang menyuplainya:
 * pustaka Reference (tombol + Image / + GLightbox / + Before/After) dan
 * Data & Grafik (tombol + Data/Grafik).
 *
 * Yang dijaga di sini adalah KONTRAK antara Blade dan JavaScript. Editor
 * dirakit resources/js/cms-tinymce.js dari atribut data-* pada pembungkusnya;
 * kalau salah satu atribut itu hilang atau route-nya berganti nama, formnya
 * tetap tampil normal tapi tombol galerinya diam-diam berhenti bekerja —
 * kegagalan yang tidak kelihatan sampai ada editor yang mencobanya.
 */
class DeforestoryTinymceEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function kasus(): DeforestoryCase
    {
        return DeforestoryCase::create([
            'slug' => 'mayawana',
            'status' => 'active',
            'sort' => 1,
        ]);
    }

    public function test_form_laporan_memasang_pembungkus_editor_lengkap_dengan_url_modulnya(): void
    {
        $kasus = $this->kasus();

        $response = $this->actingAs($this->admin())
            ->get(route('deforestory.laporan.create', ['caseSlug' => $kasus->slug]));

        $response->assertOk();

        // Pembungkus + textarea kembar: satu untuk TinyMCE (di balik
        // wire:ignore), satu tersembunyi sebagai jembatan wire:model.
        $response->assertSee('data-tinymce-wrapper', escape: false);
        $response->assertSee('data-tinymce-editor', escape: false);
        $response->assertSee('data-tinymce-input', escape: false);

        // Kedua bahasa punya editor sendiri.
        $response->assertSee('data-tinymce-picker-id="laporan_content_id"', escape: false);
        $response->assertSee('data-tinymce-picker-id="laporan_content_en"', escape: false);

        // Sumber gambar dan grafik yang dibaca JS saat tombol ditekan.
        $response->assertSee('data-tinymce-reference-page-url', escape: false);
        // e(): URL masuk sebagai nilai atribut, jadi & tertulis &amp; di HTML.
        $response->assertSee(e(route('reference.index', ['picker' => 1, 'editor' => 'laporan_content_id'])), escape: false);
        $response->assertSee(route('data-visualizations.options'), escape: false);
    }

    public function test_reference_sebagai_pemilih_hanya_menawarkan_gambar(): void
    {
        DB::table('reference_images')->insert([
            [
                'title' => 'Foto tutupan hutan',
                'alt_text' => 'Tutupan hutan 2025',
                'image_path' => 'references/hutan.jpg',
                'disk' => 'public',
                'mime_type' => 'image/jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Lampiran izin',
                'alt_text' => 'Dokumen izin',
                'image_path' => 'reference-files/izin.pdf',
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('reference.index', ['picker' => 1, 'modal' => 1, 'editor' => 'laporan_content_id']));

        $response->assertOk();
        $response->assertSee('Foto tutupan hutan');
        // PDF tidak bisa disisipkan sebagai gambar, jadi disaring dari daftar.
        $response->assertDontSee('Lampiran izin');
        // Tombol inilah yang ditangkap penyimak klik di cms-tinymce.js.
        $response->assertSee('data-reference-select', escape: false);
        $response->assertSee('data-editor-key="laporan_content_id"', escape: false);
    }

    public function test_pemilih_galeri_menyalakan_mode_pilih_banyak(): void
    {
        DB::table('reference_images')->insert([
            'title' => 'Foto tutupan hutan',
            'alt_text' => 'Tutupan hutan 2025',
            'image_path' => 'references/hutan.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('reference.index', [
            'picker' => 1,
            'modal' => 1,
            'multiple' => 1,
            'limit' => 2,
            'purpose' => 'before-after',
            'editor' => 'laporan_content_id',
        ]));

        $response->assertOk();
        $response->assertSee('data-reference-gallery-toggle', escape: false);
        $response->assertSee('data-reference-gallery-caption', escape: false);
        // Before/After menuntut tepat dua gambar; JS mengunci tombol sisip
        // sampai jumlahnya pas.
        $response->assertSee('data-reference-selection-exact="2"', escape: false);
        $response->assertSee('data-reference-selection-limit="2"', escape: false);
    }

    public function test_daftar_pilihan_grafik_hanya_memuat_yang_aktif(): void
    {
        $aktif = DB::table('data_visualizations')->insertGetId([
            'title' => 'Laju deforestasi',
            'chart_type' => 'column',
            'chart_data' => json_encode(['columns' => ['Tahun', 'Ha'], 'rows' => [['2024', '120']]]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('data_visualizations')->insert([
            'title' => 'Angka lama yang ditarik',
            'chart_type' => 'bar',
            'chart_data' => json_encode(['columns' => ['Tahun', 'Ha'], 'rows' => []]),
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->getJson(route('data-visualizations.options'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Laju deforestasi');
        $response->assertJsonPath('data.0.embed_url', route('data-visualizations.embed', $aktif));
    }

    public function test_embed_grafik_publik_dan_ikut_mati_saat_dinonaktifkan(): void
    {
        $id = DB::table('data_visualizations')->insertGetId([
            'title' => 'Laju deforestasi',
            'chart_type' => 'column',
            'chart_data' => json_encode(['columns' => ['Tahun', 'Ha'], 'rows' => [['2024', '120']]]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tanpa login: grafik tersisip harus tetap tampil bagi pembaca.
        $this->get(route('data-visualizations.embed', $id))
            ->assertOk()
            ->assertSee('published-data-visualization', escape: false);

        DB::table('data_visualizations')->where('id', $id)->update(['is_active' => false]);

        $this->get(route('data-visualizations.embed', $id))->assertNotFound();
    }

    public function test_editor_artikel_ikut_mendapat_tombol_grafik(): void
    {
        $response = $this->actingAs($this->admin())->get(route('pages.create'));

        $response->assertOk();
        // Editor artikel punya konfigurasi TinyMCE sendiri (LFM + tombol
        // slider), jadi ia hanya meminjam dialog pemilihnya lewat
        // window.bukaPemilihGrafik — bukan memakai <x-tinymce-editor>.
        $response->assertSee('addDataVisualization', escape: false);
        $response->assertSee('window.bukaPemilihGrafik', escape: false);
        $response->assertSee(route('data-visualizations.options'), escape: false);

        // Yang sudah ada sebelumnya tidak boleh hilang.
        $response->assertSee('addSlider', escape: false);
        $response->assertSee('/laravel-filemanager', escape: false);
    }

    public function test_reference_tertutup_untuk_yang_bukan_editor(): void
    {
        $this->get(route('reference.index'))->assertRedirect('/login');

        $komentator = User::factory()->create(['role' => 'commenter']);
        $this->actingAs($komentator)->get(route('reference.index'))->assertForbidden();
        $this->actingAs($komentator)->get(route('data-visualizations.index'))->assertForbidden();
    }
}
