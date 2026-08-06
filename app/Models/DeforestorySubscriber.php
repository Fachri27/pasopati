<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeforestorySubscriber extends Model
{
    protected $table = 'deforestory_subscribers';

    protected $fillable = [
        'email',
        'type',
        'case_id',
        'locale',
        'active',
        'unsubscribe_token',
        'subscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'subscribed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }

            if (empty($subscriber->locale)) {
                $subscriber->locale = app()->getLocale();
            }

            if (empty($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForCase($query, int $caseId)
    {
        return $query->where(function ($q) use ($caseId) {
            $q->where('type', 'all')
                ->orWhere(function ($q2) use ($caseId) {
                    $q2->where('type', 'case')->where('case_id', $caseId);
                });
        });
    }

    public function case()
    {
        return $this->belongsTo(DeforestoryCase::class, 'case_id');
    }
}
