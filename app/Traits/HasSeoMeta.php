<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSeoMeta
{
    public function getSeoData(string $locale = 'id')
    {
        $translation = $this->translations->where('locale', $locale)->first();

        // daftar kemungkinan nama field image
        // $imageFields = ['featured_image', 'image'];

        // $imagePath = null;

        // // cari field pertama yang ada dan tidak null
        // foreach ($imageFields as $field) {
        //     if (! empty($this->{$field})) {
        //         $imagePath = $this->{$field};
        //         break;
        //     }
        // }

        return [
            'title' => $translation->title ?? ($this->title ?? config('app.name')),
            'description' => $translation->meta_description ?? Str::limit(strip_tags($translation->deskripsi ?? $translation->content ?? ''), 160),
            // 'image' => isset($this->featured_image) ? asset('storage/' . $this->featured_image) : asset('img/image.png'),
            'image' => $this->getSeoImage(),
            'type' => 'article',
        ];
    }


    protected function getSeoImage()
    {
        if (empty($this->featured_image)) {
            return asset('img/image.png');
        }

        $filename = basename($this->featured_image);

        // cek meta image (1200x630)
        $metaPath = 'pages/meta/' . $filename;

        if (file_exists(storage_path('app/public/' . $metaPath))) {
            return asset('storage/' . $metaPath);
        }

        // fallback ke original image
        return asset('storage/' . $this->featured_image);
    }

}
