<div>
    <x-admin.page-header title="Pelanggan Deforestory"
                         description="Alamat email yang meminta kabar tiap kali laporan baru terbit."
                         :crumbs="[['Deforestory', route('deforestory.index')]]"
                         current="Pelanggan"
                         :back="route('deforestory.index')">
        <x-slot:aside>
            <p class="text-[13px] text-admin-muted">
                <span class="admin-num font-medium text-admin-smoke">{{ $activeCount }}</span> aktif dari
                <span class="admin-num font-medium text-admin-smoke">{{ $total }}</span> alamat
            </p>
        </x-slot:aside>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.100ms="search" placeholder="Cari email…" aria-label="Cari email pelanggan" />

            <div class="admin-tabs" role="group" aria-label="Saring status">
                @foreach (['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] as $nilai => $label)
                    <button type="button" wire:click="$set('status', '{{ $nilai }}')"
                            aria-pressed="{{ $status === $nilai ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Cakupan</th>
                            <th>Bahasa</th>
                            <th>Status</th>
                            <th>Berlangganan sejak</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscribers as $subscriber)
                            <tr wire:key="pelanggan-{{ $subscriber->id }}">
                                <td class="font-medium">{{ $subscriber->email }}</td>
                                <td class="max-w-[260px]">
                                    @if ($subscriber->type === 'all')
                                        <span class="text-admin-ash">Semua kasus</span>
                                    @else
                                        @php
                                            // ?-> : kasusnya bisa sudah dihapus sementara
                                            // langganannya masih tersisa.
                                            $kasus = $subscriber->case?->translation('id')?->title ?? $subscriber->case?->slug ?? '-';
                                        @endphp
                                        <span class="block truncate text-admin-ash" title="{{ $kasus }}">{{ $kasus }}</span>
                                        <span class="block text-[11.5px] text-admin-muted">satu kasus</span>
                                    @endif
                                </td>
                                <td class="uppercase text-admin-ash">{{ $subscriber->locale }}</td>
                                <td>
                                    @if ($subscriber->active)
                                        <span class="admin-cap admin-cap--ok admin-cap--dot">aktif</span>
                                    @else
                                        <span class="admin-cap admin-cap--quiet">nonaktif</span>
                                    @endif
                                </td>
                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $subscriber->subscribed_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="admin-cell-actions">
                                    <button type="button" wire:click="toggle({{ $subscriber->id }})" class="admin-btn admin-btn--quiet admin-btn--sm">
                                        {{ $subscriber->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    {{-- wire:confirm, bukan onclick="return confirm()": Livewire
                                         memasang pendengar kliknya sendiri, jadi `return false`
                                         dari onclick tidak pernah membatalkan penghapusan. --}}
                                    <button type="button" wire:click="delete({{ $subscriber->id }})"
                                            wire:confirm="Hapus pelanggan ini? Ia tidak akan menerima kabar laporan baru lagi."
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <x-admin.empty title="Tidak ada pelanggan" icon="mail">
                                        Belum ada alamat yang cocok dengan saringan ini.
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $subscribers->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>
    </div>

    <x-admin.toast />
</div>
