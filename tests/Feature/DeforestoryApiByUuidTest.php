<?php

namespace Tests\Feature;

use App\Models\DeforestoryCard;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test endpoint GET by-uuid: web lain (simontini) mengenal kasus via uuid card,
 * bukan slug. uuid → card → case (slug match) → laporan.
 *
 * Endpoint PUBLIK (tanpa token) — data laporan yang sudah publish, aman
 * di-consume siapa pun.
 *
 * Response = JSON array berisi tiap laporan dalam shape SAMA dengan payload
 * sync (DeforestorySyncJob::payload): {title_id, title_en, description_id,
 * description_en, image_id, image_en, target_url_id, target_url_en,
 * published_at}. Laporan kasus
 * terus bertambah → array ikut membesar. Consumer pakai satu shape untuk
 * push (sync) & pull (GET).
 *
 *   GET /api/deforestory/by-uuid/laporan/{uuid}
 */
class DeforestoryApiByUuidTest extends TestCase
{
    use RefreshDatabase;

    private const UUID = '6518428c-62fc-4788-97cc-f6bac6385615';

    private const CASE_SLUG = 'mayawana';

    private const LAPORAN_SLUG = 'jejak-deforestasi-mayawana';

    /** Case aktif + card (slug match) + laporan aktif dgn translations id/en. */
    private function makeCaseWithCardAndLaporan(int $laporanCount = 1): array
    {
        $card = DeforestoryCard::create([
            'uuid' => self::UUID,
            'slug' => self::CASE_SLUG,
            'status' => 'publish',
            'sort' => 1,
        ]);

        $case = DeforestoryCase::create([
            'slug' => self::CASE_SLUG,
            'status' => 'active',
            'featured_image' => 'https://cdn.test/featured.jpg',
            'category' => 'pulp',
            'year' => '2021-2025',
            'sort' => 1,
        ]);

        foreach (['id', 'en'] as $locale) {
            DeforestoryCaseTranslation::create([
                'case_id' => $case->id,
                'locale' => $locale,
                'title' => $locale === 'id' ? 'Mayawana' : 'Mayawana (EN)',
                'excerpt' => $locale === 'id' ? 'Ringkasan kasus' : 'Case excerpt',
                'intro' => 'intro',
                'laporan_content' => '<p>Konten</p>',
                'chapters' => null,
            ]);
        }

        $laporans = [];
        for ($i = 1; $i <= $laporanCount; $i++) {
            $laporan = DeforestoryLaporan::create([
                'case_id' => $case->id,
                'slug' => self::LAPORAN_SLUG.($i > 1 ? "-{$i}" : ''),
                'image' => null,
                'sort' => $i,
                'status' => 'active',
                'published_at' => '2024-11-12',
            ]);

            foreach (['id', 'en'] as $locale) {
                DeforestoryLaporanTranslation::create([
                    'laporan_id' => $laporan->id,
                    'locale' => $locale,
                    'title' => $locale === 'id' ? 'Jejak Deforestasi' : 'Deforestation Trail',
                    'excerpt' => $locale === 'id' ? 'Deskripsi laporan' : 'Report desc',
                    'content' => $locale === 'id' ? '<p>Isi ID</p>' : '<p>Body EN</p>',
                    'image' => "https://cdn.test/cover-{$locale}.jpg",
                ]);
            }
            $laporans[] = $laporan;
        }

        return [$card, $case, $laporans];
    }

    private function listUrl(string $uuid = self::UUID): string
    {
        return "/api/deforestory/by-uuid/laporan/{$uuid}";
    }

    /** Assert sebuah object punya 9 field sync-payload persis. */
    private function assertSyncShape(array $row, string $laporanSlug = self::LAPORAN_SLUG): void
    {
        $this->assertSame(
            ['title_id', 'title_en', 'description_id', 'description_en', 'image_id', 'image_en', 'target_url_id', 'target_url_en', 'published_at'],
            array_keys($row),
            'shape harus 9 field sync-payload, gak ada field lain'
        );

        $this->assertSame('Jejak Deforestasi', $row['title_id']);
        $this->assertSame('Deforestation Trail', $row['title_en']);
        $this->assertSame('Deskripsi laporan', $row['description_id']);
        $this->assertSame('Report desc', $row['description_en']);
        // image per-locale dari translation (di makeCase... image = https://cdn.test/cover-{locale}.jpg).
        $this->assertSame('https://cdn.test/cover-id.jpg', $row['image_id']);
        $this->assertSame('https://cdn.test/cover-en.jpg', $row['image_en']);
        $this->assertSame('2024-11-12', $row['published_at']);
        $this->assertStringContainsString('/id/deforestory/'.self::CASE_SLUG."/{$laporanSlug}", $row['target_url_id']);
        $this->assertStringContainsString('/en/deforestory/'.self::CASE_SLUG."/{$laporanSlug}", $row['target_url_en']);
    }

    public function test_list_returns_array_of_sync_shape_objects(): void
    {
        $this->makeCaseWithCardAndLaporan();

        $res = $this->get($this->listUrl())->assertStatus(200);

        $rows = $res->json();
        $this->assertIsArray($rows, 'list = root JSON array, bukan {data: [...]}');
        $this->assertCount(1, $rows);
        $this->assertSyncShape($rows[0]);
    }

    public function test_list_grows_as_laporans_added(): void
    {
        // Bukti endpoint ikut bertambah saat laporan kasus bertambah.
        $this->makeCaseWithCardAndLaporan(3);

        $res = $this->get($this->listUrl())->assertStatus(200);

        $rows = $res->json();
        $this->assertCount(3, $rows);
        $this->assertSyncShape($rows[0], self::LAPORAN_SLUG);
        $this->assertSyncShape($rows[1], self::LAPORAN_SLUG.'-2');
        $this->assertSyncShape($rows[2], self::LAPORAN_SLUG.'-3');
    }

    public function test_list_orders_by_sort(): void
    {
        $this->makeCaseWithCardAndLaporan(3);

        $rows = $this->get($this->listUrl())->assertStatus(200)->json();

        // Laporan di-orderBy sort (1,2,3). Slug i=1 = base, i>1 = base-{i}.
        $slugs = array_map(fn ($r) => basename(parse_url($r['target_url_id'], PHP_URL_PATH)), $rows);

        $this->assertSame(
            [self::LAPORAN_SLUG, self::LAPORAN_SLUG.'-2', self::LAPORAN_SLUG.'-3'],
            $slugs,
            'laporan harus urut sort ascending'
        );
    }

    public function test_list_404_for_unknown_uuid(): void
    {
        $this->get($this->listUrl('tidak-ada-uuid'))->assertStatus(404);
    }

    public function test_list_card_without_case_returns_empty_array(): void
    {
        DeforestoryCard::create([
            'uuid' => 'baru-tanpa-case',
            'slug' => 'baru-saja',
            'status' => 'publish',
            'sort' => 1,
        ]);

        $res = $this->get($this->listUrl('baru-tanpa-case'))->assertStatus(200);

        $this->assertSame([], $res->json());
    }

    public function test_list_excludes_inactive_laporans(): void
    {
        [$card, $case, $laporans] = $this->makeCaseWithCardAndLaporan();
        $laporans[0]->update(['status' => 'draft']);

        $res = $this->get($this->listUrl())->assertStatus(200);

        $this->assertSame([], $res->json());
    }

    public function test_list_published_at_falls_back_to_created_at(): void
    {
        [$card, $case, $laporans] = $this->makeCaseWithCardAndLaporan();
        $laporans[0]->update(['published_at' => null]);

        $res = $this->get($this->listUrl())->assertStatus(200);

        $this->assertSame($laporans[0]->created_at->toDateString(), $res->json('0.published_at'));
    }

    // ---- publik (tanpa token) ---------------------------------------------

    public function test_by_uuid_endpoint_is_public_without_token(): void
    {
        // Endpoint sindikasi publik — gak butuh Bearer token.
        $this->makeCaseWithCardAndLaporan();

        $this->get('/api/deforestory/by-uuid/laporan/'.self::UUID)->assertStatus(200);
    }
}
