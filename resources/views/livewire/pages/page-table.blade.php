<div>
    <x-admin.page-header title="Artikel" description="Tulisan Expose dan Ngopini yang tampil di situs Pasopati.">
        <a href="{{ route('pages.create') }}" class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah artikel
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.100ms="search" placeholder="Cari judul…" />

            <div class="admin-tabs" role="group" aria-label="Saring status">
                @foreach (['' => 'Semua', 'draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $nilai => $label)
                    <button type="button" wire:click="$set('status', '{{ $nilai }}')"
                            aria-pressed="{{ (string) $status === (string) $nilai ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>

            <select wire:model.live.debounce.100ms="author" aria-label="Saring penulis"
                    class="admin-input h-8 min-h-8 w-full py-0 text-[13px] sm:w-44">
                <option value="">Semua penulis</option>
                <option value="me">Tulisan saya</option>
            </select>

            {{-- wire:ignore: flatpickr mengubah <input>-nya sendiri (kelas,
                 atribut, nilai), dan morph Livewire akan mengembalikannya ke
                 markup awal tiap kali tabel dirender ulang. --}}
            <div wire:ignore class="flex w-full items-center gap-1.5 sm:w-auto" x-data="{
                dateRange: @entangle('dataRange').defer,
                picker: null,
                init() {
                    this.picker = flatpickr(this.$refs.dateRange, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        locale: { rangeSeparator: ' to ' },
                        onChange: (selectedDates, dateStr) => {
                            this.dateRange = dateStr
                            $wire.set('dataRange', dateStr)
                        }
                    })
                },
                resetDate() {
                    this.$refs.dateRange._flatpickr.clear()
                    this.dateRange = ''
                    $wire.set('dataRange', null)
                }
            }">
                <input x-ref="dateRange" type="text" readonly placeholder="Rentang tanggal terbit" aria-label="Rentang tanggal terbit"
                       class="admin-input h-8 min-h-8 w-full py-0 text-[13px] sm:w-56">
                <button type="button" @click="resetDate" class="admin-btn admin-btn--quiet admin-btn--sm">Reset</button>
            </div>
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tampilan</th>
                            <th>Jenis</th>
                            <th>Terbit</th>
                            <th>Status</th>
                            <th>Penulis</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $data)
                            @php
                                $idTranslation = $data->translations->firstWhere('locale', 'id')->title ?? '-';
                            @endphp
                            <tr wire:key="page-{{ $data->id }}">
                                <td class="min-w-[240px] max-w-[380px]">
                                    <a href="{{ route('pages.edit', $data->id) }}" class="font-medium hover:underline">{{ $idTranslation }}</a>
                                </td>
                                <td class="text-admin-ash">{{ $data->type === 'default' ? 'default' : 'parallax' }}</td>
                                <td class="text-admin-ash">{{ $data->page_type === 'expose' ? 'expose' : 'ngopini' }}</td>
                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $data->published_at ?: '—' }}</td>
                                <td>
                                    @if ($data->status === 'active')
                                        <span class="admin-cap admin-cap--ok admin-cap--dot">active</span>
                                    @elseif ($data->status === 'draft')
                                        <span class="admin-cap admin-cap--quiet">draft</span>
                                    @else
                                        <span class="admin-cap admin-cap--quiet">inactive</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-admin-ash">{{ auth()->user()->name }}</td>
                                <td class="admin-cell-actions">
                                    <a href="{{ route('page.preview', ['locale' => app()->getLocale(), 'page_type' => $data->page_type, 'slug' => $data->slug]) }}"
                                       target="_blank" class="admin-btn admin-btn--quiet admin-btn--sm">Pratinjau</a>
                                    <a href="{{ route('pages.edit', $data->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <button type="button" wire:click="delete({{ $data->id }})"
                                            wire:confirm="Hapus artikel ini? Tindakan ini tidak bisa dibatalkan."
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    <x-admin.empty title="Tidak ada artikel" icon="doc">
                                        Belum ada artikel yang cocok dengan saringan ini.
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pages->links('vendor.pagination.admin') }}
        </div>
    </div>

    <x-admin.toast />
</div>
