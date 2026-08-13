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
        'title_id',
        'slug',
        'title_en',
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
        ];
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
