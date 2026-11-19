<div>
    @php
        $locale = app()->getLocale();
        $judul = $petition->translation($locale)?->title ?? $petition->slug;
    @endphp

    <x-admin.page-header :title="$judul" eyebrow="Tanda tangan petisi"
                         :description="number_format($petition->signatureCount()).' terverifikasi dari target '.number_format($petition->goal_count)"
                         :crumbs="[['Petisi', route('petition.admin.index')]]"
                         :back="route('petition.admin.index')">
        <a href="{{ route('petition.admin.edit', $petition->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah petisi</a>
        <a href="{{ route('petition.admin.export-pdf', $petition->id) }}" class="admin-btn admin-btn--ghost admin-btn--sm">
            <x-admin.icon name="download" class="size-3.5" />
            PDF
        </a>
        {{-- CSV hanya memuat tanda tangan yang sudah terverifikasi, apa pun
             saringan yang sedang dipakai di bawah. --}}
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                title="Unduh tanda tangan terverifikasi sebagai CSV" class="admin-btn admin-btn--ghost admin-btn--sm">
            <x-admin.icon name="download" class="size-3.5" />
            CSV
        </button>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, atau kota…" />

            <div class="max-w-full overflow-x-auto">
                <div class="admin-tabs" role="group" aria-label="Saring verifikasi">
                    @foreach (['' => 'Semua', 'verified' => 'Terverifikasi', 'unverified' => 'Belum verifikasi'] as $nilai => $label)
                        <button type="button" wire:click="$set('filterVerified', '{{ $nilai }}')"
                                aria-pressed="{{ (string) $filterVerified === (string) $nilai ? 'true' : 'false' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Kota</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Komentar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($signatures as $signature)
                            <tr wire:key="ttd-{{ $signature->id }}">
                                <td class="font-medium whitespace-nowrap">{{ $signature->name }}</td>
                                <td class="text-admin-ash">{{ $signature->email }}</td>
                                <td class="whitespace-nowrap text-admin-ash">{{ $signature->city ?? '—' }}</td>
                                <td>
                                    @if ($signature->is_verified)
                                        <span class="admin-cap admin-cap--ok admin-cap--dot">terverifikasi</span>
                                    @else
                                        <span class="admin-cap admin-cap--quiet">menunggu</span>
                                    @endif
                                </td>
                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $signature->created_at->format('d M Y H:i') }}</td>
                                <td class="min-w-[220px] max-w-[340px]">
                                    @if ($signature->comment)
                                        <div class="flex items-start gap-1.5">
                                            <span class="line-clamp-2 min-w-0 flex-1 text-admin-ash" title="{{ $signature->comment }}">{{ $signature->comment }}</span>
                                            <button type="button" wire:click="deleteComment({{ $signature->id }})"
                                                    wire:confirm="Hapus komentar ini? Tanda tangannya tetap tersimpan."
                                                    class="admin-icon-btn -my-1 size-7 shrink-0" title="Hapus komentar" aria-label="Hapus komentar {{ $signature->name }}">
                                                <x-admin.icon name="trash" class="size-3.5" />
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-admin-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <x-admin.empty title="Tidak ada tanda tangan" icon="pen">
                                        @if ($search !== '' || $filterVerified !== '')
                                            Tidak ada tanda tangan yang cocok dengan saringan ini.
                                        @else
                                            Petisi ini belum ditandatangani.
                                        @endif
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $signatures->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>
    </div>

    <x-admin.toast />
</div>
