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
                <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->slug }}"
                    class="w-full h-auto object-contain shadow">
                @endif
            </div>

            <!-- ARTIKEL -->
            {{-- <div class="
               prose prose-base
                max-w-3xl mx-auto
                px-5 font-open
                md:text-left text-justify

                prose-p:text-slate-800
                prose-p:leading-[2]
                prose-p:my-6

                prose-h2:mt-6 prose-h2:mb-3 prose-h2:font-semibold
                prose-h3:mt-5 prose-h3:mb-3 prose-h3:font-semibold
                prose-h4:mt-6 prose-h4:mb-3 prose-h4:font-medium

            ">
                {!! $translation->content !!}
            </div> --}}
            <div class="
                prose
                max-w-2xl mx-auto
                px-5 poppins-regular

                md:text-md sm:text-base text-sm
                text-left

                prose-p:text-[#1a1a1a]
                prose-p:leading-[1.6] md:prose-p:leading-[1.6]
                prose-p:tracking-[0.020em]
                prose-p:my-[1em]

                prose-h2:text-[24px]
                prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

                prose-h3:text-[21px]
                prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
                ">
                {!! $translation->content !!}
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
                    <img src="{{ asset('storage/' . $item->featured_image) }}" alt="{{ $page->slug }}"
                        class="w-full h-auto object-contain shadow mb-3">
                </a>

                <h3 class="font-semibold text-lg leading-snug mb-3 font-sans">
                    <a href="{{ route('ngopini-show', $item->slug) }}" class="hover:text-red-600">
                        {{ $title }}
                    </a>
                </h3>

                <div class="text-sm text-gray-700 leading-relaxed font-sans">
                    {{ $excerpt }}
                </div>
            </div>

            @endforeach

        </div>

    </div>
</div>
@endsection