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

    // View ini dipakai dua jalur: halaman publik dan pratinjau editor. Semua
    // yang berbeda di antara keduanya disaring lewat $preview.
    $preview = $preview ?? false;
    // Semua tautan lanjutan di dalam pratinjau dibuat lewat helper ini: bagi
    // peninjau luar, tanda tangannya harus ikut terbawa ke halaman berikutnya.
    $tautan = app(\App\Support\DeforestoryPreviewLink::class);

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
                $lImg = $imageUrl($laporan->translation($locale)?->image ?? $laporan->image ?? $case->featured_image);
                $lHref = $preview
                    ? $tautan->laporan($case->slug, $laporan->slug, $locale)
                    : route('deforestory.case.laporan', [
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
                    {{-- Penanda status disandingkan dengan tahun, bukan ditumpuk
                         di atasnya: ia keterangan tentang kartu ini, sederajat
                         dengan tahun, dan sebaris membuat kartunya tidak
                         kehilangan satu baris penuh. Kalimat penjelasnya pindah
                         ke title — di daftar yang panjang, satu kata status
                         sudah cukup membedakan. --}}
                    @if ($cardYear || ($preview && $laporan->status !== 'active'))
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            @if ($cardYear)
                                <span class="text-pasopati font-bold md:text-md font-sans">{{ $cardYear }}</span>
                            @endif
                            @if ($preview && $laporan->status !== 'active')
                                <span class="inline-flex items-center gap-1 border border-amber-300 bg-amber-50 px-1.5 py-[.15rem] font-mono-ui text-[.6rem] font-semibold uppercase tracking-[.1em] text-amber-800"
                                      title="{{ $laporan->status }} — belum tampil publik">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                    {{ $laporan->status }}
                                </span>
                            @endif
                        </div>
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

{{-- BERLANGGANAN (per-kasus) — formnya sungguhan, jadi tidak dipasang
     di pratinjau supaya editor tidak sengaja mendaftarkan diri. --}}
@unless ($preview)
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
@endunless

@if ($preview)
    <x-preview-bar
        label="Pratinjau arsip"
        :status="$case->status"
        :locale="$locale"
        :id-url="$tautan->kasus($case->slug, 'id')"
        :en-url="$tautan->kasus($case->slug, 'en')"
        :cms-url="auth()->check() ? route('deforestory.laporan.index', ['caseSlug' => $case->slug]) : null"
    />
@endif

@endsection