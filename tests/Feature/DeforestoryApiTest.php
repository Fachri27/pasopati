<?php

namespace Tests\Feature;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeforestoryApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-secret-token';

    protected function setUp(): void
    {
        parent::setUp();
        // Set token at runtime; phpunit.xml tidak menyetel DEFORESTORY_API_KEY.
        config(['services.deforestory_api.key' => self::TOKEN]);
    }

    /** Buat 1 case aktif + 2 laporan (id/en translation) untuk dipakai semua test. */
    private function makeCaseWithLaporans(): DeforestoryCase
    {
        $case = DeforestoryCase::create([
            'slug' => 'mayawana',
            'status' => 'active',
            'featured_image' => 'deforestory/cover.jpg',
            'category' => 'pulp',
            'year' => '2021-2025',
            'sort' => 1,
        ]);

        foreach (['id', 'en'] as $locale) {
            DeforestoryCaseTranslation::create([
                'case_id' => $case->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Mayawana' : 'Mayawana (EN)',
                'excerpt' => $locale === 'id' ? 'Ringkasan ID' : 'Excerpt EN',
                'intro' => 'Intro',
                'laporan_content' => '<p>Konten</p>',
                'chapters' => null,
            ]);
        }

        // Laporan 1 — lebih lama (published_at 2024).
        $l1 = DeforestoryLaporan::create([
            'case_id' => $case->id,
            'slug' => 'jejak-deforestasi-mayawana',
            'image' => 'deforestory/laporans/jejak.jpg',
            'sort' => 1,
            'status' => 'active',
            'published_at' => '2024-11-12',
        ]);
        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $l1->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Jejak deforestasi' : 'Deforestation trail',
                'excerpt' => $locale === 'id' ? 'Desc jejak ID' : 'Desc trail EN',
                'content' => '<p>Isi</p>',
            ]);
        }

        // Laporan 2 — lebih baru (published_at 2025) → harus jadi "latest".
        $l2 = DeforestoryLaporan::create([
            'case_id' => $case->id,
            'slug' => 'dampak-di-luar-peta',
            'image' => 'deforestory/laporans/dampak.jpg',
            'sort' => 2,
            'status' => 'active',
            'published_at' => '2025-06-03',
        ]);
        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $l2->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Dampak di luar peta' : 'Losses beyond the map',
                'excerpt' => $locale === 'id' ? 'Desc dampak ID' : 'Desc losses EN',
                'content' => '<p>Isi</p>',
            ]);
        }

        return $case->fresh(['translations', 'laporans.translations']);
    }

    // ---- Auth ---------------------------------------------------------------

    public function test_endpoints_require_token(): void
    {
        $this->makeCaseWithLaporans();

        $endpoints = [
            '/api/deforestory/cases',
            '/api/deforestory/cases/mayawana',
            '/api/deforestory/cases/mayawana/laporan',
            '/api/deforestory/cases/mayawana/laporan/latest',
            '/api/deforestory/cases/mayawana/laporan/jejak-deforestasi-mayawana',
            '/api/deforestory/queue-length',
        ];

        foreach ($endpoints as $uri) {
            $this->get($uri)->assertStatus(401, "GET $uri should be 401 without token");
        }
    }

    public function test_wrong_token_is_rejected(): void
    {
        $this->makeCaseWithLaporans();

        $this->get('/api/deforestory/cases?token=salah')->assertStatus(401);
        $this->get('/api/deforestory/cases', [
            'Authorization' => 'Bearer salah',
        ])->assertStatus(401);
    }

    public function test_token_works_via_query_string_and_header(): void
    {
        $this->makeCaseWithLaporans();

        $this->get('/api/deforestory/cases?token='.self::TOKEN)->assertStatus(200);
        $this->get('/api/deforestory/cases', [
            'Authorization' => 'Bearer '.self::TOKEN,
        ])->assertStatus(200);
    }

    public function test_no_token_configured_blocks_all(): void
    {
        $this->makeCaseWithLaporans();
        config(['services.deforestory_api.key' => null]);

        $this->get('/api/deforestory/cases?token=anything')->assertStatus(401);
    }

    // ---- GET sindikasi -----------------------------------------------------

    public function test_cases_index_lists_active_cases_only(): void
    {
        $this->makeCaseWithLaporans();
        // case draft tidak boleh muncul.
        DeforestoryCase::create([
            'slug' => 'draft-case', 'status' => 'draft', 'sort' => 2,
        ]);

        $res = $this->get('/api/deforestory/cases?token='.self::TOKEN)
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'locale']);

        $res->assertJsonPath('data.0.slug', 'mayawana');
        $res->assertJsonPath('data.0.title', 'Mayawana');
        $res->assertJsonPath('data.0.category', 'pulp');
        $res->assertJsonCount(1, 'data');
    }

    public function test_case_show_returns_case_and_laporan_list(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases/mayawana?token='.self::TOKEN)
            ->assertStatus(200);

        $res->assertJsonPath('data.slug', 'mayawana');
        $res->assertJsonPath('data.title', 'Mayawana');
        $res->assertJsonStructure(['data' => ['laporan']]);
        $res->assertJsonCount(2, 'data.laporan');
    }

    public function test_case_show_404_for_unknown_or_inactive_case(): void
    {
        $this->makeCaseWithLaporans();

        $this->get('/api/deforestory/cases/does-not-exist?token='.self::TOKEN)
            ->assertStatus(404);
    }

    public function test_laporan_index_returns_slim_shape(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases/mayawana/laporan?token='.self::TOKEN)
            ->assertStatus(200);

        $res->assertJsonCount(2, 'data');
        $first = $res->json('data.0');
        // Shape slim: slug, sort, title, date, image, desc, link — tanpa content.
        $this->assertEqualsCanonicalizing(
            ['slug', 'sort', 'title', 'date', 'image', 'desc', 'link'],
            array_keys($first)
        );
    }

    public function test_laporan_latest_picks_newest_by_published_at(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases/mayawana/laporan/latest?token='.self::TOKEN)
            ->assertStatus(200);

        // Laporan terbaru = "dampak-di-luar-peta" (published_at 2025 > 2024).
        $res->assertJsonPath('data.slug', 'dampak-di-luar-peta');
        $res->assertJsonPath('data.date', '2025-06-03');
    }

    public function test_laporan_latest_404_when_no_active_laporan(): void
    {
        $case = DeforestoryCase::create([
            'slug' => 'empty-case', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Empty',
        ]);

        $this->get('/api/deforestory/cases/empty-case/laporan/latest?token='.self::TOKEN)
            ->assertStatus(404);
    }

    public function test_laporan_show_returns_single_laporan_metadata(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases/mayawana/laporan/jejak-deforestasi-mayawana?token='.self::TOKEN)
            ->assertStatus(200);

        $res->assertJsonPath('data.slug', 'jejak-deforestasi-mayawana');
        $res->assertJsonPath('data.title', 'Jejak deforestasi');
        $res->assertJsonPath('data.date', '2024-11-12');
        $this->assertArrayNotHasKey('content', $res->json('data'));
    }

    public function test_locale_en_returns_english_titles(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases/mayawana/laporan/latest?token='.self::TOKEN.'&locale=en')
            ->assertStatus(200);

        $res->assertJsonPath('locale', 'en');
        $res->assertJsonPath('data.title', 'Losses beyond the map');
    }

    public function test_invalid_locale_falls_back_to_id(): void
    {
        $this->makeCaseWithLaporans();

        $res = $this->get('/api/deforestory/cases?token='.self::TOKEN.'&locale=fr')
            ->assertStatus(200);

        $res->assertJsonPath('locale', 'id');
    }

    // ---- queue ---------------------------------------------------------------

    public function test_queue_length_endpoint(): void
    {
        $this->get('/api/deforestory/queue-length?token='.self::TOKEN)
            ->assertStatus(200)
            ->assertJsonStructure(['queue', 'length']);
    }
}
