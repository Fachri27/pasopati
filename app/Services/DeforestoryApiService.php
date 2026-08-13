<?php

namespace App\Services;

use App\Models\DeforestoryCard;

/**
 * Menyediakan daftar kartu kasus Deforestory ke halaman publik & admin.
 *
 * Sebelumnya service ini nge-GET card dari web lain (mock /api/deforestory-cases,
 * atau HTTP ke DEFORESTORY_API_URL). Sekarang card DIDORONG web lain ke CMS via
 * inbound webhook (POST /api/deforestory/cards, lihat DeforestoryCardWebhookController)
 * lalu disimpan di tabel deforestory_cards. Service ini cukup baca dari tabel
 * lokal — real-time, no polling, no HTTP keluar.
 *
 * Shape card tetap sama: {slug, category, year, image, title, excerpt} per
 * locale, supaya view/livewire yang memakai getCases() tidak berubah.
 */
class DeforestoryApiService
{
    /**
     * Daftar kartu kasus sesuai locale, urut sort lalu slug. Hanya card
     * `status = 'publish'` — card 'draft' disembunyi dari publik.
     */
    public function getCases(string $locale = 'id'): array
    {
        return DeforestoryCard::query()
            ->where('status', 'publish')
            ->orderBy('sort')
            ->orderBy('slug')
            ->get()
            ->map(fn (DeforestoryCard $card) => $card->toCardArray($locale))
            ->all();
    }

    /**
     * Cari satu kartu kasus by slug. Mengembalikan shape card sesuai locale
     * atau null bila tidak ada / ber-status 'draft' (tersembunyi).
     */
    public function cardBySlug(string $locale, string $slug): ?array
    {
        $card = DeforestoryCard::where('slug', $slug)->where('status', 'publish')->first();

        return $card?->toCardArray($locale);
    }

    /**
     * No-op sekarang. Sebelumnya memaksa refresh dari API eksternal; di model
     * push, card diperbarui oleh webhook web lain → tidak ada yang di-pull.
     * Dipertahankan agar pemanggil lama (mis. tombol "refresh" di admin) tidak
     * rusak; sekadar kembalikan data lokal.
     */
    public function refresh(string $locale = 'id'): array
    {
        return $this->getCases($locale);
    }
}
