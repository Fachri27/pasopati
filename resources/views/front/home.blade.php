@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<section id="hero" class="md:h-[300px] h-[200px] flex flex-col justify-center items-center">
    {{-- <p>Current Locale: {{ app()->getLocale() }}</p> --}}
    {{-- <p>{{ __('messages.welcome') }}</p> --}}
    <div class="flex space-x-3 mt-6 px-3">
        @if($fellowship)
        <a
            href="{{ route('fellowship.preview', ['locale' => app()->getLocale(), 'slug' => $fellowship->slug ?? '#']) }}">
            <img src="{{ $fellowship->translations->first()?->image_cover ? asset('storage/' . $fellowship->translations->first()->image_cover) : asset('img/ban-1.png') }}"
                alt="" class="w-[350px]">
        </a>
        @else
        <a href="#">
            <img src="{{ asset('img/ban-1.png') }}" alt="" class="w-[350px]">
        </a>
        @endif
        <a href="{{ route('fellowship-user', ['locale' => app()->getLocale()]) }}">
            <img src="{{ asset('img/ban-2.png') }}" alt="" class="w-[350px]">
        </a>
    </div>
</section>

<!-- Card List -->
@include('front.components.card-list', ['limit' => 6, 'offset' => 0])

<!-- Ngopini-Hutan -->
@include('front.landing-ngopini')

<!-- Card List -->
@include('front.components.card-list', ['limit' => null, 'offset' => 6])

@include('front.components.floating')

@endsection