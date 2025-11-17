@extends('layouts.app')

@section('content')
<div class="max-w-screen-lg mx-auto px-4 py-8">

    <!-- Judul Section -->
    <h1 class="text-2xl font-bold uppercase text-red-600 mb-6 mt-20">Ngopini</h1>

    <!-- Grid Artikel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        @forelse($pages as $artikel)
        @php
        $translation = $artikel->translations->first();
        @endphp

        <div class="flex flex-col transition">
            <!-- Gambar Artikel -->
            <a href="{{ route('ngopini-show', $artikel->slug) }}">
                <img src="{{ asset('storage/' . $artikel->featured_image) }}"
                    alt="{{ $translation->title ?? $artikel->title }}" class="w-full h-[200px] object-cover">
            </a>

            <!-- Konten Artikel -->
            <div class="p-3">
                <a href="{{ route('ngopini-show', $artikel->slug) }}"
                    class="font-semibold text-base md:text-lg hover:text-red-600 transition block">
                    {{ $translation->title ?? $artikel->title }}
                </a>
                <p class="text-gray-700 text-sm mt-2 leading-relaxed">
                    {{ Str::limit(strip_tags($translation->excerpt ?? ''), 130, '...') }}
                </p>
                <div class="flex items-center justify-between mt-5">
                    @php
                        $locale = app()->getLocale();
                    @endphp
                    @if ($locale === 'id')
                        <a href=""
                            class="text-sm font-semibold text-green-600 hover:text-green-700 focus:outline-none focus:underline">
                            Baca selengkapnya →
                        </a>
                    @else
                        <a href=""
                            class="text-sm font-semibold text-green-600 hover:text-green-700 focus:outline-none focus:underline">
                            Read more →
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-500">Belum ada artikel Ngopini.</p>
        @endforelse
    </div>

</div>
@endsection