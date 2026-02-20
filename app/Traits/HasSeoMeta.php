<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSeoMeta
{
    public function getSeoData(string $locale = 'id')
    {
        $translation = $this->translations->where('locale', $locale)->first();

        return [
            'title' => $translation->title ?? config('app.name'),
            'description' => $translation->meta_description 
                ?? Str::limit(strip_tags($translation->deskripsi ?? $translation->content ?? ''), 160),
            'image' => $this->getSeoImage($locale),
            'type' => 'article',
        ];
    }


    protected function getSeoImage(string $locale = 'id')
    {
        $translation = $this->translations->where('locale', $locale)->first();

        $imagePath = 
            $translation->image ??
            $translation->image ??
            $this->featured_image ??
            $this->image ??
            null;

        if (! $imagePath) {
            return asset('img/image.png');
        }

        $filename = basename($imagePath);

        // cek apakah ada versi meta 1200x630
        $metaPath = 'pages/meta/' . $filename;

        if (file_exists(storage_path('app/public/' . $metaPath))) {
            return asset('storage/' . $metaPath);
        }

        return asset('storage/' . $imagePath);
    }


}
