<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;
    use HasSeoMeta;

    // SARAN: tambahkan 'content_blocks' jika migration kolom JSON sudah ditambahkan
    protected $fillable = [
        'slug',
        'type',
        'page_type',
        'featured_image',
        'expose_type',
        'published_at',
        'status',
        'user_id',
    ];

    // SARAN: tambahkan 'content_blocks' => 'array' jika migration JSON sudah ditambahkan
    protected $casts = [
        'expose_type' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug(request()->input('title_id'));
            }
        });

        static::updating(function ($page) {
            if (request()->filled('title_id')) {
                $page->slug = Str::slug(request()->input('title_id'));
            }
        });
    }

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->where('locale', $locale)->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->where('is_approved', true)->latest();
    }
}
