{{--
    Kotak cari di bilah saring. Atribut (wire:model…, name, value) diteruskan
    ke <input>. data-cari membuatnya jadi sasaran pintasan "/" — lihat skrip
    di layouts/admin.blade.php.
--}}
@props(['placeholder' => 'Cari…'])

<div class="relative w-full sm:w-72">
    <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-admin-muted" />
    <input type="text" inputmode="search" autocomplete="off" data-cari placeholder="{{ $placeholder }}"
           {{ $attributes->class('admin-input h-8 min-h-8 py-0 pr-9 pl-8 text-[13px]') }}>
    <kbd class="admin-kbd pointer-events-none absolute top-1/2 right-2 -translate-y-1/2">/</kbd>
</div>
