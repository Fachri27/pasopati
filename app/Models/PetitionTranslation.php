<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetitionTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'petition_id',
        'locale',
        'title',
        'description',
    ];

    public function petition(): BelongsTo
    {
        return $this->belongsTo(Petition::class);
    }
}
