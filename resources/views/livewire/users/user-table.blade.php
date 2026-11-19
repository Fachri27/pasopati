<div>
    <x-admin.page-header title="Pengguna" description="Akun CMS dan akun komentar pembaca, beserta perannya.">
        <a href="{{ route('user.create') }}" class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah pengguna
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.300ms="search" placeholder="Cari nama…" />
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $data)
                            @php
                                // Akun komentar yang masuk lewat Google menyimpan
                                // alamat avatar utuh, bukan path di storage.
                                $foto = $data->image
                                    ? (\Illuminate\Support\Str::startsWith($data->image, ['http://', 'https://']) ? $data->image : asset('storage/' . $data->image))
                                    : null;
                            @endphp
                            <tr wire:key="pengguna-{{ $data->id }}">
                                <td class="min-w-[220px]">
                                    <div class="flex items-center gap-2.5">
                                        @if ($foto)
                                            <img src="{{ $foto }}" alt="" loading="lazy" referrerpolicy="no-referrer"
                                                 class="size-7 shrink-0 rounded-full object-cover">
                                        @else
                                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-admin-tint-strong text-[11.5px] font-semibold text-admin-smoke">
                                                {{ strtoupper(mb_substr($data->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <a href="{{ route('user.edit', $data->id) }}" class="min-w-0 font-medium hover:underline">{{ $data->name }}</a>
                                        @if ($data->id === auth()->id())
                                            <span class="admin-cap admin-cap--quiet">Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-admin-ash">{{ $data->email }}</td>
                                <td>
                                    <span class="admin-cap {{ $data->role === 'admin' ? '' : 'admin-cap--quiet' }}">{{ $data->role }}</span>
                                </td>
                                <td class="admin-cell-actions">
                                    <a href="{{ route('user.edit', $data->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    {{-- Akun sendiri tidak diberi tombol hapus:
                                         menghapusnya langsung mengeluarkan
                                         admin dari CMS di tengah sesi. --}}
                                    @if ($data->id !== auth()->id())
                                        <button type="button" wire:click="delete({{ $data->id }})"
                                                wire:confirm="Hapus pengguna {{ $data->name }}? Tindakan ini tidak bisa dibatalkan."
                                                class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0">
                                    <x-admin.empty title="Tidak ada pengguna" icon="user">
                                        Belum ada pengguna yang cocok dengan pencarian ini.
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links('vendor.pagination.admin') }}
        </div>
    </div>

    <x-admin.toast />
</div>
