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
            <div class="prose
      max-w-2xl mx-auto
      px-5
      poppins-regular

      md:text-md sm:text-base text-sm
      text-left

      prose-p:leading-relaxed md:prose-p:leading-relaxed
      prose-p:tracking-[0.020em]
      prose-p:my-[1em]

      prose-h2:text-[24px]
      prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

      prose-h3:text-[21px]
      prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold">
                {!! $category->pivot->content_id !!}
            </div>

            @else
            <div class="
            prose
      max-w-2xl mx-auto
      px-5
      poppins-regular

      md:text-md sm:text-base text-sm
      text-left

      prose-p:leading-relaxed md:prose-p:leading-relaxed
      prose-p:tracking-[0.020em]
      prose-p:my-[1em]

      prose-h2:text-[24px]
      prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

      prose-h3:text-[21px]
      prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold>{!!
                $category->pivot->content_en !!}</div>
            @endif
        </div>
        @endforeach
    </main>
</div>

@include('front.components.floating')

@endsection