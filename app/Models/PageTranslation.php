<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    use HasFactory;

    // SARAN: tambahkan 'content_blocks' ke fillable jika migration JSON sudah ditambahkan
    // Setelah migrasi, content bisa dipertahankan untuk backward-compat,
    // sementara content_blocks jadi sumber utama rendering frontend.
    protected $fillable = ['page_id', 'locale', 'title', 'excerpt', 'content', 'content_blocks'];

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
        ];
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function plainText(): string
    {
        if ($this->content_blocks) {
            $parts = [];
            foreach ($this->content_blocks as $block) {
                $parts[] = match ($block['type'] ?? '') {
                    'paragraph' => strip_tags($block['data']['html'] ?? ''),
                    'image' => $block['data']['caption'] ?? '',
                    'event_info_box' => ($block['data']['format'] ?? '') . ' ' . ($block['data']['notes'] ?? ''),
                    'agenda_day' => collect($block['data']['sessions'] ?? [])
                        ->pluck('title')->implode(' '),
                    'speaker_bio' => ($block['data']['name'] ?? '') . ' ' . ($block['data']['title'] ?? '') . ' ' . strip_tags($block['data']['bio'] ?? ''),
                    'quote' => ($block['data']['text'] ?? '') . ' ' . ($block['data']['source'] ?? ''),
                    default => '',
                };
            }
            return implode(' ', $parts);
        }
        return strip_tags($this->content ?? '');
    }
}
