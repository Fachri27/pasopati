<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriTranslation extends Model
{
    protected $fillable = [
        'locale',
        'kategori_name',
        'content',
        'kategori_id',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Teks polos untuk SEO / meta description (dipakai HasSeoMeta::getSeoData).
     */
    public function plainText(): string
    {
        return strip_tags(trim(($this->kategori_name ?? '') . ' ' . ($this->content ?? '')));
    }
}
