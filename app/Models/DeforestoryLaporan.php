<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeforestoryLaporan extends Model
{
    use HasFactory;

    protected $table = 'deforestory_laporans';

    protected $fillable = [
        'case_id',
        'slug',
        'image',
        'sort',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Slug ditetapkan oleh form (diturunkan dari judul ID), tidak otomatis.
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(DeforestoryCase::class, 'case_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DeforestoryLaporanTranslation::class, 'laporan_id');
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->where('locale', $locale)->first()
            ?? $this->translations->first();
    }
}
