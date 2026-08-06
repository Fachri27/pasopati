@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $trans = $petition->translation($locale);
    $title = $trans?->title ?? $petition->slug;
    $desc = $trans?->description ?? '';
    $progress = $petition->progressPercent();
    $count = $petition->signatureCount();
    $currentUrl = url()->current();
    $shareText = rawurlencode($locale === 'id' ? "Tanda tangani petisi: {$title}" : "Sign the petition: {$title}");
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-5 py-10 pt-20">

    {{-- Cover --}}
    @if ($petition->cover_image)
        <div class="aspect-[21/9] overflow-hidden rounded-xl mb-8">
            <img src="{{ asset('storage/' . $petition->cover_image) }}" alt="{{ $title }}"
                class="w-full h-full object-cover">
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Main Content --}}
        <div class="lg:col-span-2">
            <h1 class="text-3xl font-bold text-gray-900 font-serif">{{ $title }}</h1>
            <p class="text-gray-500 mt-2">
                {{ $locale === 'id' ? 'Ditujukan kepada' : 'Addressed to' }}:
                <span class="font-semibold text-gray-700">{{ $petition->target_name }}</span>
            </p>

            {{-- Description --}}
            @if ($desc)
                <div class="prose prose-sm max-w-none mt-6 text-gray-700">
                    {!! nl2br(e($desc)) !!}
                </div>
            @endif

            {{-- Demands --}}
            @if ($petition->demands)
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        {{ $locale === 'id' ? 'Tuntutan' : 'Demands' }}
                    </h2>
                    <ul class="space-y-2">
                        @foreach ($petition->demands as $demand)
                            <li class="flex items-start gap-2">
                                <span class="text-[#2B5343] mt-1 shrink-0">•</span>
                                <span class="text-gray-700">{{ $demand }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Share --}}
            <div class="mt-8 flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ $locale === 'id' ? 'Bagikan:' : 'Share:' }}</span>
                <a href="https://wa.me/?text={{ $shareText }}%20{{ rawurlencode($currentUrl) }}"
                    target="_blank" class="text-green-600 hover:text-green-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ rawurlencode($currentUrl) }}"
                    target="_blank" class="text-blue-400 hover:text-blue-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($currentUrl) }}"
                    target="_blank" class="text-blue-600 hover:text-blue-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl p-6 sticky top-24">

                {{-- Progress --}}
                <div class="text-center mb-6">
                    <div class="text-4xl font-bold text-[#2B5343]">{{ $progress }}%</div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ number_format($count) }} / {{ number_format($petition->goal_count) }}
                    </p>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                    <div class="bg-[#2B5343] h-3 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                </div>

                {{-- Sign Form --}}
                @if ($petition->status === 'active')
                    @if (session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('info'))
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg mb-4">
                            {{ session('info') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('petition.sign', ['locale' => $locale, 'slug' => $petition->slug]) }}"
                        class="space-y-3">
                        @csrf

                        {{-- Honeypot --}}
                        <div class="absolute -left-[99999px] -top-[99999px] opacity-0 pointer-events-none overflow-hidden h-0 w-0" aria-hidden="true" tabindex="-1">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <input type="text" name="name" placeholder="{{ $locale === 'id' ? 'Nama Lengkap*' : 'Full Name*' }}"
                                value="{{ old('name') }}" required
                                class="w-full border rounded-lg px-3 py-2 text-sm @error('name') border-red-500 @enderror">
                            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Email*"
                                value="{{ old('email') }}" required
                                class="w-full border rounded-lg px-3 py-2 text-sm @error('email') border-red-500 @enderror">
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <input type="text" name="city" placeholder="{{ $locale === 'id' ? 'Kota (opsional)' : 'City (optional)' }}"
                                value="{{ old('city') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <textarea name="comment" rows="2" placeholder="{{ $locale === 'id' ? 'Komentar (opsional)' : 'Comment (optional)' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('comment') }}</textarea>
                        </div>
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="consent" id="consent" required
                                class="mt-1">
                            <label for="consent" class="text-xs text-gray-500">
                                {{ $locale === 'id'
                                    ? 'Saya setuju data saya digunakan untuk verifikasi tanda tangan ini.'
                                    : 'I consent to my data being used for signature verification.' }}
                            </label>
                        </div>
                        @error('consent') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        <button type="submit"
                            class="w-full bg-[#2B5343] text-white py-2.5 rounded-lg font-medium hover:bg-[#1f3d31] transition text-sm">
                            {{ $locale === 'id' ? 'Tanda Tangani' : 'Sign Now' }}
                        </button>
                    </form>
                @elseif ($petition->status === 'closed')
                    <p class="text-center text-gray-500 text-sm">{{ $locale === 'id' ? 'Petisi ini sudah ditutup.' : 'This petition is closed.' }}</p>
                @elseif ($petition->status === 'succeeded')
                    <p class="text-center text-green-700 text-sm font-medium">{{ $locale === 'id' ? 'Target tercapai!' : 'Target achieved!' }}</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
