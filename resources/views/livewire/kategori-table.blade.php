<div>
    <x-admin.page-header title="Kategori" description="Tiap kategori menjadi satu tab isi di halaman fellowship.">
        <a href="{{ route('kategori.create') }}" class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah kategori
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.300ms="search" placeholder="Cari nama kategori…" />
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama (Indonesia)</th>
                            <th>Nama (English)</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategori as $data)
                            @php
                                $idKategori = $data->translations->firstWhere('locale', 'id')->kategori_name ?? '-';
                                $enKategori = $data->translations->firstWhere('locale', 'en')->kategori_name ?? '-';
                            @endphp
                            <tr wire:key="kategori-{{ $data->id }}">
                                <td>
                                    <a href="{{ route('kategori.edit', $data->id) }}" class="font-medium hover:underline">{{ $idKategori }}</a>
                                </td>
                                <td class="text-admin-ash">{{ $enKategori }}</td>
                                <td class="admin-cell-actions">
                                    <a href="{{ route('kategori.edit', $data->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <button type="button" wire:click="delete({{ $data->id }})"
                                            wire:confirm="Hapus kategori ini? Tindakan ini tidak bisa dibatalkan."
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-0">
                                    <x-admin.empty title="Tidak ada kategori" icon="tag">
                                        Belum ada kategori yang cocok dengan pencarian ini.
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $kategori->links('vendor.pagination.admin') }}
        </div>
    </div>

    <x-admin.toast />
</div>
