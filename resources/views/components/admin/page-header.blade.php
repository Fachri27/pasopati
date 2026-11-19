{{--
    Kop halaman CMS: bilah atas yang menempel + kepala berjudul.

    Bilah atasnya memuat tombol kembali, remah, dan aksi halaman (slot
    bawaan). Aksi sengaja ditaruh di bilah yang menempel, bukan di bawah form:
    Simpan tetap terjangkau tanpa menggulir ke dasar halaman. Tombol submit
    di sini berada DI LUAR <form>-nya, jadi sambungkan lewat atribut form:
        <button type="submit" form="form-petisi" class="admin-btn admin-btn--primary admin-btn--sm">Simpan</button>

    crumbs  — leluhur halaman ini, urut dari atas: [['Petisi', route(...)], ...].
              "CMS" di depan dan judul halaman di belakang ditambahkan sendiri.
    narrow  — kolom 920px (form satu kolom, halaman baca); bawaan 1200px.
              Badan halaman harus memakai lebar yang sama (admin-wrap--narrow)
              supaya tepinya segaris dengan kop.
    aside   — slot di kanan judul (mis. cap status).
--}}
@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'back' => null,
    'crumbs' => [],
    'current' => null,
    'narrow' => false,
])

@php
    $wrap = 'admin-wrap'.($narrow ? ' admin-wrap--narrow' : '');
@endphp

<div class="admin-topbar">
    <div class="{{ $wrap }} flex items-center gap-2">
        @if ($back)
            <a href="{{ $back }}" class="admin-icon-btn -ml-1.5" title="Kembali" aria-label="Kembali">
                <x-admin.icon name="left" />
            </a>
        @endif

        {{-- flex-1 (basis 0): kalau bilahnya sesak, remah yang lebih dulu
             mengalah sampai habis, baru tombol aksi melipat ke baris kedua.
             Judul halaman masih terbaca di <h1> tepat di bawahnya. --}}
        <nav class="admin-crumbs flex-1" aria-label="Remah">
            <a href="{{ route('dashboard') }}">CMS</a>
            @foreach ($crumbs as $crumb)
                <span data-sep aria-hidden="true">/</span>
                @if (! empty($crumb[1]))
                    <a href="{{ $crumb[1] }}">{{ $crumb[0] }}</a>
                @else
                    <span class="px-1">{{ $crumb[0] }}</span>
                @endif
            @endforeach
            <span data-sep aria-hidden="true">/</span>
            <span aria-current="page">{{ $current ?? $title }}</span>
        </nav>

        @if ($slot->isNotEmpty())
            <div class="flex min-w-0 flex-wrap items-center justify-end gap-1.5">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>

<header class="{{ $wrap }} admin-head flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="admin-eyebrow mb-1">{{ $eyebrow }}</p>
        @endif
        <h1>{{ $title }}</h1>
        @if ($description)
            <p data-desc>{{ $description }}</p>
        @endif
    </div>
    @isset($aside)
        <div class="shrink-0">{{ $aside }}</div>
    @endisset
</header>
