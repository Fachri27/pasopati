<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FireLaporanKomentarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Turnstile dimatikan secara bawaan supaya tiap tes menguji satu hal
        // saja; tes captcha di bawah menyalakannya sendiri.
        config(['services.turnstile.secret_key' => '']);
    }

    private function event(): Event
    {
        return Event::create([
            'image_id' => null,
            'image_en' => null,
            'video' => null,
            'title_id' => 'Karhutla Tapin',
            'title_en' => 'Tapin wildfire',
            'event_date' => '2026-08-11',
            'location' => 'Tapin, Kalimantan Selatan',
            'location_lat' => -3.1564470,
            'location_lng' => 115.1043160,
            'location_geojson' => null,
            'orientation' => 'landscape',
        ]);
    }

    /**
     * Berkomentar wajib punya akun (masuk lewat Google). Ditolak di endpoint,
     * bukan cuma disembunyikan di markup.
     */
    public function test_guest_cannot_post_a_comment(): void
    {
        $event = $this->event();

        $this->postJson(route('fire.komentar.store', $event), [
            'isi' => 'Komentar tanpa akun.',
        ])->assertUnauthorized();

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_signed_in_user_can_post_and_read_a_comment(): void
    {
        $event = $this->event();
        $pengguna = User::factory()->create(['name' => 'Warga Tapin', 'role' => 'commenter']);

        $this->actingAs($pengguna)
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => 'Asapnya sampai ke desa sebelah.',
            ])->assertCreated()
            ->assertJsonPath('komentar.0.nama', 'Warga Tapin')
            ->assertJsonPath('komentar.0.isi', 'Asapnya sampai ke desa sebelah.');

        $this->getJson(route('fire.komentar.index', $event))
            ->assertOk()
            ->assertJsonCount(1, 'komentar');
    }

    /**
     * Nama diambil dari akun, bukan dari isian — kiriman yang mencoba
     * menentukan namanya sendiri tetap tercatat atas nama pemiliknya.
     */
    public function test_name_comes_from_the_account_not_the_request(): void
    {
        $event = $this->event();
        $pengguna = User::factory()->create(['name' => 'Nama Asli', 'role' => 'commenter']);

        $this->actingAs($pengguna)
            ->postJson(route('fire.komentar.store', $event), [
                'nama' => 'Nama Palsu',
                'isi' => 'Halo.',
            ])->assertCreated()
            ->assertJsonPath('komentar.0.nama', 'Nama Asli');
    }

    public function test_comment_is_attached_to_that_event_only(): void
    {
        $satu = $this->event();
        $dua = Event::create(array_merge($satu->only([
            'image_id', 'image_en', 'video', 'event_date', 'location',
            'location_lat', 'location_lng', 'location_geojson', 'orientation',
        ]), ['title_id' => 'Kejadian lain', 'title_en' => 'Another event']));

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $satu), [
                'isi' => 'Komentar untuk laporan pertama.',
            ])->assertCreated();

        $this->getJson(route('fire.komentar.index', $dua))
            ->assertOk()
            ->assertJsonCount(0, 'komentar');
    }

    public function test_body_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $this->event()), ['isi' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isi']);
    }

    /**
     * Umpan jebakan: ditolak diam-diam (bukan galat) supaya bot tidak
     * mendapat petunjuk bahwa isian itu yang membongkarnya.
     */
    public function test_honeypot_silently_drops_the_comment(): void
    {
        $event = $this->event();

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => 'spam',
                'website' => 'http://spam.example',
            ])->assertCreated()->assertJsonCount(0, 'komentar');

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_moderated_out_comments_are_hidden(): void
    {
        $event = $this->event();

        Comment::query()->create([
            'commentable_type' => Event::class,
            'commentable_id' => $event->id,
            'name' => 'Disembunyikan',
            'body' => 'Menunggu moderasi.',
            'is_approved' => false,
        ]);

        $this->getJson(route('fire.komentar.index', $event))
            ->assertOk()
            ->assertJsonCount(0, 'komentar');
    }

    public function test_reply_is_nested_under_its_root_comment(): void
    {
        $event = $this->event();
        $andi = User::factory()->create(['name' => 'Andi']);
        $budi = User::factory()->create(['name' => 'Budi']);

        $this->actingAs($andi)
            ->postJson(route('fire.komentar.store', $event), ['isi' => 'Komentar akar.'])
            ->assertCreated();

        $akarId = Comment::query()->firstOrFail()->id;

        $this->actingAs($budi)
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => 'Balasan untuk Andi.',
                'balas_ke' => $akarId,
            ])->assertCreated()
            // Balasan tidak ikut jadi baris akar.
            ->assertJsonCount(1, 'komentar')
            ->assertJsonPath('komentar.0.balasan.0.nama', 'Budi')
            ->assertJsonPath('komentar.0.balasan.0.sebutan', 'Andi');
    }

    /**
     * Balasan atas balasan tetap dikumpulkan di bawah akar yang sama, bukan
     * bersarang makin dalam — rel pop-up sempit, @sebutan yang menerangkan
     * siapa membalas siapa.
     */
    public function test_reply_to_a_reply_stays_in_the_same_thread(): void
    {
        $event = $this->event();
        $andi = User::factory()->create(['name' => 'Andi']);
        $budi = User::factory()->create(['name' => 'Budi']);

        $this->actingAs($andi)->postJson(route('fire.komentar.store', $event), ['isi' => 'Akar.']);
        $akarId = Comment::query()->firstOrFail()->id;

        $this->actingAs($budi)->postJson(route('fire.komentar.store', $event), [
            'isi' => 'Balasan pertama.', 'balas_ke' => $akarId,
        ]);
        $balasanId = Comment::query()->where('parent_id', $akarId)->firstOrFail()->id;

        $this->actingAs($andi)
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => 'Balasan atas balasan.', 'balas_ke' => $balasanId,
            ])->assertCreated()
            ->assertJsonCount(1, 'komentar')
            ->assertJsonCount(2, 'komentar.0.balasan')
            ->assertJsonPath('komentar.0.balasan.1.sebutan', 'Budi');
    }

    /**
     * Membalas komentar sendiri tetap membawa sebutan. Balasan ditampilkan
     * datar dalam satu utas, jadi sebutan inilah satu-satunya penanda komentar
     * mana yang sedang dijawab — menghilangkannya membuat balasan pada utas
     * beranggota satu orang tampak tanpa tujuan.
     */
    public function test_replying_to_yourself_still_carries_a_mention(): void
    {
        $event = $this->event();
        $andi = User::factory()->create(['name' => 'Andi']);

        $this->actingAs($andi)->postJson(route('fire.komentar.store', $event), ['isi' => 'Akar.']);
        $akarId = Comment::query()->firstOrFail()->id;

        $this->actingAs($andi)
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => 'Menambahkan.', 'balas_ke' => $akarId,
            ])->assertCreated()
            ->assertJsonPath('komentar.0.balasan.0.sebutan', 'Andi');
    }

    /**
     * Induk dari laporan lain diabaikan: tanpa penjagaan ini sebuah balasan
     * bisa ditempelkan ke utas milik laporan yang tidak sedang dibuka.
     */
    public function test_parent_from_another_report_is_ignored(): void
    {
        $satu = $this->event();
        $dua = Event::create(array_merge($satu->only([
            'image_id', 'image_en', 'video', 'event_date', 'location',
            'location_lat', 'location_lng', 'location_geojson', 'orientation',
        ]), ['title_id' => 'Kejadian lain', 'title_en' => 'Another event']));

        $pengguna = User::factory()->create();

        $this->actingAs($pengguna)->postJson(route('fire.komentar.store', $satu), ['isi' => 'Akar di laporan satu.']);
        $akarLain = Comment::query()->firstOrFail()->id;

        $this->actingAs($pengguna)
            ->postJson(route('fire.komentar.store', $dua), [
                'isi' => 'Coba nempel ke utas laporan lain.',
                'balas_ke' => $akarLain,
            ])->assertCreated();

        // Jadi komentar akar biasa di laporannya sendiri, bukan balasan.
        $this->assertNull(Comment::query()->where('commentable_id', $dua->id)->firstOrFail()->parent_id);
        $this->getJson(route('fire.komentar.index', $satu))->assertJsonCount(0, 'komentar.0.balasan');
    }

    /**
     * Sebutan ikut tersimpan di dalam teks komentarnya (diisikan otomatis saat
     * menekan Balas), sementara `sebutan` dikirim terpisah sebagai penanda
     * nama yang dibalas. Kolom pop-up memakai keduanya: teks dicocokkan dengan
     * nama itu untuk memisahkan sebutan dari kalimatnya — nama akun Google
     * boleh berspasi, jadi memindai token @… dari teks bebas tidak bisa
     * diandalkan.
     */
    public function test_mention_is_kept_inside_the_body_and_flagged_separately(): void
    {
        $event = $this->event();
        $andi = User::factory()->create(['name' => 'Muhamad Fachri']);
        $budi = User::factory()->create(['name' => 'Budi']);

        $this->actingAs($andi)->postJson(route('fire.komentar.store', $event), ['isi' => 'Akar.']);
        $akarId = Comment::query()->firstOrFail()->id;

        $this->actingAs($budi)
            ->postJson(route('fire.komentar.store', $event), [
                'isi' => '@Muhamad Fachri terima kasih infonya.',
                'balas_ke' => $akarId,
            ])->assertCreated()
            ->assertJsonPath('komentar.0.balasan.0.sebutan', 'Muhamad Fachri')
            ->assertJsonPath('komentar.0.balasan.0.isi', '@Muhamad Fachri terima kasih infonya.');
    }

    public function test_comment_is_rejected_without_a_captcha_token(): void
    {
        config(['services.turnstile.secret_key' => 'rahasia-uji']);
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $this->event()), ['isi' => 'Halo.'])
            ->assertStatus(422);

        $this->assertSame(0, Comment::query()->count());
        Http::assertNothingSent();
    }

    public function test_comment_is_rejected_when_cloudflare_says_the_token_is_bad(): void
    {
        config(['services.turnstile.secret_key' => 'rahasia-uji']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $this->event()), [
                'isi' => 'Halo.',
                'captcha_token' => 'token-palsu',
            ])->assertStatus(422);

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_comment_goes_through_with_a_valid_captcha_token(): void
    {
        config(['services.turnstile.secret_key' => 'rahasia-uji']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $this->event()), [
                'isi' => 'Halo.',
                'captcha_token' => 'token-sah',
            ])->assertCreated()
            ->assertJsonCount(1, 'komentar');
    }

    /**
     * Tanpa secret, Turnstile dianggap belum dipasang: widget-nya juga tidak
     * dirender, jadi memaksa verifikasi hanya akan membuat komentar mustahil
     * dikirim di environment seperti itu.
     */
    public function test_captcha_is_skipped_when_turnstile_is_not_configured(): void
    {
        config(['services.turnstile.secret_key' => '']);
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->postJson(route('fire.komentar.store', $this->event()), ['isi' => 'Halo.'])
            ->assertCreated();

        Http::assertNothingSent();
    }
}
