@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-white shadow rounded-lg p-6">
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="{{ route('events.index') }}" class="hover:text-blue-600 font-medium">Event / Kejadian</a>
            <span class="text-gray-400">›</span>
            <span class="text-blue-600 font-semibold">{{ $event->title_id }}</span>
        </nav>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $event->title_id }}</h1>
            <div class="space-x-2">
                <a href="{{ route('events.edit', $event) }}"
                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
                <a href="{{ route('events.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">Kembali</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Gambar --}}
        @if ($event->image_id_url || $event->image_en_url)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @if ($event->image_id_url)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gambar Indonesia</span>
                        <img src="{{ $event->image_id_url }}" alt="{{ $event->title_id }}"
                             class="mt-2 w-full rounded-lg border border-gray-200 object-cover" style="aspect-ratio: {{ $event->orientation->aspectRatio() }}">
                    </div>
                @endif
                @if ($event->image_en_url)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gambar English</span>
                        <img src="{{ $event->image_en_url }}" alt="{{ $event->title_en }}"
                             class="mt-2 w-full rounded-lg border border-gray-200 object-cover" style="aspect-ratio: {{ $event->orientation->aspectRatio() }}">
                    </div>
                @endif
            </div>
        @endif

        {{-- Video --}}
        @if ($event->has_video)
            <div class="mb-8">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Video</span>
                <video controls preload="metadata" class="mt-2 w-full rounded-lg border border-gray-200 bg-black"
                       style="aspect-ratio: {{ $event->orientation->aspectRatio() }}">
                    <source src="{{ $event->video_url }}">
                    Browser Anda tidak mendukung tag video.
                </video>
            </div>
        @endif

        {{-- Detail --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Title (Indonesia)</span>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $event->title_id }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Title (English)</span>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $event->title_en }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Kejadian</span>
                    <p class="mt-1 font-medium text-gray-900">{{ $event->event_date_display }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Lokasi</span>
                    <p class="mt-1 font-medium text-gray-900">{{ $event->location }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Koordinat</span>
                    <p class="mt-1 font-medium text-gray-900">{{ $event->coordinate_display }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Orientation</span>
                    <p class="mt-1">
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $event->orientation->label() }}
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Peta Lokasi</span>
                <div id="event-detail-map" class="mt-2 h-96 w-full rounded-lg border border-gray-200 z-0"
                     data-lat="{{ $event->location_lat }}" data-lng="{{ $event->location_lng }}"
                     data-location="{{ $event->location }}" data-geojson='{{ json_encode($event->location_geojson) }}'></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/event.js')
@endpush
