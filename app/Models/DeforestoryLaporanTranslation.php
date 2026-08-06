<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeforestoryLaporanTranslation extends Model
{
    protected $table = 'deforestory_laporan_translations';

    protected $fillable = [
        'laporan_id',
        'locale',
        'title',
        'excerpt',
        'content',
        'image',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(DeforestoryLaporan::class, 'laporan_id');
    }
}