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
}
