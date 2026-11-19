<div
    x-data="{
        previewOpen: false,
        previewUrl: '',
        previewTitle: '',
        shareOpen: false,
        shareTitle: '',
        publicUrl: '',
        embedUrl: '',
        copied: '',
        viewMode: window.localStorage.getItem('pasopati-data-visualization-view') || 'list',
        setViewMode(mode) {
            this.viewMode = mode;
            window.localStorage.setItem('pasopati-data-visualization-view', mode);
        },
        async copyText(value, type) {
            await navigator.clipboard.writeText(value);
            this.copied = type;
            setTimeout(() => { if (this.copied === type) this.copied = ''; }, 1600);
        }
    }"
    x-on:keydown.escape.window="previewOpen = false; shareOpen = false"
>
    <x-admin.page-header title="Data & Grafik" description="Kelola, publikasikan, dan sematkan grafik interaktif Pasopati.">
        <a href="{{ route('data-visualizations.create') }}" wire:navigate class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah grafik
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            {{-- Pilihan tampilan diingat per peramban (localStorage), bukan di
                 komponen: berpindah daftar/kartu tidak perlu bolak-balik ke server. --}}
            <div class="admin-tabs" role="group" aria-label="Pilih tampilan Data dan Grafik">
                <button type="button" x-on:click="setViewMode('list')" x-bind:aria-pressed="viewMode === 'list' ? 'true' : 'false'" title="Tampilan daftar">
                    <x-admin.icon name="table" class="size-3.5" />
                    Daftar
                </button>
                <button type="button" x-on:click="setViewMode('card')" x-bind:aria-pressed="viewMode === 'card' ? 'true' : 'false'" title="Tampilan kartu">
                    <x-admin.icon name="chart" class="size-3.5" />
                    Kartu
                </button>
            </div>

            <p class="ml-auto text-[12.5px] text-admin-muted">
                <span class="admin-num font-medium text-admin-ash">{{ $items->total() }}</span> grafik
            </p>
        </div>

        <div x-cloak x-show="viewMode === 'list'" class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Diperbarui</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $chartData = json_decode($item->chart_data ?? '', true) ?: ['columns' => ['Kategori', 'Nilai'], 'rows' => []];
                                $chartLabel = match ($item->chart_type ?? 'column') {
                                    'line' => 'Line Chart',
                                    'area' => 'Area Chart',
                                    'bar' => 'Bar Chart',
                                    'doughnut' => 'Donut Chart',
                                    'pie' => 'Pie Chart',
                                    'area-grid' => 'Grid of Area',
                                    'sankey' => 'Sankey / Alluvial',
                                    default => 'Column Chart',
                                };
                            @endphp
                            <tr wire:key="data-visualization-{{ $item->id }}">
                                <td class="min-w-[260px] max-w-md">
                                    <a href="{{ route('data-visualizations.edit', $item->id) }}" wire:navigate class="font-medium hover:underline">{{ $item->title }}</a>
                                    @if ($item->description)
                                        <p class="mt-0.5 line-clamp-2 text-[12.5px] leading-[1.5] text-admin-ash">{{ $item->description }}</p>
                                    @endif
                                    <p class="mt-1 text-[12px] text-admin-muted">{{ count($chartData['rows'] ?? []) }} baris · {{ max(0, count($chartData['columns'] ?? []) - 1) }} seri data</p>
                                </td>
                                <td><span class="admin-cap admin-cap--quiet">{{ $chartLabel }}</span></td>
                                <td>
                                    {{-- Capnya sekaligus sakelar. Menonaktifkan bukan sekadar
                                         merapikan daftar: route embed-nya jadi 404, jadi grafik
                                         yang sudah disisipkan ke laporan ikut berhenti tampil —
                                         karena itu arah yang ini minta konfirmasi. --}}
                                    @if ($item->is_active)
                                        <button type="button" wire:click="toggleStatus({{ $item->id }})"
                                                wire:confirm="Nonaktifkan grafik ini? Grafik yang sudah disisipkan ke laporan ikut berhenti tampil."
                                                title="Klik untuk menonaktifkan"
                                                class="admin-cap admin-cap--ok admin-cap--dot cursor-pointer hover:opacity-75">Aktif</button>
                                    @else
                                        <button type="button" wire:click="toggleStatus({{ $item->id }})"
                                                title="Klik untuk mengaktifkan"
                                                class="admin-cap admin-cap--quiet cursor-pointer hover:opacity-75">Nonaktif</button>
                                    @endif
                                </td>
                                <td class="admin-num whitespace-nowrap text-[12.5px] text-admin-ash">
                                    {{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('d M Y, H:i') }}
                                </td>
                                <td class="admin-cell-actions">
                                    <button type="button" x-on:click="previewTitle = @js($item->title); previewOpen = true; $nextTick(() => window.renderDataVisualizationChart?.($refs.previewChart, @js($item->chart_type ?? 'bar'), @js($chartData)))" class="admin-btn admin-btn--quiet admin-btn--sm">Pratinjau</button>
                                    @if ($item->is_active)
                                        <button
                                            type="button"
                                            x-on:click="shareTitle = @js($item->title); publicUrl = @js(route('data-visualizations.show', $item->id)); embedUrl = @js(route('data-visualizations.embed', $item->id)); copied = ''; shareOpen = true"
                                            class="admin-btn admin-btn--quiet admin-btn--sm"
                                        >Bagikan</button>
                                    @endif
                                    <a href="{{ route('data-visualizations.edit', $item->id) }}" wire:navigate class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus data & grafik ini?" class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <x-admin.empty title="Belum ada grafik" icon="chart">
                                        Susun grafik pertama dari tabel angka lewat tombol Tambah grafik.
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>

        <div x-cloak x-show="viewMode === 'card'">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($items as $item)
                    @php
                        $cardChartData = json_decode($item->chart_data ?? '', true) ?: ['columns' => ['Kategori', 'Nilai'], 'rows' => []];
                        $cardChartLabel = match ($item->chart_type ?? 'column') {
                            'line' => 'Line Chart',
                            'area' => 'Area Chart',
                            'bar' => 'Bar Chart',
                            'doughnut' => 'Donut Chart',
                            'pie' => 'Pie Chart',
                            'area-grid' => 'Grid of Area',
                            'sankey' => 'Sankey / Alluvial',
                            default => 'Column Chart',
                        };
                        $cardPreviewData = array_merge($cardChartData, [
                            'top_text' => '',
                            'bottom_text' => '',
                            'show_legend' => false,
                            'compact_preview' => true,
                        ]);
                    @endphp
                    <article wire:key="data-visualization-card-{{ $item->id }}" class="admin-card flex flex-col overflow-hidden">
                        {{-- Alas kanvasnya putih (bukan warna sisipan): grafik
                             dirancang untuk halaman publik yang berlatar putih. --}}
                        <div class="relative aspect-video w-full overflow-hidden border-b border-admin-line bg-admin-surface p-4">
                            <canvas
                                data-card-chart="{{ $item->id }}"
                                x-effect="if (viewMode === 'card') $nextTick(() => window.renderDataVisualizationChart?.($el, @js($item->chart_type ?? 'bar'), @js($cardPreviewData)))"
                                aria-label="Preview {{ $item->title }}"
                                role="img"
                            ></canvas>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <div class="mb-2.5 flex items-center justify-between gap-3">
                                <span class="admin-cap admin-cap--quiet">{{ $cardChartLabel }}</span>
                                @if ($item->is_active)
                                    <button type="button" wire:click="toggleStatus({{ $item->id }})"
                                            wire:confirm="Nonaktifkan grafik ini? Grafik yang sudah disisipkan ke laporan ikut berhenti tampil."
                                            title="Klik untuk menonaktifkan"
                                            class="admin-cap admin-cap--ok admin-cap--dot cursor-pointer hover:opacity-75">Aktif</button>
                                @else
                                    <button type="button" wire:click="toggleStatus({{ $item->id }})"
                                            title="Klik untuk mengaktifkan"
                                            class="admin-cap admin-cap--quiet cursor-pointer hover:opacity-75">Nonaktif</button>
                                @endif
                            </div>

                            <h2 class="line-clamp-2 text-[14.5px] leading-snug font-semibold text-admin-smoke">{{ $item->title }}</h2>
                            @if ($item->description)
                                <p class="mt-1 line-clamp-2 text-[12.5px] leading-[1.5] text-admin-ash">{{ $item->description }}</p>
                            @endif
                            <p class="mt-auto flex items-center justify-between gap-3 pt-3 text-[12px] text-admin-muted">
                                <span>{{ count($cardChartData['rows'] ?? []) }} baris · {{ max(0, count($cardChartData['columns'] ?? []) - 1) }} seri</span>
                                <span class="admin-num whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('d M Y') }}</span>
                            </p>
                        </div>

                        <div class="admin-card__foot gap-0.5 px-2 py-1.5">
                            <button type="button" x-on:click="previewTitle = @js($item->title); previewOpen = true; $nextTick(() => window.renderDataVisualizationChart?.($refs.previewChart, @js($item->chart_type ?? 'bar'), @js($cardChartData)))" class="admin-btn admin-btn--quiet admin-btn--sm">Pratinjau</button>
                            @if ($item->is_active)
                                <button
                                    type="button"
                                    x-on:click="shareTitle = @js($item->title); publicUrl = @js(route('data-visualizations.show', $item->id)); embedUrl = @js(route('data-visualizations.embed', $item->id)); copied = ''; shareOpen = true"
                                    class="admin-btn admin-btn--quiet admin-btn--sm"
                                >Bagikan</button>
                            @endif
                            <a href="{{ route('data-visualizations.edit', $item->id) }}" wire:navigate class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                            <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus data & grafik ini?" class="admin-btn admin-btn--quiet-danger admin-btn--sm ml-auto">Hapus</button>
                        </div>
                    </article>
                @empty
                    <x-admin.empty title="Belum ada grafik" icon="chart" class="admin-empty--dashed sm:col-span-2 xl:col-span-3">
                        Susun grafik pertama dari tabel angka lewat tombol Tambah grafik.
                    </x-admin.empty>
                @endforelse
            </div>

            {{-- Kisi kartu tidak punya bingkai tabel untuk ditumpangi, jadi
                 paginasinya diberi bingkai sendiri; garis atas kaki tabel
                 dibuang supaya tidak dobel dengan garis bingkai. --}}
            @if ($items->hasPages())
                <div class="admin-table-frame mt-4 [&>nav]:border-t-0">
                    {{ $items->links('vendor.pagination.admin', ['livewire' => true]) }}
                </div>
            @endif
        </div>
    </div>

    <div x-cloak x-show="previewOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" x-on:click.self="previewOpen = false">
        <div class="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl border border-admin-line bg-admin-surface shadow-2xl" role="dialog" aria-modal="true" aria-label="Preview data dan grafik">
            <div class="flex items-center justify-between gap-4 border-b border-admin-line py-2.5 pr-3 pl-5">
                <h2 class="truncate text-[14px] font-semibold text-admin-smoke" x-text="previewTitle"></h2>
                <button type="button" x-on:click="previewOpen = false" class="admin-icon-btn shrink-0" aria-label="Tutup preview">
                    <x-admin.icon name="close" />
                </button>
            </div>
            <div class="relative min-h-[420px] w-full bg-admin-surface p-6">
                <canvas x-ref="previewChart" aria-label="Preview grafik" role="img"></canvas>
            </div>
        </div>
    </div>

    <div x-cloak x-show="shareOpen" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 p-4" x-on:click.self="shareOpen = false">
        <div class="w-full max-w-2xl overflow-hidden rounded-xl border border-admin-line bg-admin-surface shadow-2xl" role="dialog" aria-modal="true" aria-label="Bagikan data dan grafik">
            <div class="flex items-center justify-between gap-4 border-b border-admin-line py-3 pr-3 pl-5">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Bagikan grafik</p>
                    <h2 class="truncate text-[14px] font-semibold text-admin-smoke" x-text="shareTitle"></h2>
                </div>
                <button type="button" x-on:click="shareOpen = false" class="admin-icon-btn shrink-0" aria-label="Tutup">
                    <x-admin.icon name="close" />
                </button>
            </div>

            <div class="grid gap-5 p-5">
                <div>
                    <label for="grafik-tautan-publik" class="admin-label">Tautan publik</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input id="grafik-tautan-publik" type="text" x-bind:value="publicUrl" readonly class="admin-input min-w-0 flex-1 bg-admin-inset">
                        <a x-bind:href="publicUrl" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost shrink-0">
                            <x-admin.icon name="external" class="size-3.5" />
                            Buka
                        </a>
                        <button type="button" x-on:click="copyText(publicUrl, 'public')" class="admin-btn admin-btn--primary shrink-0">
                            <span x-text="copied === 'public' ? 'Tersalin' : 'Salin'"></span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="grafik-kode-embed" class="admin-label">Kode embed iframe</label>
                    <textarea
                        id="grafik-kode-embed"
                        readonly
                        rows="4"
                        x-bind:value="`<div style=&quot;position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;&quot;><iframe src=&quot;${embedUrl}&quot; width=&quot;100%&quot; height=&quot;100%&quot; frameborder=&quot;0&quot; scrolling=&quot;no&quot; loading=&quot;lazy&quot; title=&quot;${shareTitle.replaceAll('&quot;', '')}&quot; style=&quot;position:absolute;inset:0;width:100%;height:100%;border:0;overflow:hidden;&quot;></iframe></div>`"
                        class="admin-input bg-admin-inset font-admin-mono text-[12px] leading-5"
                    ></textarea>
                    <div class="mt-2 flex justify-end">
                        <button
                            type="button"
                            x-on:click="copyText(`<div style=&quot;position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;&quot;><iframe src=&quot;${embedUrl}&quot; width=&quot;100%&quot; height=&quot;100%&quot; frameborder=&quot;0&quot; scrolling=&quot;no&quot; loading=&quot;lazy&quot; title=&quot;${shareTitle.replaceAll('&quot;', '')}&quot; style=&quot;position:absolute;inset:0;width:100%;height:100%;border:0;overflow:hidden;&quot;></iframe></div>`, 'embed')"
                            class="admin-btn admin-btn--ghost"
                        >
                            <x-admin.icon name="copy" class="size-3.5" />
                            <span x-text="copied === 'embed' ? 'Kode tersalin' : 'Salin kode embed'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-admin.toast />
</div>
