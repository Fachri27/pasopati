<?php

namespace Tests\Feature;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test endpoint GET /api/deforestory/cases/{slug}/laporan/{laporanSlug}/translations
 * + helper laporanImage(): image laporan sekarang per-locale (translation.image),
 * dengan fallback ke legacy laporan.image lalu case.featured_image.
 */
class DeforestoryApiTranslationTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-secret-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.deforestory_api.key' => self::TOKEN]);
    }

    /** Case aktif + 1 laporan aktif, translations id/en. */
    private function makeCaseWithLaporan(array $laporanAttrs = [], array $caseAttrs = []): array
    {
        $case = DeforestoryCase::create(array_merge([
            'slug' => 'mayawana',
            'status' => 'active',
            'featured_image' => 'https://cdn.test/featured.jpg',
            'category' => 'pulp',
            'year' => '2021-2025',
            'sort' => 1,
        ], $caseAttrs));

        foreach (['id', 'en'] as $locale) {
            DeforestoryCaseTranslation::create([
                'case_id' => $case->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Mayawana' : 'Mayawana (EN)',
                'excerpt' => 'x',
                'intro' => 'intro',
                'laporan_content' => '<p>Konten</p>',
                'chapters' => null,
            ]);
        }

        $laporan = DeforestoryLaporan::create(array_merge([
            'case_id' => $case->id,
            'slug' => 'jejak-deforestasi-mayawana',
            'image' => null,            // legacy; null default supaya test eksplisit
            'sort' => 1,
            'status' => 'active',
            'published_at' => '2024-11-12',
        ], $laporanAttrs));

        return [$case, $laporan];
    }

    private function translationsUrl(string $caseSlug = 'mayawana', string $laporanSlug = 'jejak-deforestasi-mayawana'): string
    {
        return "/api/deforestory/cases/{$caseSlug}/laporan/{$laporanSlug}/translations?token=" . self::TOKEN;
    }

    // ---- per-locale image --------------------------------------------------

    public function test_translations_endpoint_returns_distinct_per_locale_image(): void
    {
        [$case, $laporan] = $this->makeCaseWithLaporan();

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporan->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Jejak' : 'Trail',
                'excerpt' => 'desc',
                'content' => '<p>Isi</p>',
                'image' => "https://cdn.test/cover-{$locale}.jpg",
            ]);
        }

        $res = $this->get($this->translationsUrl())->assertStatus(200);

        $idImg = $res->json('data.translations.id.image');
        $enImg = $res->json('data.translations.en.image');

        $this->assertSame('https://cdn.test/cover-id.jpg', $idImg);
        $this->assertSame('https://cdn.test/cover-en.jpg', $enImg);
        $this->assertNotEquals($idImg, $enImg, 'image id & en harus beda (per-locale)');

        // Top-level image = image id (backward compat).
        $this->assertSame('https://cdn.test/cover-id.jpg', $res->json('data.image'));
    }

    public function test_image_falls_back_to_legacy_laporan_image(): void
    {
        // translation image null untuk kedua locale → fallback ke laporan.image.
        [$case, $laporan] = $this->makeCaseWithLaporan(['image' => 'https://cdn.test/legacy.jpg']);

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporan->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Jejak' : 'Trail',
                'excerpt' => 'desc',
                'content' => '<p>Isi</p>',
                'image' => null,
            ]);
        }

        $res = $this->get($this->translationsUrl())->assertStatus(200);

        $this->assertSame('https://cdn.test/legacy.jpg', $res->json('data.translations.id.image'));
        $this->assertSame('https://cdn.test/legacy.jpg', $res->json('data.translations.en.image'));
    }

    public function test_image_falls_back_to_case_featured_image(): void
    {
        // translation image null + laporan.image null → fallback ke case.featured_image.
        [$case, $laporan] = $this->makeCaseWithLaporan(['image' => null], [
            'featured_image' => 'https://cdn.test/case-featured.jpg',
        ]);

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporan->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Jejak' : 'Trail',
                'excerpt' => 'desc',
                'content' => '<p>Isi</p>',
                'image' => null,
            ]);
        }

        $res = $this->get($this->translationsUrl())->assertStatus(200);

        $this->assertSame('https://cdn.test/case-featured.jpg', $res->json('data.translations.id.image'));
        $this->assertSame('https://cdn.test/case-featured.jpg', $res->json('data.translations.en.image'));
    }

    public function test_translations_endpoint_404_for_unknown_laporan(): void
    {
        $this->makeCaseWithLaporan();

        $this->get($this->translationsUrl('mayawana', 'tidak-ada'))->assertStatus(404);
    }

    public function test_translations_endpoint_404_for_inactive_case(): void
    {
        [$case, $laporan] = $this->makeCaseWithLaporan();
        $case->update(['status' => 'inactive']);

        $this->get($this->translationsUrl())->assertStatus(404);
    }

    // ---- laporanSummary pakai image per-locale -----------------------------

    public function test_laporan_summary_uses_per_locale_image(): void
    {
        [$case, $laporan] = $this->makeCaseWithLaporan();

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporan->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Jejak' : 'Trail',
                'excerpt' => 'desc',
                'content' => '<p>Isi</p>',
                'image' => "https://cdn.test/sum-{$locale}.jpg",
            ]);
        }

        $idRes = $this->get('/api/deforestory/cases/mayawana/laporan?token=' . self::TOKEN . '&locale=id')
            ->assertStatus(200);
        $enRes = $this->get('/api/deforestory/cases/mayawana/laporan?token=' . self::TOKEN . '&locale=en')
            ->assertStatus(200);

        $this->assertSame('https://cdn.test/sum-id.jpg', $idRes->json('data.0.image'));
        $this->assertSame('https://cdn.test/sum-en.jpg', $enRes->json('data.0.image'));
    }
}