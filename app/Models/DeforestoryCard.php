<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kartu kasus Deforestory yang didorong web lain ke CMS via inbound webhook
 * (POST /api/deforestory/cards). CMS baca card dari tabel ini — bukan lagi
 * nge-GET ke web lain.
 *
 * Menyimpan title/excerpt/image untuk id + en sekaligus. toCardArray($locale)
 * mengembalikan shape {slug, category, year, image, title, excerpt} sesuai
 * locale (fallback ke 'id') — shape yang sama yang dipakai view/livewire.
 */
class DeforestoryCard extends Model
{
    protected $table = 'deforestory_cards';

    protected $fillable = [
        'slug',
        'category',
        'year',
        'image_id',
        'image_en',
        'title_id',
        'title_en',
        'excerpt_id',
        'excerpt_en',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    /**
     * Bentuk card siap-pakai konsumen, sesuai locale.
     * Image per-locale (image_id/image_en) dengan fallback ke field 'id'.
     * Title/excerpt fallback ke field 'id'.
     */
    public function toCardArray(string $locale): array
    {
        $titleField = $locale === 'en' ? 'title_en' : 'title_id';
        $excerptField = $locale === 'en' ? 'excerpt_en' : 'excerpt_id';
        $imageField = $locale === 'en' ? 'image_en' : 'image_id';

        $title = $this->$titleField ?: $this->title_id;
        $excerpt = $this->$excerptField ?: $this->excerpt_id;
        $image = $this->$imageField ?: $this->image_id;

        return [
            'slug' => $this->slug,
            'category' => $this->category,
            'year' => $this->year,
            'image' => $image,
            'title' => $title,
            'excerpt' => $excerpt,
        ];
    }
}