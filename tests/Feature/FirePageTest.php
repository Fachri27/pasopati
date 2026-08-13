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
