<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Fellowship extends Model
{
    use HasSeoMeta;

    protected $fillable = [
        'slug',
        'image',
        'meta_image',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'user_id',
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
        return $this->hasMany(FellowshipTranslation::class);
    }

    public function kategoris()
    {
        return $this->belongsToMany(
        Kategori::class,
        'kategori_fellowships',   // nama pivot table kamu
        'fellowship_id',
        'kategori_id'
        ) 
        ->withPivot(['status', 'content_id', 'content_en'])
        ->withTimestamps();  
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
