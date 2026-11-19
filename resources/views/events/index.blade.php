@extends('layouts.admin')

@section('content')
    @php
        // Saringan tersimpan di query string (form GET), jadi "ada saringan"
        // cukup dibaca dari sana — dipakai untuk tombol Reset dan untuk
        // membedakan dua keadaan kosong di bawah.
        $tersaring = request()->filled('search') || request()->filled('date_from')
            || request()->filled('date_to') || request()->filled('orientation');
    @endphp

    <x-admin.page-header title="Kejadian" description="Titik kejadian yang tampil di peta dan korsel halaman Fire.">
        <a href="{{ route('events.create') }}" class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah kejadian
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <form method="GET" action="{{ route('events.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search name="search" :value="request('search')" placeholder="Cari judul atau lokasi…" />

            <div class="flex w-full items-center gap-1.5 sm:w-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Tanggal kejadian, dari"
                       class="admin-input admin-num h-8 min-h-8 w-full py-0 text-[13px] sm:w-[9.5rem]">
                <span class="text-admin-muted" aria-hidden="true">–</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Tanggal kejadian, sampai"
                       class="admin-input admin-num h-8 min-h-8 w-full py-0 text-[13px] sm:w-[9.5rem]">
            </div>

            <select name="orientation" aria-label="Saring orientasi"
                    class="admin-input h-8 min-h-8 w-full py-0 text-[13px] sm:w-44">
                <option value="">Semua orientasi</option>
                <option value="landscape" @selected(request('orientation') === 'landscape')>Landscape</option>
                <option value="horizontal" @selected(request('orientation') === 'horizontal')>Horizontal</option>
            </select>

            <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">Terapkan</button>
            @if ($tersaring)
                <a href="{{ route('events.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Reset</a>
            @endif
        </form>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Media</th>
                            <th>Judul</th>
                            <th>Tanggal kejadian</th>
                            <th>Lokasi</th>
                            <th>Orientasi</th>
                            <th>Dibuat</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            @php $media = $event->media_items; @endphp
                            <tr>
                                <td>
                                    @if (count($media))
                                        <div class="flex items-center gap-1.5">
                                            @foreach (array_slice($media, 0, 2) as $item)
                                                @if ($item['type'] === 'video')
                                                    <div class="relative h-9 w-14 shrink-0 overflow-hidden rounded border border-admin-line bg-black">
                                                        <video src="{{ $item['url'] }}" preload="metadata" class="size-full object-cover"></video>
                                                        <span class="absolute inset-0 grid place-items-center bg-black/40 text-white">
                                                            <x-admin.icon name="video" class="size-3.5" />
                                                        </span>
                                                    </div>
                                                @else
                                                    <img src="{{ $item['url'] }}" alt="" loading="lazy"
                                                         class="h-9 w-14 shrink-0 rounded border border-admin-line object-cover">
                                                @endif
                                            @endforeach
                                            @if (count($media) > 2)
                                                <span class="admin-num shrink-0 text-[12px] text-admin-muted">+{{ count($media) - 2 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="grid h-9 w-14 place-items-center rounded border border-dashed border-admin-line-strong text-admin-muted" title="Tanpa media">
                                            <x-admin.icon name="image" class="size-3.5" />
                                        </div>
                                    @endif
                                </td>
                                <td class="min-w-[240px] max-w-[380px]">
                                    {{-- Judul kejadian sering berupa satu kalimat panjang.
                                         Dipotong di sini supaya tinggi baris tetap seragam;
                                         judul utuhnya ada di halaman Lihat dan di title. --}}
                                    <div class="flex items-start gap-2">
                                        <a href="{{ route('events.show', $event) }}" title="{{ $event->title_id }}"
                                           class="line-clamp-2 font-medium hover:underline">{{ $event->title_id }}</a>
                                        @if ($event->has_video || collect($media)->contains('type', 'video'))
                                            <span class="admin-cap admin-cap--quiet shrink-0">video</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 line-clamp-1 text-[12.5px] text-admin-muted">{{ $event->title_en }}</p>
                                </td>
                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $event->event_date->format('d M Y') }}</td>
                                <td class="max-w-[220px] truncate text-admin-ash" title="{{ $event->location }}">{{ $event->location }}</td>
                                <td class="text-admin-ash">
                                    {{ $event->orientation === \App\Enums\EventOrientation::Landscape ? 'Landscape' : 'Horizontal' }}
                                </td>
                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $event->created_at->format('d M Y') }}</td>
                                <td class="admin-cell-actions">
                                    <a href="{{ route('events.show', $event) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Lihat</a>
                                    <a href="{{ route('events.edit', $event) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus kejadian ini? Gambar dan videonya ikut terhapus, dan tindakan ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    @if ($tersaring)
                                        <x-admin.empty title="Tidak ada kejadian yang cocok" icon="pin">
                                            Ubah saringannya, atau <a href="{{ route('events.index') }}" class="underline">tampilkan semua</a>.
                                        </x-admin.empty>
                                    @else
                                        <x-admin.empty title="Belum ada kejadian" icon="pin">
                                            <a href="{{ route('events.create') }}" class="underline">Tambah kejadian pertama</a>.
                                        </x-admin.empty>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $events->links('vendor.pagination.admin') }}
        </div>
    </div>

    <x-admin.toast />
@endsection
