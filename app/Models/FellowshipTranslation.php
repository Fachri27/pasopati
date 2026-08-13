<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FellowshipTranslation extends Model
{
    protected $fillable = [
        'title',
        'sub_judul',
        'excerpt',
        'locale',
        'fellowship_id',
        'image',
        'image_cover',
    ];

    public function fellowship()
    {
        return $this->belongsTo(Fellowship::class);
    }

    /**
     * Teks polos untuk SEO / meta description (dipakai HasSeoMeta::getSeoData).
     * Fellowship tidak punya kolom body, jadi gabungkan judul, sub-judul, dan
     * excerpt sebagai sumber deskripsi, lalu bersihkan tag HTML-nya.
     */
    public function plainText(): string
    {
        return strip_tags(trim(($this->title ?? '').' '.($this->sub_judul ?? '').' '.($this->excerpt ?? '')));
    }
}
