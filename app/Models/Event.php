<?php

namespace App\Models;

use App\Enums\EventOrientation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = [
        'image_id',
        'image_en',
        'video',
        'title_id',
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
