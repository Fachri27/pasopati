<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestoryCard;
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
            }, 'laporans as laporan_total'])
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
                'laporan_total' => $cms?->laporan_total ?? 0,
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
     * Hapus kartu deforestory beserta konten detail CMS-nya.
     *
     * Menghapus card di `deforestory_cards` (identitas dari web lain) DAN
     * `DeforestoryCase` (konten detail + cascade semua laporannya). Konfirmasi
     * di view lewat `wire:confirm` — pesan berbeda bila kasus masih punya
     * laporan. Card yang dihapus bisa muncul lagi bila web lain push ulang
     * via inbound webhook (POST /api/deforestory/cards).
     */
    public function deleteCard($slug)
    {
        $card = DeforestoryCard::where('slug', $slug)->first();
        $case = DeforestoryCase::where('slug', $slug)->first();

        if (! $card && ! $case) {
            session()->flash('error', 'Kartu "' . $slug . '" tidak ditemukan.');
            return;
        }

        if ($card) {
            $card->delete();
        }
        if ($case) {
            $case->delete();
        }

        session()->flash('success', 'Kartu "' . $slug . '" beserta konten detailnya dihapus.');
    }
}