{{--
    Paginasi untuk CMS: "1–20 dari 57" di kiri, panah dan nomor halaman di
    kanan. Ia sendiri yang menjadi kaki tabel (.admin-table-foot), jadi cukup
    diletakkan di dalam .admin-table-frame, setelah penggulirnya:

        {{ $pages->links('vendor.pagination.admin') }}

    Kalau hanya ada satu halaman, tidak ada yang dirender — termasuk garis
    kakinya.

    Komponen Livewire yang memakai WithPagination harus menyebutkannya:

        {{ $items->links('vendor.pagination.admin', ['livewire' => true]) }}

    Tanpa itu tautannya <a href> biasa, yang memuat ulang halaman dan
    membuang keadaan komponen yang tidak tercatat di URL (saringan, pilihan).
    Dengan itu tombolnya memanggil gotoPage() milik Livewire.

    Terpisah dari vendor/pagination/custom.blade.php, yang dipakai halaman
    publik Pasopati dengan aksen hijau mereknya.
--}}
@php
    $lw = $livewire ?? false;
    $nama = $paginator->getPageName();
@endphp
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="admin-table-foot flex min-h-11 flex-wrap items-center gap-2 px-4 py-2 text-[12.5px] text-admin-muted">
        <p class="mr-auto">
            @if ($paginator->firstItem())
                <span class="admin-num font-medium text-admin-ash">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
                dari
            @endif
            <span class="admin-num font-medium text-admin-ash">{{ $paginator->total() }}</span>
        </p>

        @if ($paginator->onFirstPage())
            <span class="admin-icon-btn size-7" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <x-admin.icon name="left" class="size-3.5" />
            </span>
        @else
            <{{ $lw ? 'button type=button' : 'a' }} @if ($lw) wire:click="previousPage('{{ $nama }}')" @else href="{{ $paginator->previousPageUrl() }}" @endif
               rel="prev" class="admin-icon-btn size-7" aria-label="{{ __('pagination.previous') }}">
                <x-admin.icon name="left" class="size-3.5" />
            </{{ $lw ? 'button' : 'a' }}>
        @endif

        <span class="hidden items-center gap-0.5 sm:flex">
            @foreach ($elements as $element)
                {{-- Pemisah "…" --}}
                @if (is_string($element))
                    <span class="px-1" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="admin-num grid h-7 min-w-7 place-items-center rounded bg-admin-tint-strong px-1.5 font-semibold text-admin-smoke">{{ $page }}</span>
                        @else
                            <{{ $lw ? 'button type=button' : 'a' }} @if ($lw) wire:click="gotoPage({{ $page }}, '{{ $nama }}')" wire:key="hal-{{ $nama }}-{{ $page }}" @else href="{{ $url }}" @endif
                               class="admin-num grid h-7 min-w-7 cursor-pointer place-items-center rounded px-1.5 hover:bg-admin-tint hover:text-admin-smoke" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</{{ $lw ? 'button' : 'a' }}>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>

        {{-- Layar sempit: nomor halaman diganti "3 / 12" supaya barisnya tidak patah. --}}
        <span class="admin-num sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <{{ $lw ? 'button type=button' : 'a' }} @if ($lw) wire:click="nextPage('{{ $nama }}')" @else href="{{ $paginator->nextPageUrl() }}" @endif
               rel="next" class="admin-icon-btn size-7" aria-label="{{ __('pagination.next') }}">
                <x-admin.icon name="right" class="size-3.5" />
            </{{ $lw ? 'button' : 'a' }}>
        @else
            <span class="admin-icon-btn size-7" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <x-admin.icon name="right" class="size-3.5" />
            </span>
        @endif
    </nav>
@endif
