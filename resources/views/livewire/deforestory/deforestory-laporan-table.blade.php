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
            <a href="{{ route('deforestory.laporan.create', ['caseSlug' => $case->slug]) }}">
                <button class="admin-btn admin-btn--primary text-sm font-medium">
                    ➕ Tambah laporan
                </button>
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-admin-raised border border-admin-line text-admin-amber rounded">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
            <input type="text" wire:model.live.debounce.150ms="search" placeholder="Cari judul / slug laporan..." class="border p-2 rounded">
            <div class="flex items-center gap-2">
                <a href="{{ route('deforestory.case.preview', ['caseSlug' => $case->slug, 'locale' => 'id']) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm"
                   title="Lihat arsip termasuk laporan yang masih draft">Preview arsip</a>
                <a href="{{ route('deforestory.case', ['locale' => 'id', 'slug' => $case->slug]) }}"
                   target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm">Arsip publik</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-admin-raised text-left text-sm font-medium text-admin-ash">
                        <th class="p-3 w-20">Gambar</th>
                        <th class="p-3">Judul laporan</th>
                        <th class="p-3 w-40">Slug</th>
                        <th class="p-3 w-24">Urutan</th>
                        <th class="p-3 w-28">Status</th>
                        <th class="p-3 w-56">Aksi</th>
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
                        @endphp
                        <tr>
                            <td class="p-3">
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="" class="w-16 h-10 object-cover rounded">
                                @else
                                    <span class="text-xs text-admin-muted">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="text-sm font-medium text-admin-smoke block">{{ $title }}</span>
                            </td>
                            <td class="p-3">
                                <span class="text-xs font-mono text-admin-muted">/{{ $laporan->slug }}</span>
                            </td>
                            <td class="p-3 text-xs">{{ $laporan->sort }}</td>
                            <td class="p-3">
                                @if ($laporan->status === 'active')
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400">Aktif</span>
                                @elseif ($laporan->status === 'draft')
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-admin-amber/10 text-admin-amber">Draft</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-admin-raised text-admin-ash">Inactive</span>
                                @endif
                            </td>
                            <td class="p-3">
                                {{-- Anchor diberi kelas tombol, bukan membungkus <button>:
                                     tombol di dalam tautan itu sarang interaktif yang tidak sah. --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('deforestory.laporan.edit', $laporan->id) }}"
                                       class="admin-btn admin-btn--primary admin-btn--sm">Edit</a>
                                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                                       class="admin-btn admin-btn--ghost admin-btn--sm"
                                       title="Lihat laporan ini apa adanya, termasuk saat masih draft">Preview</a>
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