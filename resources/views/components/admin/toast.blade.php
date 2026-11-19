{{--
    Kabar sekilas dari session('success') / session('error').

    Ditaruh di view masing-masing, BUKAN di layout: pada komponen Livewire
    flash-nya sering dipasang tanpa pindah halaman (mis. setelah menghapus
    baris), dan hanya bagian di dalam akar komponen yang ikut dirender ulang.
--}}
@php
    $galat = session('error');
    $kabar = $galat ?: session('success');
@endphp

@if ($kabar)
    <div class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex justify-end"
         x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 5000)"
         x-show="tampil" x-transition.opacity>
        <div class="admin-toast pointer-events-auto" role="status" data-kind="{{ $galat ? 'error' : 'ok' }}">
            <p class="min-w-0 flex-1">{{ $kabar }}</p>
            <button type="button" class="admin-icon-btn -my-1 -mr-1.5 size-6" @click="tampil = false" aria-label="Tutup">
                <x-admin.icon name="close" class="size-3.5" />
            </button>
        </div>
    </div>
@endif
