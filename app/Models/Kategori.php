<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasSeoMeta;
    protected $fillable = [];

    public function translations()
    {
        return $this->hasMany(KategoriTranslation::class);
    }

    public function fellowships()
    {
        return $this->belongsToMany(Fellowship::class, 'kategori_fellowships')
        ->withPivot('status', 'content_id', 'content_en')
        ->withTimestamps();
    }
}
