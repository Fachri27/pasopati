@extends('layouts.app')

@section('content')
<div class="max-w-screen-xl mx-auto px-5 py-8 mt-20">
    <!-- Header Hasil Pencarian -->
    <div class="mb-8">
        @if($query)
            <h1 class="text-2xl md:text-3xl font-bold mb-2">
                Hasil Pencarian untuk: "{{ $query }}"
            </h1>
            <p class="text-gray-600">
                Ditemukan {{ ($fellowships->count() ?? 0) + ($pages->count() ?? 0) }} hasil
                @if(($fellowships->count() ?? 0) > 0 && ($pages->count() ?? 0) > 0)
                    ({{ $fellowships->count() }} fellowship, {{ $pages->count() }} artikel)
                @endif
            </p>
        @else
            <h1 class="text-2xl md:text-3xl font-bold mb-2">
                Pencarian
            </h1>
            <p class="text-gray-600">
                Masukkan kata kunci untuk mencari artikel dan fellowship
            </p>
        @endif
    </div>

    @if(($fellowships->count() ?? 0) > 0 || ($pages->count() ?? 0) > 0)
        {{-- Tampilan Fellowship (seperti home-fellowship) --}}
        @if(($fellowships->count() ?? 0) > 0)
            <div class="mb-12">
                <h2 class="text-xl font-bold mb-6 text-gray-800">Fellowship</h2>
            </div>
            <div class="max-w-screen-lg mx-auto md:px-0 px-5 mb-10">
                @foreach ($fellowships as $item)
                    @php
                        $translation = $item->translation($locale);
                        $dateValue = $item->start_date;
                    @endphp

                    <div class="md:flex gap-6 mb-10">
                        <!-- Gambar -->
                        <div class="bg-gray-200 md:w-[400px] w-full md:h-auto h-[200px] overflow-hidden flex-shrink-0">
                            @php
                                $image = $translation->image ?? $item->image ?? $translation->image_cover ?? null;
                            @endphp
                            @if ($image)
                                <img src="{{ asset('storage/' . $image) }}" alt="{{ $translation->title ?? '' }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500">
                                    No Image
                                </div>
                            @endif
                        </div>

                        {{-- teks --}}
                        <div class="">
                            @if($dateValue)
                                <p class="text-red-600 font-bold md:text-md font-sans">
                                    {{ \Carbon\Carbon::parse($dateValue)->format('Y') }}
                                </p>
                            @endif
                            
                            @if ($translation->sub_judul)
                                <h2 class="text-gray-800 font-semibold tracking-tight text-sm uppercase mt-2 font-sans">
                                    {{ $translation->sub_judul }}
                                </h2>
                            @endif
                            
                            <a href="{{ route('fellowship.preview', ['locale' => $locale, 'slug' => $item->slug]) }}">
                                <h1 class="text-xl md:text-xl font-extrabold leading-tight mt-2 font-sans">
                                    {{ $translation->title ?? '' }}
                                </h1>
                            </a>
                            
                            <div class="prose prose-sm mt-4 text-gray-600 text-sm font-open">
                                {!! $translation->excerpt ?? '' !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Tampilan Page (Card Grid) --}}
        @if(($pages->count() ?? 0) > 0)
            <div class="mb-12 mt-12">
                <h2 class="text-xl font-bold mb-6 text-gray-800">Artikel</h2>
            </div>
            <div class="max-w-screen-xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-y-20 px-5 auto-rows-fr">
                @foreach ($pages as $item)
                    @php
                        $translation = $item->translation($locale);
                        $dateValue = $item->published_at;
                    @endphp

                    <!-- WRAPPER ITEM -->
                    <div class="flex flex-col gap-2 h-full">
                        <!-- TANGGAL (DI LUAR CARD) -->
                        @if($dateValue)
                            <time class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($dateValue)->translatedFormat('d F Y') }}
                            </time>
                        @endif

                        <!-- CARD -->
                        <article class="group bg-[#e3061d] shadow-sm hover:shadow-xl flex flex-col h-full">
                            <!-- Gambar -->
                            <div class="overflow-hidden aspect-[16/9]">
                                @if ($item->featured_image)
                                    <img src="{{ asset('storage/' . $item->featured_image) }}"
                                        alt="{{ $translation->title ?? '' }}"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500">
                                        No Image
                                    </div>
                                @endif
                            </div>

                            <!-- Konten -->
                            <div class="p-4 flex flex-col flex-grow text-white">
                                <a href="{{ route('show-page', ['locale' => $locale, 'page_type' => $item->page_type, 'slug' => $item->slug]) }}">
                                    <h3 class="text-lg font-semibold mb-2 leading-snug font-sans">
                                        {{ $translation->title ?? '' }}
                                    </h3>
                                </a>

                                <div class="prose prose-sm text-white max-w-none opacity-90 mb-3 prose-p:leading-snug prose-p:my-1 font-open">
                                    {!! $translation->excerpt ?? '' !!}
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif

    @else
        <!-- Tidak Ada Hasil -->
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg mb-4">
                @if($query)
                    Maaf, tidak ada hasil yang ditemukan untuk "{{ $query }}"
                @else
                    Silakan masukkan kata kunci untuk mencari artikel dan fellowship
                @endif
            </p>
            <a href="{{ route('home', ['locale' => $locale]) }}" 
               class="inline-block px-6 py-3 bg-[#e3061d] text-white rounded hover:bg-[#c10518] transition">
                Kembali ke Beranda
            </a>
        </div>
    @endif
</div>
@endsection
