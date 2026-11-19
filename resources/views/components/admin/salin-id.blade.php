{{--
    Tombol "Salin dari Indonesia" untuk kartu isi di tab English.

    Isi Indonesia — termasuk gambar, galeri, dan grafik yang sudah dipasang
    di dalamnya — disalin utuh ke kolom English, supaya yang tersisa tinggal
    menerjemahkan teksnya, bukan menyusun ulang gambarnya. Hanya tampil saat
    tab English aktif, jadi harus berada di dalam x-data yang punya `lang`.

    Tiga cara menyebutkan sumber/tujuan:
      dari + ke      — id textarea TinyMCE (x-ref/id di partial editor).
      kunciDari + kunciKe — nilai wire:key pembungkus <x-tinymce-editor>.
      wire           — nama method Livewire yang menyalin sendiri (blok).
    wireGambar menambahkan tombol kedua "Salin gambar saja" (mode wire); dua
    mode lainnya otomatis memilikinya.
--}}
@props([
    'dari' => null,
    'ke' => null,
    'kunciDari' => null,
    'kunciKe' => null,
    'wire' => null,
    'wireGambar' => null,
])

<div x-show="lang === 'en'" x-cloak class="flex flex-wrap items-center justify-end gap-1.5">
<button type="button"
        class="admin-btn admin-btn--ghost admin-btn--sm"
        title="Isi Indonesia disalin ke sini, lengkap dengan gambarnya. Isi English yang ada akan diganti."
        @if ($wire)
            wire:click="{{ $wire }}"
            wire:confirm="Ganti seluruh isi English dengan isi Indonesia? Isi English yang ada akan hilang."
        @elseif ($dari)
            @click="cmsSalinEditor('{{ $dari }}', '{{ $ke }}')"
        @else
            @click="cmsSalinPembungkus('{{ $kunciDari }}', '{{ $kunciKe }}')"
        @endif>
    <x-admin.icon name="doc" class="size-3.5" />
    Salin dari Indonesia
</button>

<button type="button"
        class="admin-btn admin-btn--quiet admin-btn--sm"
        title="Hanya gambar dan media yang disalin, di urutan yang sama. Teks English tidak diubah."
        @if ($wire)
            wire:click="{{ $wireGambar }}"
        @elseif ($dari)
            @click="cmsSalinGambarEditor('{{ $dari }}', '{{ $ke }}')"
        @else
            @click="cmsSalinGambarPembungkus('{{ $kunciDari }}', '{{ $kunciKe }}')"
        @endif>
    Salin gambar saja
</button>
</div>
