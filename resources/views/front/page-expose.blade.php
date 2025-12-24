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
    <section x-data="{ offset: 0 }" x-init="
        window.addEventListener('scroll', () => {
            if (window.innerWidth >= 768) {
                offset = window.scrollY * 0.3
            }
        })
    " class="relative min-h-[30vh] md:min-h-[70vh] overflow-hidden md:mb-20 mb-10">
        <!-- Background Image -->
        <div class="absolute inset-0 w-full h-full bg-center bg-cover"
            :style="`transform: translateY(${offset}px); background-image: url('{{ asset('storage/' . $page->featured_image) }}')`">
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50"></div>
    </section>

    <h1
        class="max-w-4xl mx-auto text-black text-xl sm:text-xl md:text-3xl lg:text-4xl font-sans font-semibold text-center px-4 leading-snug mb-10 md:mb-20 lg:mb-20">
        {!! nl2br(e($translation->title)) !!}
    </h1>

    @else
    {{-- ============================= --}}
    {{-- BAGIAN DEFAULT HEADER --}}
    {{-- ============================= --}}
    <div class="max-w-4xl mx-auto text-center my-10 sm:my-16 md:my-20 px-5">
        <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-base mb-7 font-sans pt-15">
            {{ $translation->title ?? $page->slug }}
        </div>
        <p class="text-base sm:text-lg md:text-xl font-light font-sans mb-8 leading-relaxed">
            {!! $translation->excerpt !!}
        </p>

        @if ($page->featured_image)
        <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->slug }}"
            class="w-full max-h-[400px] object-cover shadow mx-auto mt-5">
        @endif
    </div>
    @endif
</div>

{{-- ============================= --}}
{{-- KONTEN UTAMA --}}
{{-- ============================= --}}
<div class="
  prose prose-base
  max-w-2xl mx-auto
  px-5 font-open
  text-justify md:text-left

  prose-p:text-slate-800
  prose-p:leading-[2]
  prose-p:my-6

  prose-h2:text-[18px] md:prose-h2:text-[22px]
  prose-h2:mt-6 prose-h2:mb-3 prose-h2:font-semibold

  prose-h3:text-[16px] md:prose-h3:text-[20px]
  prose-h3:mt-5 prose-h3:mb-3 prose-h3:font-semibold

  prose-h4:text-[15px] md:prose-h4:text-[18px]
  prose-h4:mt-6 prose-h4:mb-3 prose-h4:font-medium
">

    {!! $translation->content !!}
</div>






@include('front.components.otherArtikel')
@include('front.components.floating')

@endsection