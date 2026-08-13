@php
    $isEdit = ! is_null($event) && $event->exists;
    $formAction = $isEdit ? route('events.update', $event) : route('events.store');
    $orientation = old('orientation', $event?->orientation?->value ?? 'landscape');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-white shadow rounded-lg p-6">
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="{{ route('events.index') }}" class="hover:text-blue-600 font-medium">Event / Kejadian</a>
            <span class="text-gray-400">›</span>
            <span class="text-blue-600 font-semibold">{{ $isEdit ? 'Edit Event' : 'Tambah Event' }}</span>
        </nav>

        <h1 class="text-2xl font-bold mb-8 text-gray-800">{{ $isEdit ? 'Edit Event' : 'Tambah Event' }}</h1>

        <form id="event-form" method="POST" action="{{ $formAction }}"
              enctype="multipart/form-data" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            @if ($errors->any())
                <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-12 gap-6">
                {{-- ================= KIRI: Gambar + Orientation ================= --}}
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    {{-- Gambar Indonesia --}}
                    <div>
                        <label class="block font-medium text-gray-700">Gambar Indonesia <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">JPG, JPEG, PNG, WEBP — maks 100 MB · wajib bila tanpa video</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <img id="preview-image-id"
                                 src="{{ old('image_id') ? '' : ($event?->image_id_url ?? '') }}"
                                 alt="Preview Image Indonesia"
                                 class="mx-auto rounded border border-gray-200 object-cover max-h-56 {{ $event?->image_id ? '' : 'hidden' }}"
                                 data-orientation-preview
                                 style="aspect-ratio: 16 / 9">
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                {{ $isEdit && $event->image_id ? 'Ganti Gambar' : 'Pilih Gambar' }}
                                <input type="file" name="image_id" accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="hidden" data-image-input data-preview-target="preview-image-id">
                            </label>
                            @if ($isEdit && $event->image_id)
                                <p class="text-xs text-gray-500 mt-2">Gambar lama dipertahankan jika tidak diganti.</p>
                            @endif
                        </div>
                        @error('image_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gambar English --}}
                    <div>
                        <label class="block font-medium text-gray-700">Gambar English <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">JPG, JPEG, PNG, WEBP — maks 100 MB · wajib bila tanpa video</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <img id="preview-image-en"
                                 src="{{ old('image_en') ? '' : ($event?->image_en_url ?? '') }}"
                                 alt="Preview Image English"
                                 class="mx-auto rounded border border-gray-200 object-cover max-h-56 {{ $event?->image_en ? '' : 'hidden' }}"
                                 data-orientation-preview
                                 style="aspect-ratio: 16 / 9">
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                {{ $isEdit && $event->image_en ? 'Ganti Gambar' : 'Pilih Gambar' }}
                                <input type="file" name="image_en" accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="hidden" data-image-input data-preview-target="preview-image-en">
                            </label>
                            @if ($isEdit && $event->image_en)
                                <p class="text-xs text-gray-500 mt-2">Gambar lama dipertahankan jika tidak diganti.</p>
                            @endif
                        </div>
                        @error('image_en')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Video --}}
                    <div>
                        <label class="block font-medium text-gray-700">Video <span class="text-xs text-gray-400 font-normal">(opsional)</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">MP4, MOV, MKV, WEBM — maks 100 MB. Bila gambar tidak diunggah, thumbnail otomatis diambil dari frame video.</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <video id="preview-video" controls preload="metadata"
                                   class="mx-auto rounded border border-gray-200 max-h-56 {{ $event?->has_video ? '' : 'hidden' }}">
                                @if ($event?->video)
                                    <source src="{{ $event->video_url }}">
                                @endif
                            </video>
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                {{ $isEdit && $event->has_video ? 'Ganti Video' : 'Pilih Video' }}
                                <input type="file" name="video" accept="video/mp4,video/quicktime,video/x-matroska,video/webm"
                                       class="hidden" data-video-input data-video-preview="preview-video">
                            </label>
                            @if ($isEdit && $event->has_video)
                                <p class="text-xs text-gray-500 mt-2">Video lama dipertahankan jika tidak diganti.</p>
                            @endif
                        </div>
                        @error('video')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Orientation --}}
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Image Orientation <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="orientation" value="landscape" @checked($orientation === 'landscape')>
                                <span class="text-sm text-gray-700">Landscape</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="orientation" value="horizontal" @checked($orientation === 'horizontal')>
                                <span class="text-sm text-gray-700">Horizontal</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Preview gambar menyesuaikan aspect ratio pilihan.</p>
                        @error('orientation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ================= KANAN: Data Event ================= --}}
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    {{-- Title ID --}}
                    <div>
                        <label for="title_id" class="block font-medium text-gray-700">Title (Indonesia) <span class="text-red-500">*</span></label>
                        <input type="text" id="title_id" name="title_id" value="{{ old('title_id', $event?->title_id) }}"
                               maxlength="255" placeholder="Judul event dalam Bahasa Indonesia"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        @error('title_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Title EN --}}
                    <div>
                        <label for="title_en" class="block font-medium text-gray-700">Title (English) <span class="text-red-500">*</span></label>
                        <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $event?->title_en) }}"
                               maxlength="255" placeholder="Event title in English"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        @error('title_en')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Kejadian --}}
                    <div>
                        <label for="event_date" class="block font-medium text-gray-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                        <input type="date" id="event_date" name="event_date"
                               value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}"
                               data-date-picker
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        @error('event_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pencarian Lokasi GeoServer --}}
                    <div>
                        <label for="location-search" class="block font-medium text-gray-700">Cari Lokasi (GeoServer)</label>
                        <input type="text" id="location-search" autocomplete="off"
                               placeholder="Ketik nama lokasi, mis. bandung..."
                               value="{{ old('location', $event?->location) }}"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        <div id="location-results" class="mt-1 w-full rounded-lg border border-gray-200 shadow-sm hidden"></div>
                        <input type="hidden" name="location" id="location" value="{{ old('location', $event?->location) }}">
                        <input type="hidden" name="location_geojson" id="location_geojson"
                               value="{{ old('location_geojson', $event?->location_geojson ? json_encode($event->location_geojson) : '') }}">
                        <p class="text-xs text-gray-500 mt-1">Pilih hasil dari GeoServer atau klik langsung pada peta.</p>
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Peta --}}
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Pilih Lokasi di Peta</label>
                        <div id="event-map" class="h-96 w-full rounded-lg border border-gray-300 z-0"></div>
                    </div>

                    {{-- Koordinat --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="location_lat" class="block font-medium text-gray-700">Latitude</label>
                            <input type="number" step="any" min="-90" max="90" id="location_lat" name="location_lat"
                                   value="{{ old('location_lat', $event?->location_lat) }}" data-coordinate="lat"
                                   placeholder="-6.917500"
                                   class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                            @error('location_lat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="location_lng" class="block font-medium text-gray-700">Longitude</label>
                            <input type="number" step="any" min="-180" max="180" id="location_lng" name="location_lng"
                                   value="{{ old('location_lng', $event?->location_lng) }}" data-coordinate="lng"
                                   placeholder="107.609100"
                                   class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                            @error('location_lng')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                            Simpan
                        </button>
                        <a href="{{ route('events.index') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
