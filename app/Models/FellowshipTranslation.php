<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FellowshipTranslation extends Model
{
     protected $fillable = [
        'title',
        'sub_judul',
        'excerpt',
        'locale',
        'fellowship_id',
        'image',
        'image_cover',
    ];

    public function fellowship()
    {
        return $this->belongsTo(Fellowship::class);
    }
}
