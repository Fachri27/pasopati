<div class="flex flex-col justify-center items-center">
    <div class="bg-admin-surface shadow rounded-lg p-6 w-full">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-admin-ash mb-3 flex items-center gap-2">
            <a href="{{ route('deforestory.index') }}" class="text-admin-smoke hover:text-admin-amber font-medium">Deforestory</a>
            <span class="text-admin-muted">›</span>
            <span class="text-admin-amber font-semibold">Laporan /{{ $case->slug }}</span>
        </nav>

        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-lg font-semibold text-admin-smoke">Laporan kasus /{{ $case->slug }}</h2>
                <p class="text-xs text-admin-muted mt-1">
                    Tiap laporan = entitas sendiri (judul, slug, gambar, excerpt, isi). Detail publik di
                    <span class="font-mono">/deforestory/{{ $case->slug }}/{slug-laporan}</span>.
                    Judul & identitas kasus diambil dari kartu API.
                </p>
            </div>
            <a href="{{ route('deforestory.laporan.create', ['caseSlug' => $case->slug]) }}"
               class="admin-btn admin-btn--primary shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                </svg>
                Tambah laporan
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-admin-raised border border-admin-line text-admin-amber rounded">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
            <div class="relative w-full sm:w-80">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                {{-- Ruang ikon ditulis inline: .admin-input memakai padding
                     shorthand di luar @layer utilities, jadi pl-* selalu kalah. --}}
                <input type="text" wire:model.live.debounce.150ms="search"
                       placeholder="Cari judul atau slug laporan…"
                       class="admin-input" style="padding-left: 2.25rem"
                       aria-label="Cari laporan">
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('deforestory.case.preview', ['caseSlug' => $case->slug, 'locale' => 'id']) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm"
                   title="Lihat arsip termasuk laporan yang masih draft">Preview arsip</a>
                <a href="{{ route('deforestory.case', ['locale' => 'id', 'slug' => $case->slug]) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm">Arsip publik</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            {{-- Tanpa table-fixed: dengan lebar kolom dipatok, kolom Aksi
                 selebar 14rem memaksa empat tombolnya membungkus jadi dua
                 baris. Lebarnya sekarang mengikuti isinya. --}}
            <table class="admin-table min-w-full">
                <thead>
                    <tr>
                        <th class="w-20">Gambar</th>
                        <th>Judul laporan</th>
                        <th class="w-48 max-w-48">Slug</th>
                        <th class="w-20">Urutan</th>
                        <th class="w-28">Status</th>
                        <th class="w-[22rem]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-admin-ash">
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
                        <tr>
                            <td>
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="" class="w-16 h-10 object-cover rounded">
                                @else
                                    <span class="text-xs text-admin-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="block font-medium text-admin-smoke">{{ $title }}</span>
                            </td>
                            <td>
                                <span class="block truncate font-mono text-[11px] text-admin-muted"
                                      title="/{{ $laporan->slug }}">/{{ $laporan->slug }}</span>
                            </td>
                            <td class="font-mono text-xs text-admin-ash">{{ $laporan->sort }}</td>
                            <td>
                                @if ($laporan->status === 'active')
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold badge-active">Aktif</span>
                                @elseif ($laporan->status === 'draft')
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold badge-pending">Draft</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold badge-draft">Inactive</span>
                                @endif
                                @if ($laporan->is_locked)
                                    <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-medium text-admin-muted"
                                          title="Pratinjau laporan ini menuntut password">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        terkunci
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{-- Anchor diberi kelas tombol, bukan membungkus <button>:
                                     tombol di dalam tautan itu sarang interaktif yang tidak sah. --}}
                                <div class="flex flex-wrap items-center gap-2 [&>*]:whitespace-nowrap">
                                    <a href="{{ route('deforestory.laporan.edit', $laporan->id) }}"
                                       class="admin-btn admin-btn--primary admin-btn--sm">Edit</a>
                                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                                       class="admin-btn admin-btn--ghost admin-btn--sm"
                                       title="Lihat laporan ini apa adanya, termasuk saat masih draft">Preview</a>
                                    <button type="button"
                                            x-data="{ disalin: false }"
                                            x-on:click="navigator.clipboard.writeText(@js($bagikanUrl)); disalin = true; setTimeout(() => disalin = false, 1600)"
                                            class="admin-btn admin-btn--ghost admin-btn--sm"
                                            title="Salin tautan pratinjau berlaku 7 hari untuk peninjau di luar CMS">
                                        <span x-show="!disalin">Salin tautan</span>
                                        <span x-show="disalin" x-cloak>Tersalin ✓</span>
                                    </button>
                                    <button wire:click='delete({{ $laporan->id }})'
                                            wire:confirm="Hapus laporan ini?"
                                            class="admin-btn admin-btn--danger admin-btn--sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-3 text-center text-admin-muted" colspan="6">
                                Belum ada laporan untuk kasus ini. Klik <strong>Tambah laporan</strong> untuk membuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $laporans->links('vendor.pagination.admin') }}
        </div>
    </div>
</div>