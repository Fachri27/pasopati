@extends('layouts.admin')

@section('content')
    @php
        $rasio = $event->orientation->aspectRatio();
        $gallery = $event->media_items;
        // Galeri baru layak tampil kalau isinya lebih dari media utama yang
        // sudah ditampilkan sendiri di atasnya. Satu item saja berarti item
        // itu media utamanya sendiri (lihat Event::getMediaItemsAttribute),
        // jadi menampilkannya lagi hanya menggandakan video di atas.
        $adaGaleri = count($gallery) > 1;
        $adaMedia = $event->image_id_url || $event->image_en_url || $event->has_video || $adaGaleri;
    @endphp

    <x-admin.page-header :title="$event->title_id"
                         :crumbs="[['Kejadian', route('events.index')]]"
                         :back="route('events.index')">
        <a href="{{ route('events.edit', $event) }}" class="admin-btn admin-btn--ghost admin-btn--sm">
            <x-admin.icon name="edit" class="size-3.5" />
            Ubah
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div class="grid min-w-0 gap-5">
            <x-admin.card title="Media">
                @if ($adaMedia)
                    <div class="grid gap-5">
                        @if ($event->image_id_url || $event->image_en_url)
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if ($event->image_id_url)
                                    <figure>
                                        <figcaption class="admin-eyebrow mb-1.5">Gambar Indonesia</figcaption>
                                        <img src="{{ $event->image_id_url }}" alt="{{ $event->title_id }}"
                                             class="w-full rounded-md border border-admin-line object-cover" style="aspect-ratio: {{ $rasio }}">
                                    </figure>
                                @endif
                                @if ($event->image_en_url)
                                    <figure>
                                        <figcaption class="admin-eyebrow mb-1.5">Gambar English</figcaption>
                                        <img src="{{ $event->image_en_url }}" alt="{{ $event->title_en }}"
                                             class="w-full rounded-md border border-admin-line object-cover" style="aspect-ratio: {{ $rasio }}">
                                    </figure>
                                @endif
                            </div>
                        @endif

                        @if ($event->has_video)
                            <div>
                                <p class="admin-eyebrow mb-1.5">Video</p>
                                <video controls preload="metadata" class="w-full rounded-md border border-admin-line bg-black"
                                       style="aspect-ratio: {{ $rasio }}">
                                    <source src="{{ $event->video_url }}">
                                    Browser Anda tidak mendukung tag video.
                                </video>
                            </div>
                        @endif

                        @if ($adaGaleri)
                            <div>
                                <p class="admin-eyebrow mb-1.5">Galeri · <span class="admin-num">{{ count($gallery) }}</span></p>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($gallery as $item)
                                        @if ($item['type'] === 'video')
                                            <video controls preload="metadata" class="w-full rounded-md border border-admin-line bg-black" style="aspect-ratio: {{ $rasio }}">
                                                <source src="{{ $item['url'] }}">
                                            </video>
                                        @else
                                            <img src="{{ $item['url'] }}" alt="{{ $event->title_id }}" loading="lazy"
                                                 class="w-full rounded-md border border-admin-line object-cover" style="aspect-ratio: {{ $rasio }}">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-[13.5px] text-admin-muted">Kejadian ini belum punya gambar atau video.</p>
                @endif
            </x-admin.card>

            <x-admin.card title="Peta lokasi">
                {{-- isolate + z-0: lapisan Leaflet memakai z-index sampai 1000;
                     tanpa konteks tumpukan sendiri petanya naik menimpa bilah
                     atas halaman yang menempel. --}}
                <div id="event-detail-map" class="isolate z-0 h-96 w-full rounded-md border border-admin-line"
                     data-lat="{{ $event->location_lat }}" data-lng="{{ $event->location_lng }}"
                     data-location="{{ $event->location }}" data-geojson='{{ json_encode($event->location_geojson) }}'></div>
            </x-admin.card>
        </div>

        {{-- top-[68px]: berhenti tepat di bawah bilah atas halaman (48px).
             Di layar sempit rincian naik ke atas media: tanggal dan lokasi
             lebih dulu dicari daripada gambarnya. Judul Indonesia tidak
             diulang di sini — ia sudah menjadi judul halaman. --}}
        <div class="max-lg:order-first lg:sticky lg:top-[68px]">
            <x-admin.card title="Rincian">
                <dl class="grid gap-3.5 text-[13.5px]">
                    <div>
                        <dt class="admin-eyebrow">Judul (English)</dt>
                        <dd class="mt-0.5">{{ $event->title_en }}</dd>
                    </div>
                    <div>
                        <dt class="admin-eyebrow">Tanggal kejadian</dt>
                        <dd class="mt-0.5">{{ $event->event_date_display }}</dd>
                    </div>
                    <div>
                        <dt class="admin-eyebrow">Lokasi</dt>
                        <dd class="mt-0.5">{{ $event->location }}</dd>
                    </div>
                    <div>
                        <dt class="admin-eyebrow">Koordinat</dt>
                        <dd class="admin-num mt-0.5">{{ $event->coordinate_display }}</dd>
                    </div>
                    <div>
                        <dt class="admin-eyebrow">Orientasi</dt>
                        <dd class="mt-0.5">{{ $event->orientation->label() }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>
    </div>

    <x-admin.toast />
@endsection

@push('scripts')
    @vite('resources/js/event.js')
@endpush
