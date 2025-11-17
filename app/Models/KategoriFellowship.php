<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriFellowship extends Model
{
    protected $table = 'kategori_fellowships';

    protected $fillable = [
        'status',
        'content_id',
        'content_en',
    ];

    public function fellowship()
    {
        return $this->belongsTo(Fellowship::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
