{{-- Keadaan kosong: ikon, satu kalimat tegas, dan (opsional) penjelasan atau
     tombol di slot. Di dalam tabel, bungkus dengan <td colspan="…" class="p-0">. --}}
@props([
    'title',
    'icon' => 'inbox',
])

<div {{ $attributes->class('admin-empty') }}>
    <x-admin.icon :name="$icon" />
    <strong>{{ $title }}</strong>
    @if ($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
