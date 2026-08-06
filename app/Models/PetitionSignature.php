<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetitionSignature extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'petition_id',
        'name',
        'email',
        'city',
        'comment',
        'is_verified',
        'verification_token',
        'ip_address',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function petition(): BelongsTo
    {
        return $this->belongsTo(Petition::class);
    }
}
