@props([
    'label',
    'hint' => null,
    'preset' => 'content',
    'value' => '',
])

@php
    $wireModel = $attributes->wire('model')->value();
@endphp

<div>
    <div class="mb-2 flex items-end justify-between gap-4">
        <div>
            <label class="block text-sm font-semibold text-admin-ash">{{ $label }}</label>
            @if ($hint)
                <p class="mt-0.5 text-xs text-admin-muted">{{ $hint }}</p>
            @endif
        </div>
        <span class="bg-admin-raised px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-admin-muted">TinyMCE custom</span>
    </div>

    {{-- Dua textarea, bukan satu. Yang di dalam wire:ignore adalah tempat
         TinyMCE menanam dirinya, dan harus kebal morph Livewire — kalau
         Livewire ikut mengganti simpul itu, editornya lenyap tiap kali
         komponen dirender ulang. Yang kedua tersembunyi dan hanya menjadi
         jembatan wire:model: JS menyalin isi editor ke sana lalu memicu
         `input`, sehingga Livewire tetap menerima perubahan seperti biasa. --}}
    <div
        data-tinymce-wrapper
        data-tinymce-picker-id="{{ $wireModel }}"
        data-tinymce-preset="{{ $preset }}"
        data-tinymce-reference-page-url="{{ route('reference.index', ['picker' => 1, 'editor' => $wireModel]) }}"
        data-tinymce-visualization-options-url="{{ route('data-visualizations.options') }}"
    >
        <div wire:ignore>
            <textarea data-tinymce-editor>{{ $value }}</textarea>
        </div>
        <textarea data-tinymce-input wire:model="{{ $wireModel }}" class="hidden" aria-hidden="true">{{ $value }}</textarea>
    </div>

    @error($wireModel)
        <p class="mt-1.5 text-xs font-medium text-red-400">{{ $message }}</p>
    @enderror
</div>
