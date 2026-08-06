@extends('layouts.deforestory')

@section('content')

@php
    $isId = $locale === 'en' ? false : true;
    $archiveUrl = route('deforestory', ['locale' => $locale]);

    $intro = $isId
        ? 'Meski deforestasi nasional cenderung menurun, termasuk di sektor pulp dan sawit, analisis spasial terkini menunjukkan situasi berbeda di konsesi-konsesi yang berada dalam radius rantai pasok grup Royal Golden Eagle.'
        : 'Even as national deforestation trends downward, including in the pulp and palm-oil sectors, recent spatial analysis shows a different picture in concessions within the supply-chain radius of the Royal Golden Eagle group.';

    $chapters = [
        [
            'slug' => 'chapter-1',
            'year' => '2021',
            'label' => $isId ? 'Bab 01 · Latar' : 'Chapter 01 · Context',
            'heading' => $isId ? 'Hutan Mayawana di radar rantai pasok' : 'Mayawana forest on the supply-chain radar',
            'headingLink' => $isId ? 'Hutan Mayawana di radar rantai pasok' : 'Mayawana forest on the supply-chain radar',
            'image' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=800&q=80',
            'imageAlt' => $isId ? 'Lanskap hutan Kalimantan yang dibuka menjadi perkebunan' : 'Kalimantan forest landscape cleared into plantation',
            'kicker' => 'DEFORESTORY INVESTIGATION',
            'body' => $isId
                ? '<p class="mb-4"><strong class="text-ink font-semibold">Mayawana</strong> adalah nama yang mulai muncul dalam dokumen-dokumen rantai pasok kayu tropis Indonesia. Daerah ini, yang sebelumnya masuk dalam peta tutupan hutan primer Kalimantan Barat, kini menunjukkan pola pembukaan lahan yang signifikan dalam kurun beberapa tahun terakhir.</p><p>Investigasi ini berangkat dari temuan citra satelit: adanya pembukaan lahan berpola geometris di sejumlah konsesi yang berada di dalam radius pengumpulan dan pengolahan milik pemasok yang diketahui mengirim bahan baku ke fasilitas grup RGE.</p>'
                : '<p class="mb-4"><strong class="text-ink font-semibold">Mayawana</strong> is a name that has begun to appear in Indonesia&rsquo;s tropical timber supply-chain documents. This area, previously part of West Kalimantan&rsquo;s primary forest cover map, now shows a significant pattern of land clearing over the past few years.</p><p>This investigation starts from satellite imagery: geometric land-clearing patterns in several concessions within the collection and processing radius of suppliers known to send raw material to RGE group facilities.</p>',
        ],
        [
            'slug' => 'chapter-2',
            'year' => '2022',
            'label' => $isId ? 'Bab 02 · Bukti' : 'Chapter 02 · Evidence',
            'heading' => $isId ? 'Angka di balik klaim keberlanjutan' : 'The numbers behind sustainability claims',
            'headingLink' => $isId ? 'Angka di balik klaim keberlanjutan' : 'The numbers behind sustainability claims',
            'image' => 'https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=800&q=80',
            'imageAlt' => $isId ? 'Visualisasi data deforestasi dari citra satelit' : 'Deforestation data visualization from satellite imagery',
            'kicker' => 'DEFORESTORY INVESTIGATION',
            'body' => $isId
                ? '<p class="mb-4">Menggunakan analisis <strong class="text-ink font-semibold">Global Forest Watch</strong> dan citra resolusi tinggi, tim menemukan bahwa pembukaan hutan primer di sekitar Mayawana terjadi bertahap namun konsisten. Polanya tidak seperti kebakaran akibat El Niño, melainkan pembukaan sistematis dengan jalan akses dan pematangan lahan.</p>'
                : '<p class="mb-4">Using <strong class="text-ink font-semibold">Global Forest Watch</strong> analysis and high-resolution imagery, the team found that primary-forest clearing around Mayawana has been gradual but steady. The pattern is not like El Niño-driven fire; it is systematic clearing with access roads and land maturation.</p>',
            'stats' => [
                ['value' => '±3.200 ha', 'label' => $isId ? 'Hutan dibuka' : 'Forest cleared'],
                ['value' => '2021–24', 'label' => $isId ? 'Periode' : 'Period'],
                ['value' => '5', 'label' => $isId ? 'Konsesi terkait' : 'Related concessions'],
            ],
        ],
        [
            'slug' => 'chapter-3',
            'year' => '2023',
            'label' => $isId ? 'Bab 03 · Pelaku' : 'Chapter 03 · Actors',
            'heading' => $isId ? 'Jejak pemasok yang sulit diputus' : 'The suppliers behind the trail',
            'headingLink' => $isId ? 'Jejak pemasok yang sulit diputus' : 'The suppliers behind the trail',
            'image' => 'https://images.unsplash.com/photo-1580137189272-c9379f8864fd?auto=format&fit=crop&w=800&q=80',
            'imageAlt' => $isId ? 'Area hutan yang telah dibuka untuk industri' : 'Forest area cleared for industry',
            'kicker' => 'DEFORESTORY INVESTIGATION',
            'body' => $isId
                ? '<p class="mb-4">Grup RGE tidak beroperasi langsung di Mayawana. Namun, setidaknya tiga pemasok bahan baku kayu yang diketahui mengirim ke pabrik pulp RGE memiliki konsesi di radius 50 kilometer dari area pembukaan hutan tersebut.</p><blockquote class="my-6 pl-5 border-l-[3px] border-pasopati font-display font-semibold text-[1.1rem] leading-[1.45] text-ink">&ldquo;Kami tidak memiliki hubungan dengan lokasi tersebut.&rdquo;<cite class="block mt-[.65rem] font-body font-medium text-[.78rem] not-italic text-ink-3">— ' . ($isId ? 'Pernyataan resmi RGE · diverifikasi terhadap data pemetaan pasok' : 'Official RGE statement · cross-checked against supply mapping data') . '</cite></blockquote>'
                : '<p class="mb-4">The RGE group does not operate directly in Mayawana. Yet at least three timber suppliers known to deliver to RGE pulp mills hold concessions within a 50-kilometre radius of the cleared area.</p><blockquote class="my-6 pl-5 border-l-[3px] border-pasopati font-display font-semibold text-[1.1rem] leading-[1.45] text-ink">&ldquo;We have no relationship with that location.&rdquo;<cite class="block mt-[.65rem] font-body font-medium text-[.78rem] not-italic text-ink-3">— ' . ($isId ? 'Pernyataan resmi RGE · diverifikasi terhadap data pemetaan pasok' : 'Official RGE statement · cross-checked against supply mapping data') . '</cite></blockquote>',
        ],
        [
            'slug' => 'chapter-4',
            'year' => '2024',
            'label' => $isId ? 'Bab 04 · Dampak' : 'Chapter 04 · Impact',
            'heading' => $isId ? 'Kerugian di luar peta' : 'Losses beyond the map',
            'headingLink' => $isId ? 'Kerugian di luar peta' : 'Losses beyond the map',
            'image' => 'https://images.unsplash.com/photo-1557050543-4d5f4e07ef46?auto=format&fit=crop&w=800&q=80',
            'imageAlt' => $isId ? 'Habitat satwa liar yang terancam akibat deforestasi' : 'Wildlife habitat threatened by deforestation',
            'kicker' => 'DEFORESTORY INVESTIGATION',
            'body' => $isId
                ? '<p class="mb-4">Deforestasi di Mayawana bukan sekadar hilangnya tutupan hijau. Area tersebut berada di dalam koridor penting yang menghubungkan beberapa blok hutan yang menjadi habitat orangutan Kalimantan dan satwa lainnya.</p><ul class="mt-4 p-0 list-none">' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Habitat satwa terfragmentasi dan koridor berpindah terputus.</li>' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Masyarakat lokal kehilangan akses ke sumber mata pencarian hutan.</li>' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Risiko kebakaran meningkat karena lahan gambut yang terbuka.</li></ul>'
                : '<p class="mb-4">Deforestation in Mayawana is not only about losing green cover. The area sits inside an important corridor linking several forest blocks that are habitat for Bornean orangutans and other wildlife.</p><ul class="mt-4 p-0 list-none">' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Wildlife habitat is fragmented and movement corridors are severed.</li>' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Local communities lose access to forest-based livelihoods.</li>' .
                  '<li class="relative pl-6 mb-2 text-[.95rem] leading-[1.65]"><span class="absolute left-0 top-[.55em] w-[7px] h-[7px] bg-pasopati"></span>Fire risk rises as open peatland is exposed.</li></ul>',
        ],
    ];
@endphp

{{-- HEADER --}}
<header class="pt-[calc(64px+2.5rem)] pb-8">
    <div class="max-w-[1200px] mx-auto px-5">
        <p class="font-mono-ui text-[.72rem] font-semibold uppercase tracking-[.18em] text-pasopati mb-3">
            <a href="{{ $archiveUrl }}" class="hover:text-pasopati-d transition-colors">Deforestory</a>
            <span class="mx-2 text-line-d">/</span>
            <span class="text-ink-2">{{ $isId ? 'Arsip Kasus' : 'Case Archive' }}</span>
        </p>
        <h1 class="font-display font-bold text-[clamp(1.9rem,5vw,2.8rem)] leading-[1.15] tracking-[-.015em] max-w-[28ch]">{{ $title }}</h1>
        <p class="mt-4 text-[1.05rem] leading-[1.7] text-ink-2 max-w-[66ch]">{{ $intro }}</p>
    </div>
</header>

{{-- MAIN --}}
<main class="max-w-[1200px] mx-auto px-5 pb-20 flex flex-col gap-10 md:gap-14">

    @foreach ($chapters as $chapter)
        <article class="flex flex-col md:flex-row gap-0 md:gap-8 pb-10 border-b border-line" id="{{ $chapter['slug'] }}">
            <div class="relative overflow-hidden bg-soft md:w-[400px] flex-shrink-0">
                <img src="{{ $chapter['image'] }}" alt="{{ $chapter['imageAlt'] }}" class="w-full h-[220px] md:h-full md:min-h-[280px] object-cover block transition-transform duration-500 hover:scale-[1.04]">
                <div class="absolute inset-0 flex flex-col justify-end p-5 bg-gradient-to-r from-black/75 via-black/50 to-transparent pointer-events-none">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <span class="relative z-10 font-mono-ui text-[.65rem] font-semibold uppercase tracking-[.14em] text-white/75 mb-1">{{ $chapter['label'] }}</span>
                    <h3 class="relative z-10 font-display font-bold text-[clamp(1.1rem,2.6vw,1.55rem)] leading-[1.25] text-white max-w-[24ch]">{{ $chapter['heading'] }}</h3>
                </div>
            </div>
            <div class="flex flex-col pt-5 md:pt-0 flex-1">
                <span class="font-display text-2xl font-bold text-pasopati leading-none mb-[.65rem]">{{ $chapter['year'] }}</span>
                <p class="font-body text-[.9rem] font-semibold uppercase tracking-[.04em] text-ink mb-2">{{ $chapter['kicker'] }}</p>
                <h2 class="font-display font-bold text-[clamp(1.25rem,3vw,1.65rem)] leading-[1.2] tracking-[-.01em] mb-[.9rem]">
                    <a href="#{{ $chapter['slug'] }}" class="hover:text-pasopati transition-colors">{{ $chapter['headingLink'] }}</a>
                </h2>
                <div class="text-base text-ink-2 leading-[1.75]">
                    {!! $chapter['body'] !!}
                    @if (! empty($chapter['stats']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 my-5 border border-line bg-surface overflow-hidden rounded-[2px]">
                            @foreach ($chapter['stats'] as $i => $stat)
                                <div class="p-4 {{ $i < count($chapter['stats']) - 1 ? 'border-b sm:border-b-0 sm:border-r border-line' : '' }}">
                                    <p class="font-mono-ui text-[.62rem] font-medium uppercase tracking-[.1em] text-ink-3 mb-[.4rem]">{{ $stat['label'] }}</p>
                                    <p class="font-mono-ui text-[1.4rem] font-semibold {{ $i === 0 ? 'text-pasopati' : 'text-ink' }} leading-none tracking-[-.02em]">{{ $stat['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @endforeach

    {{-- Card 05: CTA --}}
    <article class="flex flex-col md:flex-row bg-forest text-white rounded-[2px] overflow-hidden relative pl-[5px]" id="chapter-5">
        <div class="absolute left-0 top-0 bottom-0 w-[5px] bg-pasopati"></div>
        <div class="relative overflow-hidden bg-soft md:w-[320px] flex-shrink-0">
            <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80" alt="{{ $isId ? 'Lanskap hutan yang perlu dilindungi' : 'Forest landscape in need of protection' }}" class="w-full h-[220px] md:h-full md:min-h-[280px] object-cover block transition-transform duration-500 hover:scale-[1.04]">
            <div class="absolute inset-0 flex flex-col justify-end p-5 bg-gradient-to-t from-black/60 to-transparent pointer-events-none">
                <span class="relative z-10 font-mono-ui text-[.65rem] font-semibold uppercase tracking-[.14em] text-white/75 mb-1">{{ $isId ? 'Bab 05 · Tindak lanjut' : 'Chapter 05 · Follow-up' }}</span>
                <h3 class="relative z-10 font-display font-bold text-[clamp(1.1rem,2.6vw,1.55rem)] leading-[1.25] text-white max-w-[24ch]">{{ $isId ? 'Apa yang perlu dilakukan' : 'What needs to be done' }}</h3>
            </div>
        </div>
        <div class="flex flex-col p-7 md:p-10 md:pl-12 flex-1">
            <span class="font-display text-2xl font-bold text-white/90 leading-none mb-[.65rem]">2025</span>
            <p class="font-body text-[.9rem] font-semibold uppercase tracking-[.04em] text-white/75 mb-2">DEFORESTORY INVESTIGATION</p>
            <h2 class="font-display font-bold text-[clamp(1.25rem,3vw,1.65rem)] leading-[1.2] tracking-[-.01em] text-white mb-[.9rem]">{{ $isId ? 'Apa yang perlu dilakukan' : 'What needs to be done' }}</h2>
            <div class="text-base text-white/80 leading-[1.75]">
                <p class="mb-4">
                    {{ $isId
                        ? 'Kasus Mayawana menunjukkan bahwa komitmen nol deforestasi di atas kertas tidak cukup. Diperlukan transparansi rantai pasok, verifikasi spasial independen, dan tanggung jawab perusahaan terhadap pemasok tingkat ketiga.'
                        : 'The Mayawana case shows that zero-deforestation commitments on paper are not enough. What is needed is supply-chain transparency, independent spatial verification, and corporate accountability down to third-tier suppliers.' }}
                </p>
                <a href="#" class="inline-flex items-center gap-2 text-[.85rem] font-bold px-6 py-[.85rem] bg-pasopati text-white rounded-[2px] mt-5 hover:bg-pasopati-d hover:-translate-y-0.5 transition-all">
                    {{ $isId ? 'Baca laporan lengkap' : 'Read the full report' }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>
    </article>

</main>

{{-- BERLANGGANAN --}}
<section class="bg-paper border-t border-line px-5 py-16" aria-label="{{ $isId ? 'Berlangganan arsip' : 'Subscribe to archive' }}">
    <div class="max-w-[1200px] mx-auto grid md:grid-cols-[1fr_1.2fr] gap-10 items-center">
        <div>
            <p class="font-mono-ui text-[.7rem] font-semibold uppercase tracking-[.14em] text-pasopati mb-3">Update Arsip</p>
            <h2 class="font-display font-bold text-[clamp(1.35rem,3vw,1.85rem)] leading-[1.2] tracking-[-.01em] max-w-[26ch]">
                {{ $isId ? 'Terus terhubung dengan kasus-kasus ini.' : 'Stay connected to these cases.' }}
            </h2>
            <p class="text-[.95rem] text-ink-2 leading-[1.7] max-w-[52ch] mt-3">
                {{ $isId
                    ? 'Dapatkan pemberitahuan ketika laporan baru, data satelit, atau analisis tindak lanjut dari arsip Deforestory diterbitkan.'
                    : 'Get notified when new reports, satellite data, or follow-up analysis from the Deforestory archive are published.' }}
            </p>
        </div>
        <livewire:deforestory-subscribe :locale="$locale" variant="archive" />
    </div>
</section>

@endsection
