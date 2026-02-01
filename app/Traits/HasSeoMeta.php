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
            'image' => isset($this->featured_image) ? asset('storage/' . $this->featured_image) : asset('images/logo.png'),
            'type' => 'article',
        ];
    }
}
