<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'image_id' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:102400', 'required_without:video'],
            'image_en' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:102400', 'required_without:video'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,mkv,webm', 'max:102400'],
            'title_id' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'location' => ['required', 'string', 'max:255'],
            'location_lat' => ['required', 'numeric', 'between:-90,90'],
            'location_lng' => ['required', 'numeric', 'between:-180,180'],
            'location_geojson' => ['nullable', 'json'],
            'orientation' => ['required', 'in:landscape,horizontal'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image_id.required' => 'Gambar Bahasa Indonesia wajib diunggah.',
            'image_id.required_without' => 'Gambar atau file video wajib diunggah.',
            'image_id.image' => 'Gambar Bahasa Indonesia harus berupa file gambar.',
            'image_id.mimes' => 'Gambar Bahasa Indonesia hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'image_id.max' => 'Ukuran Gambar Bahasa Indonesia maksimal 100 MB.',
            'image_en.required_without' => 'Gambar atau file video wajib diunggah.',
            'image_en.image' => 'Gambar Bahasa Inggris harus berupa file gambar.',
            'image_en.mimes' => 'Gambar Bahasa Inggris hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'image_en.max' => 'Ukuran Gambar Bahasa Inggris maksimal 100 MB.',
            'video.file' => 'Video harus berupa file.',
            'video.mimes' => 'Video hanya boleh MP4, MOV, MKV, atau WEBM.',
            'video.max' => 'Ukuran video maksimal 100 MB.',
            'title_id.required' => 'Title (Indonesia) wajib diisi.',
            'title_id.max' => 'Title (Indonesia) maksimal 255 karakter.',
            'title_en.required' => 'Title (English) wajib diisi.',
            'title_en.max' => 'Title (English) maksimal 255 karakter.',
            'event_date.required' => 'Tanggal kejadian wajib diisi.',
            'event_date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'location.required' => 'Lokasi wajib diisi (cari via GeoServer atau klik peta).',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'location_lat.required' => 'Latitude wajib diisi.',
            'location_lat.between' => 'Latitude harus antara -90 dan 90.',
            'location_lng.required' => 'Longitude wajib diisi.',
            'location_lng.between' => 'Longitude harus antara -180 dan 180.',
            'location_geojson.json' => 'Geometry lokasi tidak valid.',
            'orientation.required' => 'Orientation wajib dipilih.',
            'orientation.in' => 'Orientation harus landscape atau horizontal.',
        ];
    }
}
