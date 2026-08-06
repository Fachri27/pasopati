<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestoryCase;
use App\Services\DeforestoryApiService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar kasus Deforestory di admin.
 *
 * Kartu (identitas kasus) datang dari tabel lokal deforestory_cards — didorong
 * web lain via inbound webhook (POST /api/deforestory/cards), sama dengan
 * halaman publik /deforestory. Di sini editor tidak "membuat kartu", melainkan
 * mengisi konten halaman DETAIL (arsip + laporan) per slug. Tiap baris
 * menunjukkan apakah detail sudah diisi di CMS.
 */
class DeforestoryCaseTable extends Component
{
    use WithPagination;

    public $search = '';
    public $locale = 'id';

    public function refreshList()
    {
        // Model push: card diperbarui via webhook dari web lain, bukan di-pull.
        // refresh() sekarang no-op; panggil sekadar re-render list.
        app(DeforestoryApiService::class)->refresh($this->locale);
    }

    public function render()
    {
        $apiCases = app(DeforestoryApiService::class)->getCases($this->locale);

        // Filter cari (berdasarkan judul kartu API).
        if ($this->search) {
            $term = strtolower($this->search);
            $apiCases = array_filter($apiCases, function ($c) use ($term) {
                return str_contains(strtolower($c['title'] ?? ''), $term)
                    || str_contains(strtolower($c['slug'] ?? ''), $term);
            });
        }

        // Map slug => CMS detail (untuk tahu sudah diisi / belum + status).
        $slugs = array_column($apiCases, 'slug');
        $cmsMap = DeforestoryCase::whereIn('slug', $slugs)
            ->withCount(['laporans as laporan_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->keyBy('slug');

        // Bangun baris akhir.
        $rows = array_map(function ($card) use ($cmsMap) {
            $slug = $card['slug'] ?? null;
            $cms = $cmsMap->get($slug);

            return [
                'slug' => $slug,
                'title' => $card['title'] ?? '',
                'image' => $card['image'] ?? null,
                'category' => $card['category'] ?? '',
                'year' => $card['year'] ?? '',
                'has_detail' => (bool) $cms,
                'detail_status' => $cms?->status,
                'case_id' => $cms?->id,
                'laporan_count' => $cms?->laporan_count ?? 0,
            ];
        }, array_values($apiCases));

        // Paginasi manual (data sudah berupa array dari API).
        $page = request()->input('page', 1);
        $perPage = 10;
        $total = count($rows);
        $rows = array_slice($rows, ($page - 1) * $perPage, $perPage);

        $cases = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );

        return view('livewire.deforestory.deforestory-case-table', compact('cases'))
            ->layout('layouts.admin');
    }

    /**
     * Hapus konten detail CMS untuk sebuah slug (bukan kartu API-nya).
     */
    public function deleteDetail($slug)
    {
        $case = DeforestoryCase::where('slug', $slug)->first();
        if ($case) {
            $case->delete();
            session()->flash('success', 'Konten detail untuk "' . $slug . '" dihapus. Kartu tetap ada di API.');
        }
    }
}