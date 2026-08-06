<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeforestoryCase extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $table = 'deforestory_cases';

    protected $fillable = [
        'slug',
        'status',
        'featured_image',
        'category',
        'year',
        'sort',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        // Slug ditetapkan eksplisit oleh CMS form agar cocok dengan kartu
        // dari API (di-match via slug). Tidak diturunkan dari title.
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DeforestoryCaseTranslation::class, 'case_id');
    }

    public function laporans(): HasMany
    {
        return $this->hasMany(DeforestoryLaporan::class, 'case_id')->orderBy('sort');
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

    /**
     * Laporan aktif kasus untuk locale terkini (dengan translations di-load).
     */
    public function activeLaporans($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->laporans()
            ->where('status', 'active')
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale)->orWhere('locale', 'id');
            }])
            ->orderBy('sort')
            ->get();
    }

    /**
     * Cari laporan berdasarkan slug di dalam kasus ini.
     */
    public function laporanBySlug($locale = null, $laporanSlug = null)
    {
        $laporan = $this->laporans()
            ->where('status', 'active')
            ->where('slug', $laporanSlug)
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale)->orWhere('locale', 'id');
            }])
            ->first();

        return $laporan;
    }
}