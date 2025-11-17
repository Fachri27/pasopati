@extends('layouts.app')

@section('content')
<div class="max-w-screen-xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6 px-5 mt-25">
    @foreach ($pages as $page)
    <article class="group bg-red-700 overflow-hidden shadow-sm hover:shadow-xl rounded-md flex flex-col">

        <!-- Gambar -->
        <div class="overflow-hidden h-40 sm:h-48 md:h-52 lg:h-56 transition-transform transform group-hover:scale-105">
            @if ($page->featured_image)
            <img src="{{ asset('storage/' . $page->featured_image) }}"
                alt="{{ $page->translations->first()->title ?? '' }}" class="w-full h-full object-cover">
            @else
            <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500">
                No Image
            </div>
            @endif
        </div>

        <!-- Konten -->
        <div class="p-4 flex flex-col flex-grow text-white">
            <time class="text-xs opacity-80 mb-2">
                {{ \Carbon\Carbon::parse($page->published_at)->translatedFormat('d F Y') }}
            </time>

            <a href="{{ route('show-page', ['page_type'=> $page->page_type, 'slug' => $page->slug]) }}">
                <h3 class="text-lg font-semibold mb-2 line-clamp-2">
                    {{ $page->translations->first()->title ?? '' }}
                </h3>
            </a>

            <div class="prose prose-sm text-white max-w-none line-clamp-3 opacity-90 mb-3">
                {!! $page->translations->first()->excerpt ?? '' !!}
            </div>

            <div class="mt-auto">
                <a href="{{ route('show-page', ['page_type'=> $page->page_type, 'slug' => $page->slug]) }}"
                    class="text-sm font-semibold text-green-300 hover:text-green-500 transition">
                    Baca selengkapnya →
                </a>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endsection