@extends('layouts.deforestory')

@section('content')

@php
    $locale = app()->getLocale();
    $isId = $locale === 'en' ? false : true;
    $t = $case->translation($locale) ?? $case->translation('id');
    // Judul header diambil dari kartu API bila ada; fallback ke CMS.
    $title = $apiTitle ?? ($t->title ?? '');
    $intro = $t->excerpt ?? ($isId
        ? 'Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.'
        : 'Spatial analysis of deforestation in Mayawana and its links to the RGE group supply chain.');
    $archiveUrl = route('deforestory', ['locale' => $locale]);

    $imageUrl = function ($path) {
        if (! $path) return '';
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    };

    // Tahun kartu disintesis dari rentang tahun kasus (mis. "2021–2025").
    $yearDigits = [];
    preg_match_all('/\b(\d{4})\b/', $case->year ?? '', $yearDigits);
    $yearStart = (int) ($yearDigits[1][0] ?? substr($case->year ?? '', 0, 4));
    $yearEnd = (int) ($yearDigits[1][1] ?? $yearStart);
@endphp

{{-- HEADER --}}
<header class="pt-[calc(64px+2.5rem)] pb-8">
    <div class="max-w-screen-lg mx-auto md:px-0 px-5">
        <p class="font-mono-ui text-[.72rem] font-semibold uppercase tracking-[.18em] text-pasopati mb-3">
            <a href="{{ $archiveUrl }}" class="hover:text-pasopati-d transition-colors">Deforestory</a>
            <span class="mx-2 text-line-d">/</span>
            <span class="text-ink-2">{{ $isId ? 'Arsip Kasus' : 'Case Archive' }}</span>
        </p>
        <h1 class="font-display font-bold text-[clamp(1.9rem,5vw,2.8rem)] leading-[1.15] tracking-[-.015em] max-w-[28ch]">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-4 text-[1.05rem] leading-[1.7] text-ink-2 max-w-[66ch]">{{ $intro }}</p>
        @endif
        @if ($case->year)
            <div class="mt-4 flex items-center flex-wrap gap-2 font-mono-ui text-[.72rem] tracking-[.05em] uppercase text-ink-3">
                <span>{{ $case->year }}</span>
                <span class="text-line-d">·</span>
                <span>Auriga Nusantara</span>
            </div>
        @endif
    </div>
</header>

@if ($laporans->isNotEmpty())

    {{-- MAIN: daftar laporan (mengikuti pola halaman fellowship index —
         gambar kiri + teks kanan, judul menaut ke halaman detail) --}}
    <main class="max-w-screen-lg mx-auto md:px-0 px-5 pb-20">
        @foreach ($laporans as $index => $laporan)
            @php
                $lt = $laporan->translation($locale) ?? $laporan->translation('id');
                $lTitle = $lt->title ?? '';
                $lExcerpt = $lt->excerpt ?? '';
                $lImg = $imageUrl($laporan->image ?: $case->featured_image);
                $lHref = route('deforestory.case.laporan', [
                    'locale' => $locale,
                    'slug' => $case->slug,
                    'laporanSlug' => $laporan->slug,
                ]);
                $cardYear = $yearStart
                    ? (string) min($yearStart + $index, ($yearEnd ?: $yearStart))
                    : ($case->year ?: '');
            @endphp

            <div class="md:flex gap-6 mb-10">
                {{-- Gambar --}}
                <a href="{{ $lHref }}" class="bg-gray-200 md:w-[400px] w-full md:h-[300px] h-[200px] overflow-hidden flex-shrink-0 block">
                    @if ($lImg)
                        <img src="{{ $lImg }}" alt="{{ $lTitle }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-ink-3 text-sm">
                            {{ $isId ? 'Tanpa gambar' : 'No image' }}
                        </div>
                    @endif
                </a>

                {{-- Teks --}}
                <div class="md:pt-0 pt-4">
                    @if ($cardYear)
                        <p class="text-pasopati font-bold md:text-md font-sans">{{ $cardYear }}</p>
                    @endif
                    <h2 class="text-gray-800 font-semibold tracking-tight text-sm uppercase mt-2 font-sans">
                        {{ $isId ? 'Investigasi Deforestory' : 'Deforestory Investigation' }}
                    </h2>
                    <a href="{{ $lHref }}">
                        <h1 class="text-xl md:text-xl font-extrabold leading-tight mt-2 font-sans text-ink hover:text-pasopati transition-colors">
                            {{ $lTitle }}
                        </h1>
                    </a>
                    @if ($lExcerpt)
                        <div class="prose prose-sm mt-4 text-ink-2 text-sm">
                            {!! $lExcerpt !!}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </main>

@else
    <section class="max-w-screen-lg mx-auto px-5 py-16">
        <p class="font-mono-ui text-[.72rem] font-semibold uppercase tracking-[.14em] text-ink-3">
            {{ $isId ? 'Belum ada laporan' : 'No report yet' }}
        </p>
    </section>
@endif

{{-- BERLANGGANAN (per-kasus) --}}
<section class="bg-paper border-t border-line px-5 py-16" aria-label="{{ $isId ? 'Berlangganan kasus' : 'Subscribe to case' }}">
    <div class="max-w-[1200px] mx-auto grid md:grid-cols-[1fr_1.2fr] gap-10 items-center">
        <div>
            <p class="font-mono-ui text-[.7rem] font-semibold uppercase tracking-[.14em] text-pasopati mb-3">Update Kasus</p>
            <h2 class="font-display font-bold text-[clamp(1.35rem,3vw,1.85rem)] leading-[1.2] tracking-[-.01em] max-w-[26ch]">
                {{ $isId ? 'Terus terhubung dengan kasus ini.' : 'Stay connected to this case.' }}
            </h2>
            <p class="text-[.95rem] text-ink-2 leading-[1.7] max-w-[52ch] mt-3">
                {{ $isId
                    ? 'Dapatkan pemberitahuan ketika laporan baru, data satelit, atau analisis tindak lanjut untuk kasus ini diterbitkan.'
                    : 'Get notified when new reports, satellite data, or follow-up analysis for this case are published.' }}
            </p>
        </div>
        <livewire:deforestory-subscribe :locale="$locale" variant="case" :case-id="$case->id" />
    </div>
</section>

@endsection