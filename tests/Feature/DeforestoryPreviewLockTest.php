<?php

namespace Tests\Feature;

use App\Http\Controllers\DeforestoryController;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Models\User;
use App\Support\DeforestoryPreviewLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Tautan pratinjau yang bisa dibagikan, dan kuncinya.
 *
 * Fitur ini membuka halaman yang belum terbit kepada orang tanpa akun CMS,
 * jadi yang dijaga di sini bukan hanya "bisa dibuka" melainkan batasnya:
 * tautan tanpa tanda tangan ditolak, tanda tangan yang kedaluwarsa ditolak,
 * laporan terkunci tetap tertutup sampai password benar dimasukkan, dan
 * tebakan password dibatasi.
 */
class DeforestoryPreviewLockTest extends TestCase
{
    use RefreshDatabase;

    private DeforestoryCase $case;

    private DeforestoryLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('deforestory-preview:127.0.0.1');

        $this->case = DeforestoryCase::create(['slug' => 'mayawana', 'status' => 'active', 'sort' => 1]);
        DeforestoryCaseTranslation::create([
            'case_id' => $this->case->id, 'locale' => 'id', 'title' => 'Mayawana', 'excerpt' => 'Kasus.',
        ]);

        $this->laporan = DeforestoryLaporan::create([
            'case_id' => $this->case->id, 'slug' => 'draft-terkunci',
            'status' => 'draft', 'is_locked' => true, 'sort' => 0,
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $this->laporan->id, 'locale' => 'id',
            'title' => 'Laporan Rahasia', 'excerpt' => 'Ringkasan.',
            'content' => '<p>ISI YANG DILINDUNGI.</p>',
        ]);
    }

    private function tautan(): string
    {
        return DeforestoryController::tautanBagikan('mayawana', 'draft-terkunci', 'id');
    }

    private function tautanUnlock(): string
    {
        return DeforestoryController::tautanUnlock('mayawana', 'draft-terkunci');
    }

    private function aturPassword(string $password = 'rahasia123'): void
    {
        app(DeforestoryPreviewLock::class)->simpanPassword($password);
    }

    public function test_tanpa_tanda_tangan_dan_tanpa_sesi_cms_ditolak(): void
    {
        $this->get(route('deforestory.laporan.preview', [
            'caseSlug' => 'mayawana', 'laporanSlug' => 'draft-terkunci', 'locale' => 'id',
        ]))->assertForbidden();
    }

    public function test_tanda_tangan_kedaluwarsa_ditolak(): void
    {
        $tautan = DeforestoryController::tautanBagikan('mayawana', 'draft-terkunci', 'id', 7);

        $this->travel(8)->days();

        $this->get($tautan)->assertForbidden();
    }

    public function test_tautan_sah_membuka_gerbang_password_bukan_isinya(): void
    {
        $this->aturPassword();

        $response = $this->get($this->tautan());

        $response->assertOk();
        $response->assertSee('Pratinjau terkunci');
        // Yang paling penting: isinya belum boleh ikut terkirim.
        $response->assertDontSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_password_benar_membuka_isinya(): void
    {
        $this->aturPassword();

        $this->from($this->tautan())
            ->post($this->tautanUnlock(), ['password' => 'rahasia123'])
            ->assertRedirect();

        $this->get($this->tautan())
            ->assertOk()
            ->assertSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_password_salah_ditolak_dan_isinya_tetap_tertutup(): void
    {
        $this->aturPassword();

        $this->post($this->tautanUnlock(), ['password' => 'salah'])->assertSessionHasErrors('password');

        $this->get($this->tautan())->assertDontSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_tebakan_beruntun_dibatasi(): void
    {
        $this->aturPassword();

        for ($i = 0; $i < 5; $i++) {
            $this->post($this->tautanUnlock(), ['password' => 'salah'.$i]);
        }

        // Percobaan keenam ditolak sebelum passwordnya sempat dicocokkan —
        // termasuk kalau kebetulan yang dimasukkan benar.
        $this->post($this->tautanUnlock(), ['password' => 'rahasia123'])->assertSessionHasErrors('password');

        $this->get($this->tautan())->assertDontSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_password_belum_diatur_tetap_menutup_bukan_membuka(): void
    {
        // Kunci tanpa password yang terpasang harus gagal tertutup, bukan
        // gagal terbuka.
        $this->get($this->tautan())
            ->assertOk()
            ->assertSee('belum diatur')
            ->assertDontSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_editor_cms_lewat_tanpa_password(): void
    {
        $this->aturPassword();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('deforestory.laporan.preview', [
                'caseSlug' => 'mayawana', 'laporanSlug' => 'draft-terkunci', 'locale' => 'id',
            ]))
            ->assertOk()
            ->assertSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_laporan_tanpa_kunci_langsung_terbuka_lewat_tautan(): void
    {
        $this->laporan->update(['is_locked' => false]);

        $this->get($this->tautan())
            ->assertOk()
            ->assertSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_ganti_password_menutup_sesi_yang_sudah_terbuka(): void
    {
        $this->aturPassword();
        $this->post($this->tautanUnlock(), ['password' => 'rahasia123']);

        $this->get($this->tautan())->assertSee('ISI YANG DILINDUNGI', escape: false);

        // Sesi menyimpan sidik dari hash, jadi hash baru = sesi lama tidak
        // lagi cocok.
        $this->aturPassword('passwordbaru');

        $this->get($this->tautan())->assertDontSee('ISI YANG DILINDUNGI', escape: false);
    }

    public function test_password_bisa_dibaca_kembali_oleh_editor(): void
    {
        $this->aturPassword('rahasia123');

        // Supaya editor bisa membagikannya lagi tanpa harus menggantinya —
        // mengganti akan memutus akses semua orang yang sudah dikirimi tautan.
        $this->assertSame('rahasia123', app(DeforestoryPreviewLock::class)->passwordTerbuka());
    }
}
