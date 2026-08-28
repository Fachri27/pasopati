<?php

namespace App\Models;

use App\Enums\EventOrientation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'image_id',
        'image_en',
        'video',
        'media',
        'title_id',
        'slug',
        'title_en',
        'desc_id',
        'desc_en',
        'event_date',
        'location',
        'location_lat',
        'location_lng',
        'location_geojson',
        'orientation',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'location_lat' => 'float',
            'location_lng' => 'float',
            'location_geojson' => 'array',
            'orientation' => EventOrientation::class,
            'media' => 'array',
        ];
    }

    /**
     * Normalized gallery items for the fire card slider and admin table.
     * Falls back to the legacy single image/video columns so existing events
     * keep working without a data migration.
     */
    public function getMediaItemsAttribute(): array
    {
        $items = [];
        $paths = [];

        foreach ($this->media ?? [] as $item) {
            if (is_array($item) && ! empty($item['path']) && ! empty($item['type'])) {
                $items[] = [
                    'type' => $item['type'],
                    'url' => Storage::disk('public')->url($item['path']),
                    'path' => $item['path'],
                ];
                $paths[] = $item['path'];
            }
        }

        // Jika hanya media legacy (image_id/video) yang tersedia, bangun satu
        // item agar event lama tetap tampil di slider.
        if ($items === []) {
            if ($this->video) {
                $items[] = ['type' => 'video', 'url' => $this->video_url, 'path' => $this->video];
            }
            if ($this->image_id) {
                $items[] = ['type' => 'image', 'url' => $this->image_id_url, 'path' => $this->image_id];
            }

            return $items;
        }

        // Gabungkan media utama ke depan galeri kalau belum ada di dalamnya.
        $primary = [];
        if ($this->video && ! in_array($this->video, $paths, true)) {
            $primary[] = ['type' => 'video', 'url' => $this->video_url, 'path' => $this->video];
        }
        if ($this->image_id && ! in_array($this->image_id, $paths, true)) {
            $primary[] = ['type' => 'image', 'url' => $this->image_id_url, 'path' => $this->image_id];
        }

        return array_merge($primary, $items);
    }

    /*
     * Slug dari `title_id` diisi otomatis saat event baru disimpan dan
     * dibiarkan apa adanya saat judul diubah — agar tautan share yang sudah
     * tersebar tetap mengarah ke event yang sama. Hanya diisi kalau kosong,
     * jadi editor boleh menetapkan slug sendiri kalau perlu. Dijaga unik
     * dengan menambah akhiran -2, -3, dst. bila bentuk dasarnya dipakai
     * event lain.
     */
    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            if (! empty($event->slug)) {
                return;
            }

            $dasar = Str::slug($event->title_id) ?: ('event-' . ($event->id ?? 'baru'));
            $slug = $dasar;
            $i = 1;
            while (Event::where('slug', $slug)->where('id', '!=', $event->id ?? 0)->exists()) {
                $slug = $dasar . '-' . ++$i;
            }
            $event->slug = $slug;
        });
    }

    public function getImageIdUrlAttribute(): ?string
    {
        return $this->image_id ? Storage::disk('public')->url($this->image_id) : null;
    }

    public function getImageEnUrlAttribute(): ?string
    {
        return $this->image_en ? Storage::disk('public')->url($this->image_en) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? Storage::disk('public')->url($this->video) : null;
    }

    public function getHasVideoAttribute(): bool
    {
        return filled($this->video);
    }

    public function getEventDateDisplayAttribute(): string
    {
        return $this->event_date?->format('d F Y') ?? '-';
    }

    public function getCoordinateDisplayAttribute(): string
    {
        if ($this->location_lat === null || $this->location_lng === null) {
            return '-';
        }

        return number_format($this->location_lat, 6, ',', '.').', '.number_format($this->location_lng, 6, ',', '.');
    }
}
