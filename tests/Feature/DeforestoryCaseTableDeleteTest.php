<?php

namespace Tests\Feature;

use App\Livewire\Deforestory\DeforestoryCaseTable;
use App\Models\DeforestoryCard;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeforestoryCaseTableDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeCard(string $slug = 'mayawana'): DeforestoryCard
    {
        return DeforestoryCard::create([
            'uuid' => '26cd5ee6-b0dc-4a06-b9c7-82dbf6a99c10',
            'slug' => $slug,
            'status' => 'publish',
            'title_id' => 'Mayawana',
        ]);
    }

    private function makeCaseDetail(string $slug = 'mayawana', int $laporan = 0): DeforestoryCase
    {
        $case = DeforestoryCase::create([
            'slug' => $slug, 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        for ($i = 1; $i <= $laporan; $i++) {
            $lap = DeforestoryLaporan::create([
                'case_id' => $case->id, 'slug' => "laporan-$i",
                'sort' => $i, 'status' => 'active',
            ]);
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $lap->id, 'locale' => 'id',
                'title' => "Laporan $i", 'excerpt' => 'x',
            ]);
        }

        return $case;
    }

    public function test_delete_card_removes_card_case_and_laporans(): void
    {
        $this->makeCard();
        $case = $this->makeCaseDetail('mayawana', 2);

        $this->assertSame(1, DeforestoryCard::count());
        $this->assertSame(2, DeforestoryLaporan::count());

        Livewire::test(DeforestoryCaseTable::class)
            ->call('deleteCard', 'mayawana');

        $this->assertSame(0, DeforestoryCard::count());
        $this->assertSame(0, DeforestoryCase::count());
        $this->assertSame(0, DeforestoryLaporan::count());
        $this->assertSame(0, DeforestoryCaseTranslation::count());
        $this->assertSame(0, DeforestoryLaporanTranslation::count());
    }

    public function test_delete_card_removes_card_even_without_cms_detail(): void
    {
        $this->makeCard();

        Livewire::test(DeforestoryCaseTable::class)
            ->call('deleteCard', 'mayawana');

        $this->assertSame(0, DeforestoryCard::count());
    }

    public function test_delete_card_removes_cms_detail_even_without_card(): void
    {
        $case = $this->makeCaseDetail('mayawana', 1);

        Livewire::test(DeforestoryCaseTable::class)
            ->call('deleteCard', 'mayawana');

        $this->assertSame(0, DeforestoryCase::count());
        $this->assertSame(0, DeforestoryLaporan::count());
        $this->assertNull($case->fresh());
    }

    public function test_delete_card_flashes_error_when_nothing_exists(): void
    {
        $component = Livewire::test(DeforestoryCaseTable::class)
            ->call('deleteCard', 'tidak-ada');

        $component->assertSee('Kartu "tidak-ada" tidak ditemukan.');
    }
}
