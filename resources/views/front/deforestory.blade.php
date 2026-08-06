@extends('layouts.app')

@section('content')

@php
    $locale = app()->getLocale();

    // Daftar kartu diambil dari API eksternal (mock). Tiap item berupa array:
    // {slug, title, excerpt, image, category, year}. Konten arsip/laporan
    // tetap di CMS lokal, di-match via slug.
    $imageUrl = function ($path) {
        if (! $path) return '';
        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    };
@endphp

{{-- Hero --}}
<header class="relative bg-paper text-ink overflow-hidden border-b border-line hero-glow">
    <div class="relative max-w-[1200px] mx-auto px-5 text-center pt-[calc(64px+4rem)] pb-[4.5rem]">
        <h1 class="font-display font-bold text-[clamp(2.6rem,8vw,5rem)] leading-[1.05] tracking-[-.025em] max-w-[16ch] mx-auto mb-5 text-pasopati">
            Defore<span class="text-forest not-italic">story</span>
        </h1>
        <p class="text-[clamp(1.05rem,2vw,1.25rem)] leading-relaxed text-ink-2 max-w-[62ch] mx-auto mb-8">
            Kisah deforestasi, konflik lahan, dan kerusakan ekosistem Indonesia disusun dari citra satelit dan catatan lapangan menjadi arsip yang dapat dibandingkan, ditelusuri, dan diingat kembali.
        </p>
    </div>
</header>

<main class="max-w-[1200px] mx-auto px-5 pt-12 pb-20 md:pt-16">
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-6 lg:gap-8" id="card-list">

        @forelse ($cases as $case)
            @php
                $title = $case['title'] ?? '';
                $excerpt = $case['excerpt'] ?? '';
                $img = $imageUrl($case['image'] ?? null);
                $href = route('deforestory.case', ['locale' => $locale, 'slug' => $case['slug'] ?? '#']);
                $cat = $case['category'] ?? 'deforestasi';
                $year = $case['year'] ?? '';
            @endphp
            <article
                class="card flex flex-col h-full overflow-hidden rounded-[2px] shadow-[0_6px_24px_rgba(0,0,0,.10)] hover:shadow-[0_14px_40px_rgba(0,0,0,.16)] hover:-translate-y-[3px] focus-within:shadow-[0_14px_40px_rgba(0,0,0,.16)] focus-within:-translate-y-[3px] transition-all duration-250"
                data-category="{{ $cat }}"
                data-cause="{{ $cat }}"
                data-year="{{ $year }}"
            >
                <a href="{{ $href }}" class="group flex flex-col h-full" aria-label="Baca: {{ $title }}">
                    <div class="relative overflow-hidden bg-soft">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $title }}" class="w-full h-[220px] lg:h-[200px] object-cover block transition-transform duration-500 group-hover:scale-[1.04]">
                        @else
                            <div class="w-full h-[220px] lg:h-[200px] bg-soft flex items-center justify-center text-ink-3 text-sm">Tanpa gambar</div>
                        @endif
                    </div>
                    <div class="flex-1 flex flex-col bg-pasopati text-white p-[1.15rem_1.25rem_1.35rem]">
                        @if ($year)
                            <span class="font-mono-ui text-[.62rem] font-semibold uppercase tracking-[.1em] text-white/70 mb-1">{{ $year }}</span>
                        @endif
                        <h2 class="font-display font-bold text-[clamp(1.05rem,2.5vw,1.35rem)] leading-[1.25] mb-[.55rem]">{{ $title }}</h2>
                        <p class="flex-1 text-[.9rem] leading-[1.55] text-white/90 line-clamp-4">{{ $excerpt }}</p>
                    </div>
                </a>
            </article>
        @empty
            <p class="text-ink-3 col-span-full text-center py-16">
                {{ $locale === 'en' ? 'No cases available yet.' : 'Belum ada kasus tersedia.' }}
            </p>
        @endforelse

    </div>
</main>

{{-- BERLANGGANAN (semua kasus) --}}
<section class="bg-paper border-t border-line px-5 py-16" aria-label="{{ $locale === 'en' ? 'Subscribe to Deforestory archive' : 'Berlangganan arsip Deforestory' }}">
    <div class="max-w-[1200px] mx-auto grid md:grid-cols-[1fr_1.2fr] gap-10 items-center">
        <div>
            <p class="font-mono-ui text-[.7rem] font-semibold uppercase tracking-[.14em] text-pasopati mb-3">Update Arsip</p>
            <h2 class="font-display font-bold text-[clamp(1.35rem,3vw,1.85rem)] leading-[1.2] tracking-[-.01em] max-w-[26ch]">
                {{ $locale === 'en' ? 'Stay connected to the Deforestory archive.' : 'Terus terhubung dengan arsip Deforestory.' }}
            </h2>
            <p class="text-[.95rem] text-ink-2 leading-[1.7] max-w-[52ch] mt-3">
                {{ $locale === 'en'
                    ? 'Get notified when new reports, satellite data, or follow-up analysis from the Deforestory archive are published.'
                    : 'Dapatkan pemberitahuan ketika laporan baru, data satelit, atau analisis tindak lanjut dari arsip Deforestory diterbitkan.' }}
            </p>
        </div>
        <livewire:deforestory-subscribe :locale="$locale" variant="archive" />
    </div>
</section>

@endsection
