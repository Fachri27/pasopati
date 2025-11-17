@extends('layouts.app')

@section('content')
@php
$locale = request()->segment(1);
$translation = $page->translations->where('locale', $locale)->first();
@endphp

<div class="max-w-screen-lg mx-auto px-4 py-10 md:mt-20 mt-10">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

        <!-- KONTEN UTAMA -->
        <div class="md:col-span-2">

            <!-- GAMBAR -->
            <div class="w-full">
                @if ($page->featured_image)
                <img 
                    src="{{ asset('storage/' . $page->featured_image) }}" 
                    alt="{{ $page->slug }}"
                    class="w-full h-auto object-contain shadow"
                >
                @endif
            </div>

            <!-- ARTIKEL -->
            <div class="mt-8">
                <div class="prose prose-md max-w-none  prose-headings:font-semibold leading-relaxed md:text-left text-justify">
                    {!! $translation->content !!}
                </div>
            </div>

        </div>

        <!-- SIDEBAR -->
        <div class="space-y-10">

            @foreach ($related as $item)

            @php
                $trans = $item->translations->where('locale', $locale)->first();
                $thumbnail = $item->featured_image 
                    ? asset('storage/' . $item->featured_image) 
                    : '/images/default.jpg';

                $title = $trans->title ?? $item->title;
                $excerpt = Str::limit(strip_tags($trans->content ?? ''), 130);
            @endphp

            <div class="w-full">
                <a href="{{ url($locale . '/' . $item->slug) }}">
                    <img 
                    src="{{ asset('storage/' . $item->featured_image) }}" 
                    alt="{{ $page->slug }}"
                    class="w-full h-auto object-contain shadow mb-3"
                >
                </a>

                <h3 class="font-semibold text-lg leading-snug mb-3">
                    <a href="{{ route('ngopini-show', $item->slug) }}" class="hover:text-red-600">
                        {{ $title }}
                    </a>
                </h3>

                <div class="text-sm text-gray-700 leading-relaxed">
                    {{ $excerpt }}
                </div>
            </div>

            @endforeach

        </div>

    </div>
</div>
@endsection
