<div class="w-full">
    <div class="bg-admin-surface shadow rounded-lg p-6 w-full">

        {{-- ── Kepala halaman ─────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div class="max-w-3xl">
                <h2 class="text-lg font-semibold text-admin-smoke">Deforestory — Arsip Kasus</h2>
                <p class="text-xs text-admin-muted mt-1 leading-relaxed">
                    Kartu kasus datang dari API dan tidak dibuat di sini. Yang diisi lewat CMS adalah
                    <strong class="text-admin-ash">laporan</strong> di dalam tiap kasus — masing-masing punya judul,
                    slug, gambar, dan isi sendiri.
                </p>
            </div>

            <button wire:click="refreshList" wire:loading.attr="disabled" wire:target="refreshList"
                    class="admin-btn admin-btn--ghost admin-btn--sm shrink-0">
                <svg wire:loading.remove wire:target="refreshList" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="refreshList" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <span wire:loading.remove wire:target="refreshList">Refresh</span>
                <span wire:loading wire:target="refreshList">Memuat…</span>
            </button>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-admin-raised border border-admin-line text-admin-amber rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        {{-- ── Pencarian + sumber data ────────────────────────────────── --}}
        @php
            $apiUrl = config('services.deforestory_api.url');
            $apiHost = $apiUrl ? (parse_url($apiUrl, PHP_URL_HOST) ?: $apiUrl) : null;
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="relative w-full sm:w-80">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.150ms="search"
                       placeholder="Cari judul atau slug kartu…"
                       class="admin-input" style="padding-left: 2.25rem"
                       aria-label="Cari kartu kasus">
            </div>

            {{-- Kalau DEFORESTORY_API_URL kosong, daftar ini pasti kosong juga.
                 Menuliskannya sebagai peringatan lebih menolong daripada
                 "Sumber: API ()" yang tidak memberi tahu apa pun. --}}
            @if ($apiHost)
                <span class="inline-flex items-center gap-1.5 text-xs text-admin-muted" title="{{ $apiUrl }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Sumber: <span class="font-mono text-admin-ash">{{ $apiHost }}</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-xs badge-pending px-2.5 py-1 rounded-full font-medium">
                    DEFORESTORY_API_URL belum diatur
                </span>
            @endif
        </div>

        {{-- ── Tabel ──────────────────────────────────────────────────── --}}
        <div class="overflow-x-auto">
            <table class="admin-table min-w-full">
                <thead>
                    <tr>
                        <th class="w-[5.5rem]">Gambar</th>
                        <th>Kartu (API)</th>
                        <th class="w-64">UUID Simontini</th>
                        <th class="w-28">Kategori</th>
                        <th class="w-24">Tahun</th>
                        <th class="w-32">Laporan</th>
                        <th class="w-[19rem]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $row)
                        @php
                            $imgUrl = '';
                            if (!empty($row['image'])) {
                                $imgUrl = \Illuminate\Support\Str::startsWith($row['image'], ['http://','https://'])
                                    ? $row['image'] : asset('storage/' . $row['image']);
                            }
                            $laporanUrl = route('deforestory.laporan.index', ['caseSlug' => $row['slug']]);
                        @endphp
                        <tr wire:key="card-{{ $row['slug'] }}">
                            <td>
                                {{-- Placeholder duduk di belakang gambar, bukan sebagai cabang @else.
                                     Sebagian kartu API menunjuk berkas yang sudah tidak ada, dan
                                     tanpa ini yang muncul ikon gambar rusak bawaan peramban. --}}
                                <div class="relative w-20 h-12 rounded-md border border-admin-line bg-admin-base overflow-hidden">
                                    <span class="absolute inset-0 flex items-center justify-center text-admin-muted">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    @if ($imgUrl)
                                        <img src="{{ $imgUrl }}" alt=""
                                             onerror="this.style.display='none'"
                                             class="relative w-full h-full object-cover">
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="block font-medium text-admin-smoke">{{ $row['title'] }}</span>
                                <span class="mt-0.5 inline-block font-mono text-[11px] text-admin-muted">/{{ $row['slug'] }}</span>
                            </td>

                            <td>
                                @if (! empty($row['uuid']))
                                    <div class="flex items-center gap-2"
                                         x-data="{ copied:false, copy(){ navigator.clipboard?.writeText('{{ e($row['uuid']) }}'); this.copied=true; setTimeout(()=>this.copied=false,1200) } }">
                                        <code class="font-mono text-[11px] text-admin-ash truncate" title="{{ $row['uuid'] }}">{{ $row['uuid'] }}</code>
                                        <button type="button" @click="copy"
                                                class="shrink-0 text-admin-muted hover:text-admin-amber transition"
                                                :title="copied ? 'Tersalin' : 'Salin UUID'" aria-label="Salin UUID">
                                            <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <svg x-show="copied" x-cloak class="w-4 h-4 text-admin-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-admin-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @if ($row['category'])
                                    <span class="inline-block px-2 py-0.5 rounded-full bg-admin-base text-[11px] font-medium text-admin-ash">{{ $row['category'] }}</span>
                                @else
                                    <span class="text-admin-muted">—</span>
                                @endif
                            </td>

                            <td class="font-mono text-xs text-admin-ash whitespace-nowrap">{{ $row['year'] ?: '—' }}</td>

                            <td>
                                @if ($row['laporan_count'] > 0)
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full badge-active">{{ $row['laporan_count'] }} laporan</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full badge-draft">Belum ada</span>
                                @endif
                            </td>

                            <td>
                                {{-- Anchor DIBERI kelas tombol, bukan membungkus <button>:
                                     tombol di dalam tautan itu sarang interaktif yang tidak sah,
                                     dan itu pula yang membuat barisnya membungkus tak beraturan. --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ $laporanUrl }}" class="admin-btn admin-btn--primary admin-btn--sm">Kelola laporan</a>
                                    <a href="{{ route('deforestory.case.preview', ['caseSlug' => $row['slug'], 'locale' => 'id']) }}"
                                       target="_blank" rel="noopener"
                                       class="admin-btn admin-btn--ghost admin-btn--sm"
                                       title="Lihat arsip termasuk laporan yang masih draft">Preview</a>
                                    <button wire:click="deleteCard('{{ $row['slug'] }}')"
                                            wire:confirm="{{ $row['laporan_total'] > 0
                                                ? 'Kartu ini punya ' . $row['laporan_total'] . ' laporan. Hapus kartu beserta semua konten detailnya?'
                                                : 'Hapus kartu ini?' }}"
                                            class="admin-btn admin-btn--danger admin-btn--sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center">
                                <svg class="w-10 h-10 mx-auto text-admin-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4l-2 3h-4l-2-3H4"/>
                                </svg>
                                <p class="text-sm font-medium text-admin-ash">Tidak ada kartu dari API</p>
                                <p class="mt-1 text-xs text-admin-muted">
                                    Periksa <code class="font-mono">DEFORESTORY_API_URL</code>, lalu tekan Refresh.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $cases->links('vendor.pagination.admin') }}
        </div>
    </div>
</div>
