<div>
    @php
        // Judul kasus diambil dari kartu API saat kasusnya dibuat; kalau
        // terjemahannya belum ada, slug-nya yang tampil.
        $judulKasus = $case->translation('id')?->title ?: '/'.$case->slug;
    @endphp

    <x-admin.page-header :title="$judulKasus" eyebrow="Laporan kasus"
                         :description="'Tiap laporan punya judul, slug, gambar, ringkasan, dan isi sendiri; tampil publik di /deforestory/'.$case->slug.'/{slug-laporan}. Judul dan identitas kasus diambil dari kartu API.'"
                         :crumbs="[['Deforestory', route('deforestory.index')]]"
                         :current="'Laporan /'.$case->slug"
                         :back="route('deforestory.index')">
        <a href="{{ route('deforestory.laporan.create', ['caseSlug' => $case->slug]) }}"
           class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah laporan
        </a>

        {{-- Dua tautan arsip duduk di samping judul, bukan di bilah atas:
             bertiga dengan "Tambah laporan" mereka tidak muat di layar
             sempit, sedangkan slot ini turun ke bawah judul saat sesak. --}}
        <x-slot:aside>
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('deforestory.case.preview', ['caseSlug' => $case->slug, 'locale' => 'id']) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm"
                   title="Lihat arsip termasuk laporan yang masih draft">Pratinjau arsip</a>
                <a href="{{ route('deforestory.case', ['locale' => 'id', 'slug' => $case->slug]) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm">
                    Arsip publik
                    <x-admin.icon name="external" class="size-3.5" />
                </a>
            </div>
        </x-slot:aside>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.150ms="search" placeholder="Cari judul atau slug laporan…" aria-label="Cari laporan" />
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-20">Gambar</th>
                            <th>Laporan</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporans as $laporan)
                            @php
                                $idTrans = $laporan->translations->firstWhere('locale', 'id');
                                $title = $idTrans->title ?? '(tanpa judul)';
                                $imgUrl = $laporan->image
                                    ? (\Illuminate\Support\Str::startsWith($laporan->image, ['http://','https://'])
                                        ? $laporan->image
                                        : asset('storage/' . $laporan->image))
                                    : '';
                                $previewUrl = route('deforestory.laporan.preview', [
                                    'caseSlug' => $case->slug,
                                    'laporanSlug' => $laporan->slug,
                                    'locale' => 'id',
                                ]);
                                // Tautan bertanda tangan, berlaku 7 hari: untuk
                                // peninjau yang tidak punya akun CMS.
                                $bagikanUrl = app(\App\Support\DeforestoryPreviewLink::class)
                                    ->bagikan($case->slug, $laporan->slug, 'id');
                            @endphp
                            <tr wire:key="laporan-{{ $laporan->id }}">
                                <td>
                                    @if ($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="" class="h-10 w-16 rounded-md border border-admin-line object-cover">
                                    @else
                                        <span class="grid h-10 w-16 place-items-center rounded-md border border-admin-line bg-admin-inset text-admin-muted">
                                            <x-admin.icon name="image" />
                                        </span>
                                    @endif
                                </td>
                                <td class="min-w-[240px] max-w-[420px]">
                                    <a href="{{ route('deforestory.laporan.edit', $laporan->id) }}" class="block font-medium hover:underline">{{ $title }}</a>
                                    <span class="mt-0.5 block truncate font-admin-mono text-[11.5px] text-admin-muted"
                                          title="/{{ $laporan->slug }}">/{{ $laporan->slug }}</span>
                                </td>
                                <td class="admin-num text-admin-ash">{{ $laporan->sort }}</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($laporan->status === 'active')
                                            <span class="admin-cap admin-cap--ok admin-cap--dot">Aktif</span>
                                        @elseif ($laporan->status === 'draft')
                                            <span class="admin-cap admin-cap--quiet">Draft</span>
                                        @else
                                            <span class="admin-cap admin-cap--quiet">Inactive</span>
                                        @endif
                                        @if ($laporan->is_locked)
                                            <span class="inline-flex items-center gap-1 text-[11.5px] font-medium text-admin-muted"
                                                  title="Pratinjau laporan ini menuntut password">
                                                <x-admin.icon name="lock" class="size-3" />
                                                terkunci
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="admin-cell-actions">
                                    {{-- Anchor diberi kelas tombol, bukan membungkus <button>:
                                         tombol di dalam tautan itu sarang interaktif yang tidak sah. --}}
                                    <a href="{{ route('deforestory.laporan.edit', $laporan->id) }}"
                                       class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                                       class="admin-btn admin-btn--quiet admin-btn--sm"
                                       title="Lihat laporan ini apa adanya, termasuk saat masih draft">Pratinjau</a>
                                    <button type="button"
                                            x-data="{ disalin: false }"
                                            x-on:click="navigator.clipboard.writeText(@js($bagikanUrl)); disalin = true; setTimeout(() => disalin = false, 1600)"
                                            class="admin-btn admin-btn--quiet admin-btn--sm"
                                            title="Salin tautan pratinjau berlaku 7 hari untuk peninjau di luar CMS">
                                        <span x-show="!disalin">Salin tautan</span>
                                        <span x-show="disalin" x-cloak>Tersalin</span>
                                    </button>
                                    <button wire:click='delete({{ $laporan->id }})' type="button"
                                            wire:confirm="Hapus laporan ini?"
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    @if ($search)
                                        <x-admin.empty title="Tidak ada laporan yang cocok" icon="search">
                                            Coba kata kunci lain, atau kosongkan pencarian.
                                        </x-admin.empty>
                                    @else
                                        <x-admin.empty title="Belum ada laporan untuk kasus ini" icon="doc">
                                            <a href="{{ route('deforestory.laporan.create', ['caseSlug' => $case->slug]) }}"
                                               class="admin-btn admin-btn--ghost admin-btn--sm mt-1">
                                                <x-admin.icon name="plus" class="size-3.5" />
                                                Tambah laporan
                                            </a>
                                        </x-admin.empty>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $laporans->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>
    </div>

    <x-admin.toast />
</div>
