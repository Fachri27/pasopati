@props([
    // Opsional: saat editornya sudah duduk di bawah judul seksi yang menyebut
    // hal yang sama, label di sini hanya mengulanginya.
    'label' => null,
    'hint' => null,
    'preset' => 'content',
    'value' => '',
])

@php
    $wireModel = $attributes->wire('model')->value();
@endphp

<div>
    {{-- Kepala hanya dibuat kalau memang ada isinya: tanpa label dan hint,
         editornya menempel langsung ke judul seksi di atasnya. --}}
    @if ($label || $hint)
        <div class="mb-2">
            @if ($label)
                <p class="admin-label mb-0">{{ $label }}</p>
            @endif
            @if ($hint)
                <p class="admin-hint mt-0.5">{{ $hint }}</p>
            @endif
        </div>
    @endif

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
            <textarea data-tinymce-editor aria-label="{{ $label ?: $hint }}">{{ $value }}</textarea>
        </div>
        <textarea data-tinymce-input wire:model="{{ $wireModel }}" class="hidden" aria-hidden="true">{{ $value }}</textarea>
    </div>

    @error($wireModel)
        <p class="admin-error">{{ $message }}</p>
    @enderror
</div>
