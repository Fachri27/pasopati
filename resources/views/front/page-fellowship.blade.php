@extends('layouts.app')
@section('content')
<section class="flex justify-center md:mt-30 mt-15">
    <div class="bg-gray-100 w-full max-w-screen-lg mx-auto">
        <img src="{{ asset('storage/' . $fellowship->image) }}" alt="poster 1" class="w-full h-auto object-cover">
    </div>
</section>
<div x-data="{ selectedId: {{ $categories->first()->id ?? 'null' }} }"
    class="md:flex max-w-screen-lg mx-auto md:mt-10 font-sans">

    <!-- Sidebar -->
    <aside class="md:w-64 w-full bg-white md:sticky md:top-0  md:self-start md:p-4 pt-5 px-3">
        <nav class="flex flex-col space-y-1">
            @foreach ($categories as $category)
            @php
            $catTrans = $category->translations->first();
            @endphp
            <button @click="selectedId = {{ $category->id }}" :class="selectedId === {{ $category->id }} 
                        ? 'bg-red-600 text-white font-bold' 
                        : 'text-red-600 hover:bg-red-100 font-semibold'" class="px-3 py-2 text-left">
                {{ $catTrans->kategori_name ?? 'No Title' }}
            </button>
            @endforeach
        </nav>
    </aside>

    <!-- Content -->
    <main class="flex-1 px-5 py-5 md:py-2 text-sm">
        @foreach ($categories as $category)
        @php
        $catTrans = $category->translations->first();
        @endphp
        <div x-show="selectedId === {{ $category->id }}" style="display: none !important">
            {{-- Konten dari pivot sesuai bahasa --}}
            @if($locale === 'id')
            <div class="prose prose-base
    max-w-3xl mx-auto
    px-5 font-open
    md:text-left text-justify

    prose-p:text-slate-800
    prose-p:leading-[2]
    prose-p:my-6

    prose-h2:mt-6 prose-h2:mb-3 prose-h2:font-semibold
    prose-h3:mt-5 prose-h3:mb-3 prose-h3:font-semibold
    prose-h4:mt-6 prose-h4:mb-3 prose-h4:font-medium">
                {!! $category->pivot->content_id !!}
            </div>

            @else
            <div class="
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
            ">{!!
                $category->pivot->content_en !!}</div>
            @endif
        </div>
        @endforeach
    </main>
</div>

@include('front.components.floating')

@endsection