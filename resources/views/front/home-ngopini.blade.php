@extends('layouts.app')

@section('content')
<div class="max-w-screen-lg mx-auto px-4 py-8 font-sans">

    <!-- Judul Section -->
    <h1 class="text-2xl font-bold uppercase text-[#d50c2e] mb-6 mt-20">Ngopini</h1>

    <!-- Grid Artikel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        @forelse($pages as $artikel)
        @php
        $translation = $artikel->translations->first();
        @endphp

        <div class="flex flex-col transition">
            <!-- Gambar Artikel -->
            <a href="{{ route('ngopini-show', ['locale' => app()->getLocale(), 'slug' => $artikel->slug]) }}">
                <div class="w-full aspect-video overflow-hidden">
                    <img src="{{ asset('storage/' . $artikel->featured_image) }}"
                        alt="{{ $translation->title ?? $artikel->title }}" class="w-full h-full object-cover">
                </div>
            </a>


            <!-- Konten Artikel -->
            <div class="p-3">
                <a href="{{ route('ngopini-show', ['locale' => app()->getLocale(), 'slug' => $artikel->slug]) }}"
                    class="font-semibold text-base md:text-lg hover:text-red-600 transition block">
                    {{ $translation->title ?? $artikel->title }}
                </a>
                <p class="text-gray-700 text-sm mt-2 leading-relaxed">
                    {{ Str::limit(strip_tags($translation->excerpt ?? ''), 130, '...') }}
                </p>
            </div>
        </div>
        @empty
        <p class="text-gray-500">Belum ada artikel Ngopini.</p>
        @endforelse
    </div>

</div>
@endsection