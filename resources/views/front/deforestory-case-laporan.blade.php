@extends('layouts.deforestory')

@section('content')

@php
    $locale = app()->getLocale();
    $isId = $locale === 'en' ? false : true;
    $t = $case->translation($locale) ?? $case->translation('id');
    // Judul kasus (breadcrumb) diambil dari kartu API bila ada; fallback CMS.
    $caseTitle = $apiTitle ?? ($t->title ?? '');

    $imageUrl = function ($path) {
        if (! $path) return '';
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    };

    $laporanTitle = $laporanTrans->title ?? '';
    $laporanExcerpt = $laporanTrans->excerpt ?? '';
    $laporanContent = $laporanTrans->content ?? '';
    $cover = $imageUrl($laporanTrans->image ?? $laporan->image ?? $case->featured_image);

    $caseUrl = route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]);
    $archiveUrl = route('deforestory', ['locale' => $locale]);
@endphp

<header class="pt-[calc(64px+2.5rem)] sm:pt-[calc(64px+4rem)] pb-8 sm:pb-12 text-center">
    <div class="max-w-[720px] mx-auto px-5">
        <h1 class="font-display font-bold text-[clamp(2rem,6vw,3.25rem)] leading-[1.1] tracking-[-.025em] max-w-[22ch] mx-auto">{{ $laporanTitle }}</h1>
        @if ($case->year || $laporan->published_at)
            <div class="mt-6 flex items-center justify-center flex-wrap gap-2 font-mono-ui text-[.72rem] tracking-[.05em] uppercase text-ink-3">
                @if ($laporan->published_at)
                    <span>{{ $laporan->published_at->translatedFormat('d M Y') }}</span>
                    <span class="text-line-d">·</span>
                @endif
                @if ($case->year)
                    <span>{{ $case->year }}</span>
                    <span class="text-line-d">·</span>
                @endif
                <span>Auriga Nusantara</span>
                <span class="text-line-d">·</span>
                <span>{{ $isId ? 'Investigasi' : 'Investigation' }}</span>
            </div>
        @endif
    </div>
</header>

@if ($cover)
    <figure class="max-w-[1200px] mx-auto px-5 mb-2">
        <img src="{{ $cover }}" alt="{{ $laporanTitle }}" class="w-full h-auto max-h-[520px] sm:max-h-[620px] object-cover">
    </figure>
@endif

<article class="max-w-[720px] mx-auto px-5 py-8 sm:py-10">
    @if ($laporanExcerpt)
        <p class="text-[1.1rem] leading-[1.7] text-ink-2 font-medium mb-8 pb-8 border-b border-line">{!! $laporanExcerpt !!}</p>
    @endif

    @if ($laporanContent)
        <div class="
            prose
            max-w-2xl mx-auto
            px-5
            poppins-regular

            md:text-md sm:text-base text-[16px]
            text-left

            prose-p:tracking-[0.020em]
            prose-p:my-[1em]

            prose-h2:text-[24px]
            prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

            prose-h3:text-[21px]
            prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
        ">
            {!! $laporanContent !!}
        </div>
    @else
        <p class="text-ink-3 text-center py-10">{{ $isId ? 'Laporan belum memiliki konten.' : 'Report has no content yet.' }}</p>
    @endif
</article>

<section class="bg-surface border-t border-line px-5 py-12 sm:py-16" aria-label="Kembali ke arsip kasus">
    <div class="max-w-[720px] mx-auto text-center">
        <a href="{{ $caseUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-forest hover:text-forest-d transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            {{ $isId ? 'Kembali ke arsip kasus' : 'Back to case archive' }}
        </a>
    </div>
</section>

<section class="bg-paper border-t border-line px-5 py-12 sm:py-16" aria-label="Komentar">
    <div class="max-w-[720px] mx-auto">
        <livewire:comment-section :commentable="$laporan" wire:key="comments-{{ $laporan->id }}" />
    </div>
</section>

@endsection
