<div class="flex flex-col justify-center items-center">
    <div class="bg-admin-surface shadow rounded-lg p-6 w-full">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-admin-ash mb-3 flex items-center gap-2">
            <a href="{{ route('deforestory.index') }}" class="text-admin-smoke hover:text-blue-400 font-medium">Deforestory</a>
            <span class="text-admin-muted">›</span>
            <span class="text-blue-400 font-semibold">Laporan /{{ $case->slug }}</span>
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
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    ➕ Tambah laporan
                </button>
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
            <input type="text" wire:model.live.debounce.150ms="search" placeholder="Cari judul / slug laporan..." class="border p-2 rounded">
            <a href="{{ route('deforestory.case', ['locale' => 'id', 'slug' => $case->slug]) }}" target="_blank">
                <button class="bg-admin-raised hover:bg-admin-raised text-admin-ash px-4 py-2 rounded-lg text-sm font-medium border">
                    👁 Lihat arsip publik
                </button>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse table-fixed">
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
                            $publicUrl = route('deforestory.case.laporan', [
                                'locale' => 'id',
                                'slug' => $case->slug,
                                'laporanSlug' => $laporan->slug,
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
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-amber-500/10 text-amber-400">Draft</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-admin-raised text-admin-ash">Inactive</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ $publicUrl }}" target="_blank">
                                    <button class="bg-admin-raised px-3 py-1 rounded text-white text-xs">Lihat</button>
                                </a>
                                <a href="{{ route('deforestory.laporan.edit', $laporan->id) }}">
                                    <button class="bg-yellow-600 px-3 py-1 rounded text-white text-xs">Edit</button>
                                </a>
                                <button wire:click='delete({{ $laporan->id }})'
                                    onclick="return confirm('Hapus laporan ini?')"
                                    class="bg-red-600 px-3 py-1 rounded text-white text-xs">Hapus</button>
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
            {{ $laporans->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>