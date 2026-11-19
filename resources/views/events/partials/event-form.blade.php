@php
    $isEdit = ! is_null($event) && $event->exists;
    $formAction = $isEdit ? route('events.update', $event) : route('events.store');
    $orientation = old('orientation', $event?->orientation?->value ?? 'landscape');
@endphp

{{-- Simpan ada di bilah atas yang menempel, di luar <form>-nya; atribut
     form="event-form" yang menyambungkannya. id form itu juga dipakai
     resources/js/event.js untuk menyalakan peta, pencarian lokasi, dan
     pratinjau media — jangan diganti. --}}
<x-admin.page-header :title="$isEdit ? 'Ubah kejadian' : 'Kejadian baru'"
                     :crumbs="[['Kejadian', route('events.index')]]"
                     :back="route('events.index')">
    <a href="{{ route('events.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
    <button type="submit" form="event-form" class="admin-btn admin-btn--primary admin-btn--sm">
        {{ $isEdit ? 'Simpan perubahan' : 'Simpan kejadian' }}
    </button>
</x-admin.page-header>

<div class="admin-wrap admin-body">
    @if ($errors->any())
        <div class="admin-alert mb-5" role="alert">
            <div>
                <p class="font-medium">Kejadian belum tersimpan.</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="event-form" method="POST" action="{{ $formAction }}"
          enctype="multipart/form-data" novalidate
          class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- ================= Kolom utama: isi + lokasi ================= --}}
        <div class="grid min-w-0 gap-5">
            <x-admin.card title="Isi">
                <div class="grid gap-4">
                    <div>
                        <label for="title_id" class="admin-label">Judul (Indonesia) <span class="text-admin-ember" aria-hidden="true">*</span></label>
                        <input type="text" id="title_id" name="title_id" value="{{ old('title_id', $event?->title_id) }}"
                               maxlength="255" placeholder="Judul kejadian dalam Bahasa Indonesia"
                               class="admin-input" @error('title_id') aria-invalid="true" @enderror>
                        @error('title_id')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title_en" class="admin-label">Judul (English) <span class="text-admin-ember" aria-hidden="true">*</span></label>
                        <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $event?->title_en) }}"
                               maxlength="255" placeholder="Event title in English"
                               class="admin-input" @error('title_en') aria-invalid="true" @enderror>
                        @error('title_en')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="desc_id" class="admin-label">Deskripsi (Indonesia)</label>
                        <textarea id="desc_id" name="desc_id" rows="4"
                                  placeholder="Deskripsi kejadian dalam Bahasa Indonesia"
                                  class="admin-input" @error('desc_id') aria-invalid="true" @enderror>{{ old('desc_id', $event->desc_id ?? '') }}</textarea>
                        @error('desc_id')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="desc_en" class="admin-label">Deskripsi (English)</label>
                        <textarea id="desc_en" name="desc_en" rows="4"
                                  placeholder="Event description in English"
                                  class="admin-input" @error('desc_en') aria-invalid="true" @enderror>{{ old('desc_en', $event->desc_en ?? '') }}</textarea>
                        @error('desc_en')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:max-w-[240px]">
                        <label for="event_date" class="admin-label">Tanggal kejadian <span class="text-admin-ember" aria-hidden="true">*</span></label>
                        <input type="date" id="event_date" name="event_date"
                               value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}"
                               data-date-picker
                               class="admin-input" @error('event_date') aria-invalid="true" @enderror>
                        @error('event_date')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Lokasi" description="Pilih hasil pencarian GeoServer, atau klik langsung pada peta.">
                <div class="grid gap-4">
                    <div>
                        <label for="location-search" class="admin-label">Cari lokasi <span class="text-admin-ember" aria-hidden="true">*</span></label>
                        <input type="text" id="location-search" autocomplete="off"
                               placeholder="Ketik nama lokasi, mis. bandung…"
                               value="{{ old('location', $event?->location) }}"
                               class="admin-input" @error('location') aria-invalid="true" @enderror>
                        {{-- bg sendiri, kalau tidak isinya menumpang latar halaman.
                             max-h + overflow: hasil GeoServer untuk kata seumum
                             "depok" bisa puluhan baris dan mendorong sisa form
                             keluar layar. --}}
                        <div id="location-results"
                             class="mt-1 hidden max-h-72 w-full overflow-y-auto rounded-md border border-admin-line-strong bg-admin-surface"></div>
                        <input type="hidden" name="location" id="location" value="{{ old('location', $event?->location) }}">
                        <input type="hidden" name="location_geojson" id="location_geojson"
                               value="{{ old('location_geojson', $event?->location_geojson ? json_encode($event->location_geojson) : '') }}">
                        @error('location')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- isolate + z-0: lapisan Leaflet memakai z-index sampai
                         1000; tanpa konteks tumpukan sendiri petanya naik
                         menimpa bilah atas halaman yang menempel. --}}
                    <div id="event-map" class="isolate z-0 h-96 w-full rounded-md border border-admin-line"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="location_lat" class="admin-label">Latitude <span class="text-admin-ember" aria-hidden="true">*</span></label>
                            <input type="number" step="any" min="-90" max="90" id="location_lat" name="location_lat"
                                   value="{{ old('location_lat', $event?->location_lat) }}" data-coordinate="lat"
                                   placeholder="-6.917500"
                                   class="admin-input admin-num" @error('location_lat') aria-invalid="true" @enderror>
                            @error('location_lat')
                                <p class="admin-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="location_lng" class="admin-label">Longitude <span class="text-admin-ember" aria-hidden="true">*</span></label>
                            <input type="number" step="any" min="-180" max="180" id="location_lng" name="location_lng"
                                   value="{{ old('location_lng', $event?->location_lng) }}" data-coordinate="lng"
                                   placeholder="107.609100"
                                   class="admin-input admin-num" @error('location_lng') aria-invalid="true" @enderror>
                            @error('location_lng')
                                <p class="admin-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- ================= Rel samping: media ================= --}}
        {{-- Sengaja tidak menempel (sticky): tiga unggahan berikut
             pratinjaunya lebih tinggi dari layar, dan rel menempel yang lebih
             tinggi dari layar menyembunyikan bagian bawahnya sendiri. --}}
        <div class="grid min-w-0 gap-5">
            <x-admin.card title="Media utama" description="Paling tidak satu: gambar, video, atau galeri.">
                <div class="grid gap-5">
                    {{-- Isian berkasnya dibiarkan tampil apa adanya (bukan
                         disembunyikan di balik tombol tiruan): nama berkas
                         yang dipilih terlihat, dan bisa dijangkau papan ketik. --}}
                    <div>
                        <label for="image_id" class="admin-label">Gambar Indonesia</label>
                        <img id="preview-image-id"
                             src="{{ old('image_id') ? '' : ($event?->image_id_url ?? '') }}"
                             alt="Pratinjau gambar Indonesia"
                             class="mb-2 w-full rounded-md border border-admin-line object-cover {{ $event?->image_id ? '' : 'hidden' }}"
                             data-orientation-preview
                             style="aspect-ratio: 16 / 9">
                        <input type="file" id="image_id" name="image_id" accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="admin-input" data-image-input data-preview-target="preview-image-id"
                               @error('image_id') aria-invalid="true" @enderror>
                        <p class="admin-hint">
                            JPG, PNG, atau WEBP, paling besar 100 MB.
                            @if ($isEdit && $event->image_id)
                                Gambar lama dipertahankan jika tidak diganti.
                            @endif
                        </p>
                        @error('image_id')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="image_en" class="admin-label">Gambar English</label>
                        <img id="preview-image-en"
                             src="{{ old('image_en') ? '' : ($event?->image_en_url ?? '') }}"
                             alt="Pratinjau gambar English"
                             class="mb-2 w-full rounded-md border border-admin-line object-cover {{ $event?->image_en ? '' : 'hidden' }}"
                             data-orientation-preview
                             style="aspect-ratio: 16 / 9">
                        <input type="file" id="image_en" name="image_en" accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="admin-input" data-image-input data-preview-target="preview-image-en"
                               @error('image_en') aria-invalid="true" @enderror>
                        <p class="admin-hint">
                            JPG, PNG, atau WEBP, paling besar 100 MB.
                            @if ($isEdit && $event->image_en)
                                Gambar lama dipertahankan jika tidak diganti.
                            @endif
                        </p>
                        @error('image_en')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="video" class="admin-label">Video <span class="font-normal text-admin-muted">(opsional)</span></label>
                        <video id="preview-video" controls preload="metadata"
                               class="mb-2 w-full rounded-md border border-admin-line bg-black {{ $event?->has_video ? '' : 'hidden' }}">
                            @if ($event?->video)
                                <source src="{{ $event->video_url }}">
                            @endif
                        </video>
                        <input type="file" id="video" name="video" accept="video/mp4,video/quicktime,video/x-matroska,video/webm"
                               class="admin-input" data-video-input data-video-preview="preview-video"
                               @error('video') aria-invalid="true" @enderror>
                        <p class="admin-hint">
                            MP4, MOV, MKV, atau WEBM, paling besar 100 MB. Bila gambar tidak diunggah, thumbnail diambil dari frame video.
                            @if ($isEdit && $event->has_video)
                                Video lama dipertahankan jika tidak diganti.
                            @endif
                        </p>
                        @error('video')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p id="label-orientasi" class="admin-label">Orientasi gambar <span class="text-admin-ember" aria-hidden="true">*</span></p>
                        {{-- Radio asli di balik tab bersegmen: .admin-tabs
                             menandai label yang radionya terpilih, dan
                             event.js tetap membaca input[name="orientation"]. --}}
                        <div class="admin-tabs" role="radiogroup" aria-labelledby="label-orientasi">
                            <label>
                                <input type="radio" name="orientation" value="landscape" class="sr-only" @checked($orientation === 'landscape')>
                                Landscape
                            </label>
                            <label>
                                <input type="radio" name="orientation" value="horizontal" class="sr-only" @checked($orientation === 'horizontal')>
                                Horizontal
                            </label>
                        </div>
                        <p class="admin-hint">Landscape 16:9, Horizontal 4:3. Pratinjau gambar di atas mengikuti pilihan ini.</p>
                        @error('orientation')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Galeri" description="Boleh beberapa foto atau video sekaligus.">
                <div x-data="mediaGallery()">
                    @if ($isEdit && !empty($event->media))
                        {{-- Centang = dipertahankan. Keadaan "akan dihapus"
                             adalah gaya bawaannya dan peer-checked yang
                             membatalkannya, karena Tailwind tidak punya varian
                             untuk "peer tidak tercentang". --}}
                        <div class="mb-3 grid grid-cols-3 gap-2">
                            @foreach ($event->media as $index => $item)
                                <label class="relative block cursor-pointer overflow-hidden rounded-md border border-admin-line bg-admin-inset">
                                    <input type="checkbox" name="keep_media[]" value="{{ $index }}" checked class="peer sr-only"
                                           aria-label="Pertahankan media {{ $index + 1 }}">
                                    <div class="opacity-35 peer-checked:opacity-100">
                                        @if (($item['type'] ?? '') === 'video')
                                            <video src="{{ url('storage/' . $item['path']) }}" preload="metadata" class="h-16 w-full object-cover"></video>
                                        @else
                                            <img src="{{ url('storage/' . $item['path']) }}" alt="Media {{ $index + 1 }}" class="h-16 w-full object-cover">
                                        @endif
                                    </div>
                                    <span class="absolute top-1 right-1 hidden size-4.5 place-items-center rounded-full bg-admin-smoke text-admin-base peer-checked:grid">
                                        <x-admin.icon name="check" class="size-3" />
                                    </span>
                                    <span class="block truncate px-1.5 py-1 text-center text-[11.5px] text-admin-muted peer-checked:hidden">
                                        <span class="text-admin-ember">akan dihapus</span>
                                    </span>
                                    <span class="hidden truncate px-1.5 py-1 text-center text-[11.5px] text-admin-muted peer-checked:block">
                                        {{ ($item['type'] ?? '') === 'video' ? 'Video' : 'Gambar' }} {{ $index + 1 }}
                                    </span>
                                    <span class="pointer-events-none absolute inset-0 rounded-md peer-focus-visible:outline-2 peer-focus-visible:-outline-offset-2 peer-focus-visible:outline-admin-focus"></span>
                                </label>
                            @endforeach
                        </div>
                        <p class="admin-hint mt-0 mb-3">Lepas centang pada media yang ingin dihapus saat disimpan.</p>
                    @endif

                    <input type="file" name="media_files[]" multiple aria-label="Tambah media galeri"
                           accept="image/jpeg,image/jpg,image/png,image/webp,video/mp4,video/quicktime,video/x-matroska,video/webm"
                           class="admin-input" x-on:change="updatePreviews($event)"
                           @error('media_files') aria-invalid="true" @enderror>
                    <p class="admin-hint">JPG, PNG, WEBP, MP4, MOV, MKV, atau WEBM, paling besar 100 MB per berkas.</p>

                    <div x-show="previews.length" x-cloak class="mt-3 grid grid-cols-3 gap-2">
                        <template x-for="(preview, idx) in previews" :key="idx">
                            <div class="overflow-hidden rounded-md border border-admin-line bg-admin-inset">
                                <template x-if="preview.type === 'video'">
                                    <video :src="preview.url" preload="metadata" class="h-16 w-full object-cover"></video>
                                </template>
                                <template x-if="preview.type !== 'video'">
                                    <img :src="preview.url" alt="" class="h-16 w-full object-cover">
                                </template>
                                <p x-text="preview.name" class="truncate px-1.5 py-1 text-center text-[11.5px] text-admin-muted"></p>
                            </div>
                        </template>
                    </div>

                    @error('media_files')
                        <p class="admin-error">{{ $message }}</p>
                    @enderror
                    @error('media_files.*')
                        <p class="admin-error">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                    function mediaGallery() {
                        return {
                            previews: [],
                            updatePreviews(event) {
                                this.previews = [];
                                Array.from(event.target.files).forEach(file => {
                                    this.previews.push({
                                        url: URL.createObjectURL(file),
                                        name: file.name,
                                        type: file.type.startsWith('video/') ? 'video' : 'image'
                                    });
                                });
                            }
                        };
                    }
                </script>
            </x-admin.card>
        </div>
    </form>
</div>

<x-admin.toast />
