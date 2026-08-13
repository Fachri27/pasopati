<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Services\DeforestoryApiService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar laporan untuk sebuah kasus Deforestory (admin).
 *
 * Saat halaman ini dibuka untuk sebuah caseSlug, kasusnya di-auto-create
 * dari kartu API (firstOrCreate) supaya editor langsung bisa menambah
 * laporan tanpa harus "membuat kasus" dulu. Identitas kasus (judul,
 * excerpt, kategori, tahun, gambar) tetap dari kartu API; di sini editor
 * hanya mengelola daftar laporan (tiap laporan = entitas sendiri dengan
 * slug, judul, gambar, excerpt, isi per-locale).
 */
class DeforestoryLaporanTable extends Component
{
    use WithPagination;

    public $caseSlug;

    public $case;

    public $search = '';

    public function mount($caseSlug = null)
    {
        $this->caseSlug = $caseSlug;
        $this->case = $this->resolveCase($caseSlug);
    }

    /**
     * Ambil/buat kasus CMS untuk slug ini. Identitas (judul, excerpt,
     * kategori, tahun, gambar) diisi dari kartu API bila kasus baru.
     */
    protected function resolveCase(string $slug): DeforestoryCase
    {
        $case = DeforestoryCase::where('slug', $slug)->first();

        if ($case) {
            return $case;
        }

        // Cari kartu di API (id + en) untuk mengisi translation.
        $api = app(DeforestoryApiService::class);
        $cardId = null;
        $cardEn = null;
        foreach ($api->getCases('id') as $card) {
            if (($card['slug'] ?? null) === $slug) {
                $cardId = $card;
                break;
            }
        }
        foreach ($api->getCases('en') as $card) {
            if (($card['slug'] ?? null) === $slug) {
                $cardEn = $card;
                break;
            }
        }

        $case = DeforestoryCase::create([
            'slug' => $slug,
            'status' => 'active',
            'featured_image' => $cardId['image'] ?? null,
            'category' => $cardId['category'] ?? null,
            'year' => $cardId['year'] ?? null,
            'sort' => 0,
            'user_id' => auth()->id(),
        ]);

        if ($cardId) {
            DeforestoryCaseTranslation::create([
                'case_id' => $case->id,
                'locale' => 'id',
                'title' => $cardId['title'] ?? ucfirst($slug),
                'excerpt' => $cardId['excerpt'] ?? null,
                'intro' => null,
                'laporan_content' => null,
                'chapters' => null,
            ]);
        }
        if ($cardEn) {
            DeforestoryCaseTranslation::create([
                'case_id' => $case->id,
                'locale' => 'en',
                'title' => $cardEn['title'] ?? ucfirst($slug),
                'excerpt' => $cardEn['excerpt'] ?? null,
                'intro' => null,
                'laporan_content' => null,
                'chapters' => null,
            ]);
        }

        return $case->fresh();
    }

    public function delete($laporanId)
    {
        $laporan = DeforestoryLaporan::find($laporanId);
        if ($laporan) {
            // Hapus gambar laporan dari disk.
            if ($laporan->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($laporan->image);
            }
            $laporan->delete();
            session()->flash('success', 'Laporan dihapus.');
        }
    }

    public function render()
    {
        $query = DeforestoryLaporan::where('case_id', $this->case->id)
            ->with(['translations'])
            ->orderBy('sort');

        if ($this->search) {
            $term = strtolower($this->search);
            $laporanIds = DeforestoryLaporan::where('case_id', $this->case->id)
                ->whereHas('translations', function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                })->pluck('id');
            $query->whereIn('id', $laporanIds);
        }

        $laporans = $query->paginate(10);

        return view('livewire.deforestory.deforestory-laporan-table', [
            'laporans' => $laporans,
            'case' => $this->case,
        ])->layout('layouts.admin');
    }
}
