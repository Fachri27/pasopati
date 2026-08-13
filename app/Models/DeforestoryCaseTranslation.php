<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeforestoryCaseTranslation extends Model
{
    use HasFactory;

    protected $table = 'deforestory_case_translations';

    protected $fillable = [
        'case_id',
        'locale',
        'title',
        'intro',
        'excerpt',
        'laporan_content',
        'chapters',
    ];

    protected function casts(): array
    {
        return [
            'chapters' => 'array',
        ];
    }

    public function case()
    {
        return $this->belongsTo(DeforestoryCase::class, 'case_id');
    }

    /**
     * Teks polos untuk SEO / meta description (dipakai HasSeoMeta::getSeoData).
     */
    public function plainText(): string
    {
        $text = $this->intro.' '.$this->laporan_content;

        if ($this->chapters) {
            foreach ($this->chapters as $chapter) {
                $text .= ' '.($chapter['title'] ?? '').' '.($chapter['body'] ?? '');
            }
        }

        return strip_tags($text ?? '');
    }
}
