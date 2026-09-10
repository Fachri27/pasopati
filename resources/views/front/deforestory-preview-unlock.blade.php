@extends('layouts.deforestory')

@section('content')

@php
    $isId = $locale !== 'en';
    $lt = $laporan->translation($locale) ?? $laporan->translation('id');
    $judul = $lt->title ?? ($isId ? 'Laporan terkunci' : 'Locked report');
@endphp

<main class="pt-[calc(64px+3rem)] pb-24 px-5">
    <div class="max-w-[420px] mx-auto text-center">

        <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-paper border border-line mb-6">
            <svg class="w-6 h-6 text-ink-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </span>

        <p class="font-mono-ui text-[.7rem] font-semibold uppercase tracking-[.16em] text-pasopati mb-3">
            {{ $isId ? 'Pratinjau terkunci' : 'Protected preview' }}
        </p>

        <h1 class="font-display font-bold text-[clamp(1.4rem,4vw,2rem)] leading-[1.2] tracking-[-.015em]">{{ $judul }}</h1>

        <p class="mt-4 text-[.95rem] leading-[1.7] text-ink-2">
            {{ $isId
                ? 'Laporan ini belum terbit. Masukkan password pratinjau untuk membukanya.'
                : 'This report is not published yet. Enter the preview password to open it.' }}
        </p>

        @if ($belumDiatur)
            {{-- Password global belum diisi di CMS. Tanpa pesan ini, pembaca
                 hanya akan mencoba-coba password yang memang belum ada. --}}
            <p class="mt-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ $isId
                    ? 'Password pratinjau belum diatur di CMS. Hubungi pengirim tautan ini.'
                    : 'The preview password has not been set in the CMS. Contact whoever sent you this link.' }}
            </p>
        @endif

        <form method="POST" action="{{ $unlockUrl }}" class="mt-7 text-left">
            @csrf

            <label for="password" class="block text-sm font-medium text-ink mb-1.5">
                {{ $isId ? 'Password' : 'Password' }}
            </label>
            <input id="password" type="password" name="password" required autofocus
                   autocomplete="current-password"
                   class="w-full rounded-lg border border-line bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-forest focus:ring-2 focus:ring-forest/15"
                   placeholder="{{ $isId ? 'Masukkan password pratinjau' : 'Enter the preview password' }}">

            @error('password')
                <p class="mt-2 text-sm font-medium text-pasopati">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="mt-4 w-full rounded-lg bg-forest px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                {{ $isId ? 'Buka pratinjau' : 'Open preview' }}
            </button>
        </form>

        <p class="mt-6 font-mono-ui text-[.65rem] uppercase tracking-[.12em] text-ink-3">
            {{ $isId ? 'Jangan bagikan tautan ini lebih jauh' : 'Please do not share this link further' }}
        </p>
    </div>
</main>

@endsection
