<?php

namespace Tests\Feature;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pratinjau arsip kasus dan laporan Deforestory.
 *
 * Gunanya justru memperlihatkan yang BELUM terbit, jadi dua hal yang dijaga
 * di sini saling menyeimbangkan: pratinjau wajib menampilkan draft, dan
 * pratinjau wajib tertutup untuk publik. Kalau salah satunya lepas, fiturnya
 * berubah jadi kebocoran konten yang belum siap terbit.
 */
class DeforestoryPreviewTest extends TestCase
{
    use RefreshDatabase;

    private DeforestoryCase $case;

    private DeforestoryLaporan $draft;

    private DeforestoryLaporan $terbit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->case = DeforestoryCase::create(['slug' => 'mayawana', 'status' => 'active', 'sort' => 1]);

        // Halaman publik menyusun meta SEO dari terjemahan kasus; tanpa ini
        // ia gagal sebelum sempat merender apa pun.
        DeforestoryCaseTranslation::create([
            'case_id' => $this->case->id, 'locale' => 'id',
            'title' => 'Mayawana', 'excerpt' => 'Kasus contoh.',
        ]);

        $this->terbit = $this->buatLaporan('sudah-terbit', 'active', 'Laporan Sudah Terbit');
        $this->draft = $this->buatLaporan('masih-draft', 'draft', 'Laporan Masih Draft');
    }

    private function buatLaporan(string $slug, string $status, string $judul): DeforestoryLaporan
    {
        $laporan = DeforestoryLaporan::create([
            'case_id' => $this->case->id, 'slug' => $slug, 'status' => $status, 'sort' => 0,
        ]);

        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'id',
            'title' => $judul, 'excerpt' => 'Ringkasan.', 'content' => '<p>Isi laporan.</p>',
        ]);

        return $laporan;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_pratinjau_arsip_menampilkan_laporan_draft(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('deforestory.case.preview', ['caseSlug' => 'mayawana', 'locale' => 'id']));

        $response->assertOk();
        $response->assertSee('Laporan Sudah Terbit');
        $response->assertSee('Laporan Masih Draft');
        // Ditandai, supaya draft tidak tertukar dengan yang sudah terbit.
        $response->assertSee('belum tampil publik');
        $response->assertSee('Pratinjau arsip');
    }

    public function test_arsip_publik_tetap_menyembunyikan_draft(): void
    {
        $response = $this->get(route('deforestory.case', ['locale' => 'id', 'slug' => 'mayawana']));

        $response->assertOk();
        $response->assertSee('Laporan Sudah Terbit');
        $response->assertDontSee('Laporan Masih Draft');
        $response->assertDontSee('Pratinjau arsip');
    }

    public function test_tautan_di_pratinjau_mengarah_ke_pratinjau_laporan(): void
    {
        // Kalau tautannya ke route publik, laporan draft akan memantul balik
        // ke arsip — pratinjau yang isinya tidak bisa dibuka.
        $this->actingAs($this->admin())
            ->get(route('deforestory.case.preview', ['caseSlug' => 'mayawana', 'locale' => 'id']))
            ->assertOk()
            ->assertSee(e(route('deforestory.laporan.preview', [
                'caseSlug' => 'mayawana', 'laporanSlug' => 'masih-draft', 'locale' => 'id',
            ])), escape: false);
    }

    public function test_pratinjau_laporan_draft_bisa_dibuka_editor(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('deforestory.laporan.preview', [
                'caseSlug' => 'mayawana', 'laporanSlug' => 'masih-draft', 'locale' => 'id',
            ]));

        $response->assertOk();
        $response->assertSee('Laporan Masih Draft');
        $response->assertSee('Isi laporan.', escape: false);
        $response->assertSee('Pratinjau laporan');
    }

    public function test_laporan_draft_tetap_memantul_di_jalur_publik(): void
    {
        $this->get(route('deforestory.case.laporan', [
            'locale' => 'id', 'slug' => 'mayawana', 'laporanSlug' => 'masih-draft',
        ]))->assertRedirect(route('deforestory.case', ['locale' => 'id', 'slug' => 'mayawana']));
    }

    public function test_daftar_laporan_di_cms_menautkan_pratinjau_bukan_halaman_publik(): void
    {
        // Pratinjau menampilkan laporan yang sama ditambah yang masih draft,
        // jadi tautan publik di sebelahnya tidak menambah apa pun.
        $response = $this->actingAs($this->admin())
            ->get(route('deforestory.laporan.index', ['caseSlug' => 'mayawana']));

        $response->assertOk();
        $response->assertSee(e(route('deforestory.laporan.preview', [
            'caseSlug' => 'mayawana', 'laporanSlug' => 'masih-draft', 'locale' => 'id',
        ])), escape: false);
        $response->assertDontSee(e(route('deforestory.case.laporan', [
            'locale' => 'id', 'slug' => 'mayawana', 'laporanSlug' => 'masih-draft',
        ])), escape: false);
    }

    public function test_pratinjau_tertutup_untuk_publik_dan_bukan_editor(): void
    {
        $rute = [
            route('deforestory.case.preview', ['caseSlug' => 'mayawana', 'locale' => 'id']),
            route('deforestory.laporan.preview', ['caseSlug' => 'mayawana', 'laporanSlug' => 'masih-draft', 'locale' => 'id']),
        ];

        // Ditolak, bukan diarahkan ke login: sejak pratinjau bisa dibuka lewat
        // tautan bertanda tangan, URL tanpa tanda tangan yang sah harus
        // terbaca sebagai tautan tidak berlaku — bukan sebagai halaman yang
        // menuntut akun.
        foreach ($rute as $url) {
            $this->get($url)->assertForbidden();
        }

        $komentator = User::factory()->create(['role' => 'commenter']);
        foreach ($rute as $url) {
            $this->actingAs($komentator)->get($url)->assertForbidden();
        }
    }

    public function test_pratinjau_juga_melayani_kasus_yang_belum_aktif(): void
    {
        $this->case->update(['status' => 'draft']);

        $this->actingAs($this->admin())
            ->get(route('deforestory.case.preview', ['caseSlug' => 'mayawana', 'locale' => 'id']))
            ->assertOk()
            ->assertSee('Laporan Sudah Terbit');
    }
}
