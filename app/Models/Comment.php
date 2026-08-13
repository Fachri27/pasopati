<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'page_id',
        'commentable_type',
        'commentable_id',
        'user_id',
        'name',
        'email',
        'body',
        'ip_address',
        'is_approved',
        'parent_id',
        'mention_name',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
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
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
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
     * (**tebal**, _miring_, [teks](https://...), daftar '- '/'1. ') yang
     * disisipkan tombol format di editor dikonversi ke <strong>/<em>/<a>/
     * <ul>/<ol>/<li>; sisanya di-escape supaya tidak ada injeksi HTML.
     * URL hanya http/https. Daftar bersarang pakai indent 2 spasi.
     */
    public static function formatBody(string $body): string
    {
        $escaped = e(trim($body));

        $escaped = preg_replace_callback('/\*\*([^*]+)\*\*/', fn ($m) => '<strong>'.$m[1].'</strong>', $escaped);
        $escaped = preg_replace_callback('/_([^_]+)_/', fn ($m) => '<em>'.$m[1].'</em>', $escaped);
        $escaped = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/', fn ($m) => '<a href="'.e($m[2]).'" target="_blank" rel="noopener noreferrer nofollow" class="text-[#2B5343] underline">'.e($m[1]).'</a>', $escaped);

        // Blok daftar diparse sebelum nl2br supaya <li> tidak dipecah <br>.
        $escaped = self::listsToHtml($escaped);

        return nl2br($escaped);
    }

    /**
     * Ubah baris-baris markdown daftar ('- ' / '1. ') menjadi <ul>/<ol>/<li>.
     * Baris berurutan dengan marker sama + indent sama jadi satu list; indent
     * lebih dalam = list bersarang di dalam <li> sebelumnya.
     */
    protected static function listsToHtml(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $out = '';
        $i = 0;
        $n = count($lines);
        while ($i < $n) {
            if (preg_match('/^(\s*)(-|\d+\.)\s+(.*)$/', $lines[$i])) {
                $block = self::parseListBlock($lines, $i);
                $out .= $block['html'];
                $i = $block['next'];
            } else {
                $out .= $lines[$i]."\n";
                $i++;
            }
        }

        return $out;
    }

    protected static function parseListBlock(array $lines, int $i): array
    {
        preg_match('/^(\s*)(-|\d+\.)\s+(.*)$/', $lines[$i], $first);
        $baseIndent = strlen(str_replace("\t", '  ', $first[1]));
        $ordered = (bool) preg_match('/\d+\./', $first[2]);
        $tag = $ordered ? 'ol' : 'ul';
        $html = '<'.$tag.'>';
        $n = count($lines);
        while ($i < $n) {
            if (! preg_match('/^(\s*)(-|\d+\.)\s+(.*)$/', $lines[$i], $m)) {
                break;
            }
            $indent = strlen(str_replace("\t", '  ', $m[1]));
            if ($indent !== $baseIndent) {
                break;
            }
            if ((bool) preg_match('/\d+\./', $m[2]) !== $ordered) {
                break;
            }
            $content = $m[3];
            $i++;
            $nested = '';
            while ($i < $n) {
                if (preg_match('/^(\s*)(-|\d+\.)\s+(.*)$/', $lines[$i], $nm)
                    && strlen(str_replace("\t", '  ', $nm[1])) > $baseIndent) {
                    $block = self::parseListBlock($lines, $i);
                    $nested .= $block['html'];
                    $i = $block['next'];
                } else {
                    break;
                }
            }
            $html .= '<li>'.$content.$nested.'</li>';
        }
        $html .= '</'.$tag.'>';

        return ['html' => $html, 'next' => $i];
    }
}
