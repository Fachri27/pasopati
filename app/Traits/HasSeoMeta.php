<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSeoMeta
{
    public function getSeoData(string $locale = 'id')
    {
        $translation = $this->translations->where('locale', $locale)->first();

        // daftar kemungkinan nama field image
        $imageFields = ['image', 'featured_image'];

        $imagePath = null;

        // cari field pertama yang ada dan tidak null
        foreach ($imageFields as $field) {
            if (! empty($this->{$field})) {
                $imagePath = $this->{$field};
                break;
            }
        }

        return [
            'title' => 'Pasopati - '.($translation->title ?? $this->title ?? config('app.name')),
            'description' => $translation->meta_description
                ?? Str::limit(strip_tags($translation->deskripsi ?? $translation->content ?? ''), 160),
            'image' => $imagePath
                ? asset('storage/'.$imagePath)
                : asset('img/image.png'),
            'type' => 'article',
        ];
    }
}
