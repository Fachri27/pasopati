<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Petition extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'target_name',
        'demands',
        'cover_image',
        'goal_count',
        'status',
        'published_at',
        'user_id',
    ];

    protected $casts = [
        'demands' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($petition) {
            if (empty($petition->slug)) {
                $petition->slug = Str::slug(request()->input('title_id'));
            }
        });

        static::updating(function ($petition) {
            if (request()->filled('title_id')) {
                $petition->slug = Str::slug(request()->input('title_id'));
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PetitionTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->where('locale', $locale)->first();
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(PetitionSignature::class);
    }

    public function verifiedSignatures(): HasMany
    {
        return $this->hasMany(PetitionSignature::class)->where('is_verified', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signatureCount(): int
    {
        return $this->verifiedSignatures()->count();
    }

    public function progressPercent(): int
    {
        if ($this->goal_count <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->signatureCount() / $this->goal_count) * 100));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
