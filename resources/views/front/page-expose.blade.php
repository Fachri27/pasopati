@extends('layouts.app')

@section('content')

@php
$locale = request()->segment(1);
$translation = $page->translations->where('locale', $locale)->first();
@endphp

<div>
    {{-- ============================= --}}
    {{-- BAGIAN PARALLAX HEADER --}}
    {{-- ============================= --}}
    @if ($page->type === 'parallax')
        <section 
            x-data 
            x-init="window.addEventListener('scroll', () => {
                $refs.parallaxImg.style.transform = `translateY(${window.scrollY * 0.3}px)`
            })"
            class="relative overflow-hidden h-[250px] sm:h-[350px] md:h-[500px] lg:h-[750px] mb-5 md:mb-10 lg:mb-15"
        >
            <img 
                x-ref="parallaxImg"
                src="{{ asset('storage/' . $page->featured_image) }}"
                alt="{{ $translation->title }}"
                class="absolute top-0 left-0 w-full h-full object-cover md:object-center transition-transform duration-300 ease-out"
            >

            {{-- overlay hitam transparan --}}
            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
            </div>
        </section>
        <h1 class="max-w-4xl mx-auto text-black text-xl sm:text-xl md:text-3xl lg:text-4xl font-serif font-semibold text-center px-4 leading-snug mb-10 md:mb-20 lg:mb-20">
            {!! nl2br(e($translation->title)) !!}
        </h1>

    @else
        {{-- ============================= --}}
        {{-- BAGIAN DEFAULT HEADER --}}
        {{-- ============================= --}}
        <div class="max-w-4xl mx-auto text-center my-10 sm:my-16 md:my-20 px-5">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-semibold mb-4 font-serif">
                {{ $translation->title ?? $page->slug }}
            </h1>

            @if ($page->featured_image)
                <img 
                    src="{{ asset('storage/' . $page->featured_image) }}" 
                    alt="{{ $page->slug }}"
                    class="w-full max-h-[400px] object-cover rounded-lg shadow mx-auto"
                >
            @endif
        </div>
    @endif
</div>

{{-- ============================= --}}
{{-- KONTEN UTAMA --}}
{{-- ============================= --}}
<div class="prose prose-sm sm:prose-base md:prose-lg lg:prose-base prose-black max-w-3xl mx-auto text-justify md:text-left px-5 leading-relaxed font-serif">
    {!! $translation->content !!}
</div>

@include('front.components.otherArtikel')
@include('front.components.floating')

@endsection
