<div>
    <x-admin.page-header title="Deforestory"
                         description="Kartu kasus datang dari API dan tidak dibuat di sini. Yang diisi lewat CMS adalah laporan di dalam tiap kasus — masing-masing punya judul, slug, gambar, dan isi sendiri.">
        <button wire:click="bukaPanelKunci" type="button" class="admin-btn admin-btn--ghost admin-btn--sm"
                aria-expanded="{{ $panelKunci ? 'true' : 'false' }}">
            <x-admin.icon name="lock" class="size-3.5" />
            Password pratinjau
            <span class="admin-cap {{ $passwordSudahDiatur ? 'admin-cap--ok' : 'admin-cap--quiet' }}">
                {{ $passwordSudahDiatur ? 'aktif' : 'kosong' }}
            </span>
        </button>
        <button wire:click="refreshList" wire:loading.attr="disabled" wire:target="refreshList" type="button"
                class="admin-btn admin-btn--ghost admin-btn--sm">
            <x-admin.icon name="refresh" class="size-3.5" wire:loading.class="animate-spin" wire:target="refreshList" />
            <span wire:loading.remove wire:target="refreshList">Muat ulang</span>
            <span wire:loading wire:target="refreshList">Memuat…</span>
        </button>

        {{-- Di samping judul, bukan di bilah atas: bertiga dengan dua tombol
             di atas ia tidak muat di layar sempit. --}}
        <x-slot:aside>
            <a href="{{ route('deforestory.subscribers') }}" class="admin-btn admin-btn--ghost admin-btn--sm">
                <x-admin.icon name="mail" class="size-3.5" />
                Pelanggan
            </a>
        </x-slot:aside>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        {{-- Password pratinjau: satu untuk seluruh Deforestory. Laporan yang
             ditandai "Kunci pratinjau" menuntut password ini sebelum isinya
             terbuka bagi peninjau di luar CMS. --}}
        @if ($panelKunci)
            <x-admin.card class="mb-5" title="Password pratinjau Deforestory"
                          description="Berlaku untuk semua laporan yang dikunci. Bagikan bersama tautan pratinjau kepada peninjau yang tidak punya akun CMS. Mengganti password akan menutup kembali semua sesi yang sudah terbuka dengan password lama.">
                <div class="flex flex-wrap items-start gap-2">
                    <div class="w-full sm:w-80">
                        <input type="text" wire:model="passwordPratinjau" class="admin-input font-admin-mono"
                               placeholder="Minimal 6 karakter" aria-label="Password pratinjau"
                               @error('passwordPratinjau') aria-invalid="true" @enderror>
                        @error('passwordPratinjau') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <button wire:click="simpanPasswordPratinjau" type="button" class="admin-btn admin-btn--primary">Simpan</button>
                    @if ($passwordSudahDiatur)
                        <button wire:click="hapusPasswordPratinjau" type="button"
                                wire:confirm="Hapus password pratinjau? Laporan yang dikunci tidak akan bisa dibuka siapa pun sampai password baru diatur."
                                class="admin-btn admin-btn--danger">Hapus</button>
                    @endif
                </div>
            </x-admin.card>
        @endif

        {{-- ── Pencarian + sumber data ────────────────────────────────── --}}
        <div class="mb-4 flex flex-wrap items-center gap-x-3 gap-y-2">
            <x-admin.search wire:model.live.debounce.150ms="search" placeholder="Cari judul atau slug kartu…" aria-label="Cari kartu kasus" />

            {{-- Dulu di sini ada peringatan "DEFORESTORY_API_URL belum diatur".
                 Kartu sekarang didorong web Deforestory lewat webhook dan dibaca
                 dari tabel lokal, sedangkan kunci konfigurasi URL itu sudah tidak
                 ada, jadi peringatannya selalu menyala walau daftarnya terisi. --}}
            <span class="text-[12.5px] text-admin-muted sm:ml-auto">Sumber: kiriman webhook dari web Deforestory</span>
        </div>

        {{-- ── Tabel ──────────────────────────────────────────────────── --}}
        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-[5.5rem]">Gambar</th>
                            <th>Kartu (API)</th>
                            <th>UUID Simontini</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Laporan</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
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
                                    <div class="relative h-12 w-20 overflow-hidden rounded-md border border-admin-line bg-admin-inset">
                                        <span class="absolute inset-0 grid place-items-center text-admin-muted">
                                            <x-admin.icon name="image" />
                                        </span>
                                        @if ($imgUrl)
                                            <img src="{{ $imgUrl }}" alt=""
                                                 onerror="this.style.display='none'"
                                                 class="relative h-full w-full object-cover">
                                        @endif
                                    </div>
                                </td>

                                <td class="min-w-[220px]">
                                    <a href="{{ $laporanUrl }}" class="block font-medium hover:underline">{{ $row['title'] }}</a>
                                    <span class="mt-0.5 inline-block font-admin-mono text-[11.5px] text-admin-muted">/{{ $row['slug'] }}</span>
                                </td>

                                <td>
                                    @if (! empty($row['uuid']))
                                        <div class="flex items-center gap-1"
                                             x-data="{ copied:false, copy(){ navigator.clipboard?.writeText('{{ e($row['uuid']) }}'); this.copied=true; setTimeout(()=>this.copied=false,1200) } }">
                                            <code class="max-w-[11rem] truncate font-admin-mono text-[11.5px] text-admin-ash" title="{{ $row['uuid'] }}">{{ $row['uuid'] }}</code>
                                            <button type="button" @click="copy" class="admin-icon-btn size-6 shrink-0"
                                                    :title="copied ? 'Tersalin' : 'Salin UUID'" aria-label="Salin UUID">
                                                <x-admin.icon name="copy" class="size-3.5" x-show="!copied" />
                                                <x-admin.icon name="check" class="size-3.5 text-admin-green" x-show="copied" x-cloak />
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-admin-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($row['category'])
                                        <span class="admin-cap admin-cap--quiet">{{ $row['category'] }}</span>
                                    @else
                                        <span class="text-admin-muted">—</span>
                                    @endif
                                </td>

                                <td class="admin-num whitespace-nowrap text-admin-ash">{{ $row['year'] ?: '—' }}</td>

                                <td>
                                    @if ($row['laporan_count'] > 0)
                                        <span class="admin-cap admin-cap--ok">{{ $row['laporan_count'] }} laporan</span>
                                    @else
                                        <span class="admin-cap admin-cap--quiet">Belum ada</span>
                                    @endif
                                </td>

                                <td class="admin-cell-actions">
                                    {{-- Anchor DIBERI kelas tombol, bukan membungkus <button>:
                                         tombol di dalam tautan itu sarang interaktif yang tidak sah. --}}
                                    <a href="{{ $laporanUrl }}" class="admin-btn admin-btn--ghost admin-btn--sm">Kelola laporan</a>
                                    <a href="{{ route('deforestory.case.preview', ['caseSlug' => $row['slug'], 'locale' => 'id']) }}"
                                       target="_blank" rel="noopener"
                                       class="admin-btn admin-btn--quiet admin-btn--sm"
                                       title="Lihat arsip termasuk laporan yang masih draft">Pratinjau</a>
                                    <button wire:click="deleteCard('{{ $row['slug'] }}')" type="button"
                                            wire:confirm="{{ $row['laporan_total'] > 0
                                                ? 'Kartu ini punya ' . $row['laporan_total'] . ' laporan. Hapus kartu beserta semua konten detailnya?'
                                                : 'Hapus kartu ini?' }}"
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    @if ($search)
                                        <x-admin.empty title="Tidak ada kartu yang cocok" icon="search">
                                            Coba kata kunci lain, atau kosongkan pencarian.
                                        </x-admin.empty>
                                    @else
                                        <x-admin.empty title="Belum ada kartu kasus">
                                            Kartu muncul di sini setelah web Deforestory mengirimkannya ke CMS.
                                        </x-admin.empty>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tanpa ['livewire' => true], dan itu disengaja: komponen ini
                 memotong halamannya sendiri dari ?page= di URL (datanya array
                 dari API), bukan dari keadaan paginasi Livewire. Tombol
                 gotoPage() tidak akan memindahkan apa pun; yang bekerja adalah
                 tautan biasa yang memuat ulang halaman.

                 withPath: setelah komponen dirender ulang (mis. sehabis
                 mengetik di kotak cari), request()->url() menunjuk
                 /livewire/update, dan tanpa ini tautan halamannya ikut ke sana. --}}
            {{ $cases->withPath(route('deforestory.index'))->links('vendor.pagination.admin') }}
        </div>
    </div>

    <x-admin.toast />
</div>
