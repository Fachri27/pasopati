<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Fellowship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Event contoh; `location` sengaja memuat nama provinsi karena dari
     * situlah FireController menyimpulkan label pulau pada kartu.
     */
    private function buatEvent(string $judul, string $lokasi, string $tanggal, string $orientasi = 'landscape', ?string $video = null): Event
    {
        return Event::create([
            'image_id' => null,
            'image_en' => null,
            'video' => $video,
            'title_id' => $judul,
            'title_en' => $judul.' (EN)',
            'event_date' => $tanggal,
            'location' => $lokasi,
            'location_lat' => -6.9175000,
            'location_lng' => 107.6191000,
            'location_geojson' => null,
            'orientation' => $orientasi,
        ]);
    }

    public function test_fire_page_shows_empty_shelf_when_no_events(): void
    {
        $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->assertSee('class="pantauan-kosong"', false)
            ->assertSee('Belum ada laporan')
            ->assertDontSee('aria-roledescription="korsel"', false);
    }

    public function test_empty_shelf_hides_cms_link_from_public_visitors(): void
    {
        $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->assertDontSee('Tambah kejadian');
    }

    public function test_empty_shelf_offers_cms_link_to_editors(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->assertSee('Tambah kejadian')
            ->assertSee(route('events.create'), false);
    }

    public function test_fire_page_renders_events_from_cms(): void
    {
        $this->buatEvent('Karhutla Sukabumi Meluas', 'Sukabumi, Jawa Barat', '2026-08-11');

        $response = $this->get(route('fire', ['locale' => 'id']));

        $response->assertOk();
        // Bukan 'pantauan-kosong' polos: string itu juga ada pada <link> CSS
        // di layout, yang selalu ikut ter-render.
        $response->assertDontSee('class="pantauan-kosong"', false);
        $response->assertSee('aria-roledescription="korsel"', false);
        $response->assertSee('Karhutla Sukabumi Meluas');
        // Tombol bagikan + toast tersaji di markup pop-up rincian.
        $response->assertSee('rincian__bagikan', false);
        $response->assertSee('rincian__toast', false);
        // Halaman base /fire tetap memakai judul default — meta per-event
        // hanya untuk permalink (lihat test_fire_event_permalink_*).
        $response->assertSee('<title>Fire Pasopati — Pantauan Karhutla Indonesia</title>', false);

        // Slug dibangun otomatis dari judul dan ikut dalam payload berita
        // supaya tautan share memakai ?event=<slug>, bukan id numerik.
        $berita = $response->viewData('berita');
        $this->assertSame('karhutla-sukabumi-meluas', $berita[0]['slug']);
    }

    /**
     * Label pulau diperiksa lewat view data, bukan assertSee: @js() menulis
     * payload korsel dengan escape ", jadi mencari string '"pulau":"…"'
     * di HTML tidak akan pernah cocok.
     */
    public function test_fire_page_maps_location_to_island_for_map_popup(): void
    {
        $this->buatEvent('Karhutla Riau Meluas', 'Bengkalis, Riau', '2026-08-10');
        $this->buatEvent('Karhutla Ketapang', 'Ketapang, Kalimantan Barat', '2026-08-09');

        $berita = $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->viewData('berita');

        $this->assertSame(['Sumatra', 'Kalimantan'], array_column($berita, 'pulau'));
    }

    /**
     * Orientasi di CMS memilih varian kartu: "horizontal" → foto memenuhi
     * bingkai dengan teks putih menumpang (`vertikal` = true), "landscape" →
     * kartu kaca putih dengan foto 3:2 di bawah.
     */
    public function test_orientation_selects_the_card_variant(): void
    {
        $this->buatEvent('Kartu foto penuh', 'Bengkalis, Riau', '2026-08-11', 'horizontal');
        $this->buatEvent('Kartu kaca putih', 'Sukabumi, Jawa Barat', '2026-08-10', 'landscape');

        $berita = $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->viewData('berita');

        $this->assertTrue($berita[0]['vertikal']);
        $this->assertFalse($berita[1]['vertikal']);
    }

    /**
     * Event boleh berisi video, bukan foto. Kartu memutarnya di kotak media
     * yang sama, dengan `gambar` (thumbnail dari EventController) sebagai
     * poster — jadi kedua kunci itu harus sama-sama terkirim.
     */
    public function test_event_with_video_sends_both_video_and_poster(): void
    {
        $this->buatEvent('Rekaman udara Banjarbaru', 'Banjarbaru, Kalimantan Selatan', '2026-08-11', 'landscape', 'events/videos/uji.mp4');
        $this->buatEvent('Hanya foto', 'Sukabumi, Jawa Barat', '2026-08-10');

        $berita = $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->viewData('berita');

        $this->assertStringContainsString('events/videos/uji.mp4', $berita[0]['video']);
        $this->assertNotEmpty($berita[0]['gambar']);

        // Tanpa video, kartu tetap memakai <img> — kuncinya harus null, bukan
        // string kosong, karena Blade memilih varian lewat `!k.isi.video`.
        $this->assertNull($berita[1]['video']);
    }

    /**
     * Poster video tidak boleh jatuh ke foto cadangan bersama.
     *
     * `gambar` sengaja punya cadangan supaya kartu tanpa foto tidak kosong,
     * tetapi cadangan itu satu berkas yang sama untuk semua event. Dipakai
     * sebagai poster, setiap event yang thumbnail-nya gagal dibuat (ffmpeg
     * tidak terpasang di server) tampil dengan foto identik dan terlihat
     * seperti kartu yang tertukar. `poster` karena itu null apa adanya.
     */
    public function test_video_without_a_thumbnail_sends_no_poster(): void
    {
        $this->buatEvent('Rekaman tanpa thumbnail', 'Bengkalis, Riau', '2026-08-11', 'landscape', 'events/videos/uji.mp4');

        $berita = $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->viewData('berita');

        $this->assertNull($berita[0]['poster']);

        // `gambar` tetap terisi: dipakai <img> kartu non-video dan keping
        // bundar di pop-up, yang memang tidak boleh kosong.
        $this->assertNotEmpty($berita[0]['gambar']);
    }

    public function test_fire_page_takes_ten_most_recent_events(): void
    {
        foreach (range(1, 12) as $n) {
            $this->buatEvent("Kejadian {$n}", 'Sukabumi, Jawa Barat', '2026-08-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
        }

        $berita = $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->viewData('berita');

        $this->assertCount(10, $berita);
        $this->assertSame('Kejadian 12', $berita[0]['judul']);
        $this->assertSame('Kejadian 3', $berita[9]['judul']);
    }

    /**
     * Tautan share pop-up rincian memakai ?event=<slug>. Kalau event itu lebih
     * lama dari 10 berita terbaru, ia tidak terambil secara normal →
     * bukaRincianSlug(slug) di klien tidak menemukannya. FireController harus
     * tetap menyertakannya (di-prepend) supaya deep-link share selalu bisa
     * membuka rincian event yang dimaksud.
     */
    public function test_event_query_param_includes_old_event_not_in_latest_ten(): void
    {
        foreach (range(1, 12) as $n) {
            $this->buatEvent("Kejadian {$n}", 'Sukabumi, Jawa Barat', '2026-08-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
        }

        $lama = Event::where('title_id', 'Kejadian 1')->first();

        // Tanpa param: 10 terbaru, Kejadian 1 (paling lama) tidak termuat.
        $berita = $this->get(route('fire', ['locale' => 'id']))->assertOk()->viewData('berita');
        $this->assertCount(10, $berita);
        $this->assertNotContains('Kejadian 1', array_column($berita, 'judul'));

        // Dengan ?event=<slug lama>: Kejadian 1 di-prepend, total jadi 11.
        $berita = $this->get(route('fire', ['locale' => 'id', 'event' => $lama->slug]))
            ->assertOk()
            ->viewData('berita');

        $this->assertCount(11, $berita);
        $this->assertSame('Kejadian 1', $berita[0]['judul']);
        $this->assertSame($lama->slug, $berita[0]['slug']);
    }

    /** ?event= untuk event yang sudah termuat di 10 terbaru tidak menduplikasi. */
    public function test_event_query_param_for_already_included_event_does_not_duplicate(): void
    {
        foreach (range(1, 12) as $n) {
            $this->buatEvent("Kejadian {$n}", 'Sukabumi, Jawa Barat', '2026-08-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
        }

        $terbaru = Event::where('title_id', 'Kejadian 12')->first();

        $berita = $this->get(route('fire', ['locale' => 'id', 'event' => $terbaru->slug]))
            ->assertOk()
            ->viewData('berita');

        $this->assertCount(10, $berita);
        $this->assertSame('Kejadian 12', $berita[0]['judul']);
    }

    /** ?event= dengan slug yang tidak ada tidak mengubah daftar berita. */
    public function test_event_query_param_with_unknown_slug_leaves_list_unchanged(): void
    {
        foreach (range(1, 12) as $n) {
            $this->buatEvent("Kejadian {$n}", 'Sukabumi, Jawa Barat', '2026-08-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
        }

        $berita = $this->get(route('fire', ['locale' => 'id', 'event' => 'event-tidak-ada']))
            ->assertOk()
            ->viewData('berita');

        $this->assertCount(10, $berita);
    }

    /**
     * Permalink path /{locale}/fire/<slug> (pola Instagram). Controller show()
     * memuat event itu, menyiapkan slug untuk korsel (pop-up terbuka otomatis),
     * dan tetap menyajikan 10 berita terbaru di korsel.
     */
    public function test_fire_event_permalink_loads_event_and_passes_slug(): void
    {
        $this->buatEvent('Kejadian Lama', 'Sukabumi, Jawa Barat', '2026-08-01');
        $baru = $this->buatEvent('Kejadian Baru', 'Bengkalis, Riau', '2026-08-12');

        $response = $this->get(route('fire.event', ['locale' => 'id', 'slug' => $baru->slug]))
            ->assertOk();

        $this->assertSame($baru->slug, $response->viewData('eventSlugDiminta'));
        // urlDasar selalu base /{locale}/fire (tanpa slug) — dipakai korsel
        // menyusun permalink tiap event.
        $this->assertStringEndsWith('/id/fire', $response->viewData('urlDasar'));
        $berita = $response->viewData('berita');
        $this->assertContains($baru->title_id, array_column($berita, 'judul'));
    }

    /** Permalink event yang lebih lama dari 10 terbaru: di-prepend supaya
     *  pop-up-nya bisa dibuka, sama seperti deep-link ?event= lama. */
    public function test_fire_event_permalink_includes_old_event_not_in_latest_ten(): void
    {
        foreach (range(1, 12) as $n) {
            $this->buatEvent("Kejadian {$n}", 'Sukabumi, Jawa Barat', '2026-08-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
        }

        $lama = Event::where('title_id', 'Kejadian 1')->first();

        $berita = $this->get(route('fire.event', ['locale' => 'id', 'slug' => $lama->slug]))
            ->assertOk()
            ->viewData('berita');

        $this->assertCount(11, $berita);
        $this->assertSame('Kejadian 1', $berita[0]['judul']);
        $this->assertSame($lama->slug, $berita[0]['slug']);
    }

    /** Slug tak dikenal di permalink → 404, sama seperti post Instagram yang tak ada. */
    public function test_unknown_event_slug_returns_404(): void
    {
        $this->buatEvent('Satu Event', 'Sukabumi, Jawa Barat', '2026-08-11');

        $this->get(route('fire.event', ['locale' => 'id', 'slug' => 'event-tidak-ada']))
            ->assertNotFound();
    }

    /**
     * Permalink memuat OG/Twitter meta + <title> dari event itu, supaya preview
     * link di WhatsApp/Twitter menampilkan judul+gambar event — bagian inti dari
     * pengalaman "kaya Instagram". Halaman base /fire tidak memuat meta ini
     * (diverifikasi di test_fire_page_renders_events_from_cms).
     */
    public function test_fire_event_permalink_has_event_og_meta(): void
    {
        $e = $this->buatEvent('Karhutla Sukabumi Meluas', 'Sukabumi, Jawa Barat', '2026-08-11');

        $response = $this->get(route('fire.event', ['locale' => 'id', 'slug' => $e->slug]))->assertOk();

        $response->assertSee('<title>Karhutla Sukabumi Meluas</title>', false);
        $response->assertSee('<meta property="og:title" content="Karhutla Sukabumi Meluas"', false);
        $response->assertSee('<meta property="og:image" content="', false);
        // image_id kosong di buatEvent → jatuh ke cadangan berita-jawa.jpg.
        $response->assertSee('assets/img/berita-jawa.jpg', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);
    }

    /**
     * Navbar /fire memakai salinannya sendiri (pasopati.nav) yang ikut
     * menampilkan menu Fellowship dari $yearPosts. Variabel itu diisi view
     * composer di AppServiceProvider; ketika composer-nya hanya menyasar
     * layouts.app dan layouts.deforestory, seluruh halaman ini gagal dirender.
     *
     * Diuji lewat isi menunya, bukan sekadar assertOk(): kalau $yearPosts
     * hilang tanpa memicu galat, perulangannya cuma menghasilkan menu kosong
     * dan halaman tetap 200 — persis keadaan yang lolos dari pengujian
     * sebelumnya.
     */
    public function test_navbar_lists_fellowship_entries(): void
    {
        $fellowship = Fellowship::query()->create([
            'slug' => 'fellowship-uji',
            'status' => 'active',
            'start_date' => '2026-03-01',
            'end_date' => '2026-06-01',
        ]);

        $this->get(route('fire', ['locale' => 'id']))
            ->assertOk()
            ->assertSee(route('fellowship.preview', ['locale' => 'id', 'slug' => $fellowship->slug]), false);
    }
}
