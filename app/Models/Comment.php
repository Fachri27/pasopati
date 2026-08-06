<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        "page_id",
        "commentable_type",
        "commentable_id",
        "user_id",
        "name",
        "email",
        "body",
        "ip_address",
        "is_approved",
        "parent_id",
        "mention_name",
    ];

    protected function casts(): array
    {
        return [
            "is_approved" => "boolean",
        ];
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, "parent_id");
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, "parent_id");
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function likes()
    {
        return $this->reactions()->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->reactions()->where('type', 'dislike');
    }

    public function likesCount(): int
    {
        return $this->likes()->count();
    }

    public function dislikesCount(): int
    {
        return $this->dislikes()->count();
    }

    public function isLikedBy(?User $user, ?string $ip): bool
    {
        return $this->reactionBy($user, $ip)?->type === 'like';
    }

    public function isDislikedBy(?User $user, ?string $ip): bool
    {
        return $this->reactionBy($user, $ip)?->type === 'dislike';
    }

    protected function reactionBy(?User $user, ?string $ip): ?CommentReaction
    {
        if ($user) {
            return $this->reactions->where('user_id', $user->id)->first();
        }

        return $this->reactions->where('ip_address', $ip)->first();
    }

    public function displayName(): string
    {
        return $this->user?->name ?? $this->name ?? 'Anonim';
    }

    /**
     * Jumlah seluruh balasan di dalam benang ini (anak, cucu, dst.) —
     * dipakai label "Lihat N balasan" pada toggle thread.
     */
    public function descendantsCount(): int
    {
        return $this->replies->sum(fn ($reply) => 1 + $reply->descendantsCount());
    }

    /**
     * Render body komentar menjadi HTML aman. Markup ringan gaya markdown
     * (**tebal**, _miring_, [teks](https://...)) yang disisipkan tombol
     * format di editor dikonversi ke <strong>/<em>/<a>; sisanya di-escape
     * supaya tidak ada injeksi HTML. URL hanya http/https.
     */
    public static function formatBody(string $body): string
    {
        $escaped = e(trim($body));

        $escaped = preg_replace_callback('/\*\*([^*]+)\*\*/', fn ($m) => '<strong>'.$m[1].'</strong>', $escaped);
        $escaped = preg_replace_callback('/_([^_]+)_/', fn ($m) => '<em>'.$m[1].'</em>', $escaped);
        $escaped = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/', fn ($m) => '<a href="'.e($m[2]).'" target="_blank" rel="noopener noreferrer nofollow" class="text-[#2B5343] underline">'.e($m[1]).'</a>', $escaped);

        return nl2br($escaped);
    }
}
