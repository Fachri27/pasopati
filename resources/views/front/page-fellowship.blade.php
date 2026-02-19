@extends('layouts.app')

@section('content')

{{-- ======================================================
|  HERO IMAGE (FROM TRANSLATION)
====================================================== --}}
@php
    $translation = $fellowship->translations->firstWhere('locale', $locale);

    $img = $translation->image_cover;
@endphp

<section class="flex justify-center md:mt-30 mt-15">
    <div class="bg-gray-100 w-full max-w-screen-lg mx-auto">

        @if($img)
            <img
                src="{{ asset('storage/' . $img) }}"
                alt="poster fellowship"
                class="w-full h-auto object-cover"
            >
        @endif

    </div>
</section>



{{-- ======================================================
|  CONTENT LAYOUT
====================================================== --}}
<div
    x-data="{ selectedId: {{ $fellowship->kategoris->first() ? (int)$fellowship->kategoris->first()->id : 'null' }} }"
    class="md:flex max-w-screen-lg mx-auto md:mt-10 font-sans"
>

    {{-- ================= SIDEBAR ================= --}}
    <aside class="md:w-64 w-full bg-white md:sticky md:top-0 md:self-start md:p-4 pt-5 px-3">

        <nav class="flex flex-col space-y-1">

            @foreach ($fellowship->kategoris as $category)
                @php
                    $catTrans = $category->translations->firstWhere('locale', $locale)
                        ?? $category->translations->first();
                @endphp

                <button
                    @click="selectedId = {{ (int)$category->id }}"
                    :class="selectedId === {{ (int)$category->id }}
                        ? 'bg-red-600 text-white font-bold'
                        : 'text-red-600 hover:bg-red-100 font-semibold'"
                    class="px-3 py-2 text-left rounded transition"
                >
                    {{ $catTrans->kategori_name ?? 'No Title' }}
                </button>

            @endforeach

        </nav>
    </aside>



    {{-- ================= CONTENT ================= --}}
    <main class="flex-1 px-5 py-3 md:py-2 text-sm">

        @foreach ($fellowship->kategoris as $category)

            @php
                $contentId = $category->pivot->content_id ?? '';
                $contentEn = $category->pivot->content_en ?? '';

                $content = $locale === 'id'
                    ? $contentId
                    : ($contentEn ?: $contentId);
            @endphp

            <div
                x-show="selectedId === {{ (int)$category->id }}"
                style="display:none"
            >

                <div class="prose
                    max-w-2xl mx-auto
                    px-5
                    poppins-regular
                    md:text-md sm:text-base text-sm
                    prose-p:leading-relaxed
                    prose-p:tracking-[0.020em]
                    prose-h2:text-[24px]
                    prose-h3:text-[21px]">

                    {!! $content !!}

                </div>

            </div>

        @endforeach

    </main>

</div>


@include('front.components.floating')

@endsection
