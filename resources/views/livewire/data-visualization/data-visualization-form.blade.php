<div
    x-data="{
        activeTab: 'data',
        sections: {
            basic: true,
            typography: true,
            legend: true,
            axis: true,
            workspace: true
        }
    }"
    x-on:data-visualization-preview.window="$nextTick(() => window.renderDataVisualizationChart?.($refs.formChartPreview, $event.detail.chartType, $event.detail.chartData))"
>
    <x-admin.page-header :title="$visualizationId ? 'Ubah grafik' : 'Grafik baru'"
                         description="Masukkan data langsung melalui tabel, kemudian pilih bentuk grafiknya."
                         :crumbs="[['Data & Grafik', route('data-visualizations.index')]]"
                         :back="route('data-visualizations.index')">
        <a href="{{ route('data-visualizations.index') }}" wire:navigate class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-grafik" wire:loading.attr="disabled" wire:target="save" class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $visualizationId ? 'Simpan perubahan' : 'Simpan grafik' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        {{-- Tiap seksi bisa dilipat, jadi galat di seksi yang sedang tertutup
             tidak terlihat di samping isiannya. Ringkasan ini menampilkan
             semuanya sekaligus. --}}
        @if ($errors->any())
            <div class="admin-alert mb-5" role="alert">
                <div>
                    <p class="font-medium">Grafik belum tersimpan.</p>
                    <ul class="mt-1 list-disc pl-4">
                        @foreach ($errors->all() as $galat)
                            <li>{{ $galat }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Rel samping di form ini TIDAK menempel: isinya (tipografi, legend,
             skala) lebih tinggi dari layar, dan kolom menempel yang lebih
             tinggi dari layar membuat bagian bawahnya tak terjangkau. --}}
        <form id="form-grafik" wire:submit="save" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="grid min-w-0 gap-5">
                <section class="admin-card">
                <div class="admin-card__head" x-bind:class="sections.basic || 'border-b-0'">
                    <button type="button" x-on:click="sections.basic = !sections.basic" x-bind:aria-expanded="sections.basic" class="flex min-w-0 flex-1 items-start gap-2.5 text-left">
                        <span class="mt-[3px] shrink-0 text-admin-muted transition-transform" x-bind:class="sections.basic || '-rotate-90'" aria-hidden="true">
                            <x-admin.icon name="down" class="size-3.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[14px] leading-[1.35] font-semibold text-admin-smoke">Informasi dasar</span>
                            <span class="mt-0.5 block text-[12.5px] text-admin-muted">Identitas dan jenis grafik yang akan ditampilkan.</span>
                        </span>
                    </button>
                </div>
                <div x-show="sections.basic" class="admin-card__body grid gap-4">
                    <div>
                        <label for="grafik-judul" class="admin-label">Judul <span class="text-admin-ember">*</span></label>
                        <input id="grafik-judul" type="text" wire:model="title" placeholder="Contoh: Tren Deforestasi Indonesia 2020–2026"
                               class="admin-input" @error('title') aria-invalid="true" @enderror>
                        @error('title') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="grafik-deskripsi" class="admin-label">Deskripsi</label>
                        <textarea id="grafik-deskripsi" wire:model="description" rows="3" placeholder="Keterangan singkat mengenai data atau grafik"
                                  class="admin-input" @error('description') aria-invalid="true" @enderror></textarea>
                        @error('description') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <fieldset>
                        <legend class="admin-label">Jenis grafik <span class="text-admin-ember">*</span></legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ([
                                'line' => 'Line Chart',
                                'area' => 'Area Chart',
                                'bar' => 'Bar Chart',
                                'column' => 'Column Chart',
                                'doughnut' => 'Donut Chart',
                                'pie' => 'Pie Chart',
                                'area-grid' => 'Grid of Area',
                            ] as $chartValue => $chartLabel)
                                <label wire:key="chart-type-{{ $chartValue }}" class="relative cursor-pointer">
                                    <input type="radio" wire:model.live="chart_type" value="{{ $chartValue }}" class="peer sr-only">
                                    {{-- Pilihan aktif ditandai garis tinta + alas tipis, bukan
                                         bidang penuh: sketsanya tetap terbaca di kedua keadaan. --}}
                                    <span class="flex min-h-[108px] flex-col justify-between gap-2.5 rounded-md border border-admin-line-strong bg-admin-surface p-3 text-left text-admin-muted transition hover:border-admin-smoke/40 hover:text-admin-ash peer-checked:border-admin-smoke peer-checked:bg-admin-tint peer-checked:text-admin-smoke peer-checked:shadow-[inset_0_0_0_1px_var(--color-admin-smoke)] peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-admin-focus">
                                        <span class="block text-[13px] leading-tight font-medium text-admin-smoke">{{ $chartLabel }}</span>
                                        <span class="block h-12 w-full" aria-hidden="true">
                                    @switch($chartValue)
                                        @case('line')
                                            <svg class="h-full w-full" viewBox="0 0 160 64" fill="none" stroke="currentColor"><path d="M4 56H156M12 48 44 32 76 40 110 13 148 24" stroke-width="3"/><circle cx="12" cy="48" r="3" fill="currentColor"/><circle cx="44" cy="32" r="3" fill="currentColor"/><circle cx="76" cy="40" r="3" fill="currentColor"/><circle cx="110" cy="13" r="3" fill="currentColor"/><circle cx="148" cy="24" r="3" fill="currentColor"/></svg>
                                            @break
                                        @case('area')
                                            <svg class="h-full w-full" viewBox="0 0 160 64"><path d="M4 56 14 46 48 28 80 39 113 14 148 25 156 56Z" fill="currentColor" opacity=".24"/><path d="M14 46 48 28 80 39 113 14 148 25" fill="none" stroke="currentColor" stroke-width="3"/><path d="M4 56H156" stroke="currentColor" opacity=".4"/></svg>
                                            @break
                                        @case('bar')
                                            <svg class="h-full w-full" viewBox="0 0 160 64" fill="currentColor"><rect x="8" y="7" width="93" height="10"/><rect x="8" y="27" width="137" height="10" opacity=".72"/><rect x="8" y="47" width="64" height="10" opacity=".45"/></svg>
                                            @break
                                        @case('column')
                                            <svg class="h-full w-full" viewBox="0 0 160 64" fill="currentColor"><rect x="15" y="31" width="22" height="27" opacity=".45"/><rect x="51" y="12" width="22" height="46"/><rect x="87" y="23" width="22" height="35" opacity=".72"/><rect x="123" y="39" width="22" height="19" opacity=".38"/></svg>
                                            @break
                                        @case('doughnut')
                                            <svg class="h-full w-full" viewBox="0 0 160 64"><circle cx="80" cy="32" r="23" fill="none" stroke="currentColor" stroke-width="12" opacity=".2"/><path d="M80 9a23 23 0 0 1 20 34" fill="none" stroke="currentColor" stroke-width="12"/><path d="M100 43a23 23 0 0 1-35 7" fill="none" stroke="currentColor" stroke-width="12" opacity=".62"/></svg>
                                            @break
                                        @case('pie')
                                            <svg class="h-full w-full" viewBox="0 0 160 64"><circle cx="80" cy="32" r="25" fill="currentColor" opacity=".22"/><path d="M80 32V7a25 25 0 0 1 21 39Z" fill="currentColor"/><path d="m80 32 21 14a25 25 0 0 1-39 4Z" fill="currentColor" opacity=".62"/></svg>
                                            @break
                                        @case('area-grid')
                                            <svg class="h-full w-full" viewBox="0 0 160 64" fill="currentColor"><rect x="20" y="8" width="32" height="20"/><rect x="57" y="8" width="48" height="20" opacity=".7"/><rect x="110" y="8" width="30" height="20" opacity=".4"/><rect x="20" y="33" width="50" height="23" opacity=".55"/><rect x="75" y="33" width="28" height="23" opacity=".32"/><rect x="108" y="33" width="32" height="23" opacity=".82"/></svg>
                                            @break
                                    @endswitch
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('chart_type') <p class="admin-error">{{ $message }}</p> @enderror
                    </fieldset>
                </div>
                </section>

                <section class="admin-card">
                <div class="admin-card__head" x-bind:class="sections.workspace || 'border-b-0'">
                    <button type="button" x-on:click="sections.workspace = !sections.workspace" x-bind:aria-expanded="sections.workspace" class="flex min-w-0 flex-1 items-start gap-2.5 text-left">
                        <span class="mt-[3px] shrink-0 text-admin-muted transition-transform" x-bind:class="sections.workspace || '-rotate-90'" aria-hidden="true">
                            <x-admin.icon name="down" class="size-3.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[14px] leading-[1.35] font-semibold text-admin-smoke">Lembar kerja</span>
                            <span class="mt-0.5 block text-[12.5px] text-admin-muted">Kelola isi tabel dan periksa hasilnya langsung.</span>
                        </span>
                    </button>
                    <div x-show="sections.workspace" class="admin-tabs shrink-0" role="tablist" aria-label="Data dan preview grafik">
                        <button
                            type="button"
                            role="tab"
                            x-on:click="activeTab = 'data'"
                            x-bind:aria-selected="activeTab === 'data'"
                            x-bind:class="activeTab === 'data' ? 'active' : ''"
                        >
                            <x-admin.icon name="table" class="size-3.5" />
                            Data
                        </button>
                        <button
                            type="button"
                            role="tab"
                            x-on:click="activeTab = 'preview'; $nextTick(() => $wire.preview())"
                            x-bind:aria-selected="activeTab === 'preview'"
                            x-bind:class="activeTab === 'preview' ? 'active' : ''"
                        >
                            <x-admin.icon name="chart" class="size-3.5" />
                            Pratinjau
                        </button>
                    </div>
                </div>

                <div x-show="sections.workspace">
                    <div x-show="activeTab === 'data'" role="tabpanel" class="admin-card__body">
                        <div class="mb-3 flex justify-end gap-1.5">
                            <button type="button" wire:click="addRow" class="admin-btn admin-btn--ghost admin-btn--sm">
                                <x-admin.icon name="plus" class="size-3.5" />
                                Baris
                            </button>
                            <button type="button" wire:click="addColumn" class="admin-btn admin-btn--ghost admin-btn--sm">
                                <x-admin.icon name="plus" class="size-3.5" />
                                Kolom
                            </button>
                        </div>

                        {{-- Kolom A memuat label (kategori/tahun), kolom sesudahnya
                             seri angka; baris 1 memuat nama kolom. Keduanya dibedakan
                             lewat alas abu yang lebih pekat, bukan lewat rona.

                             Cincin fokus sel sengaja #376A64: harus sewarna dengan
                             garis seleksi .pasopati-spreadsheet-cell.is-selected di
                             app.css, yang tampil bersamaan saat sel diklik. --}}
                        <div data-spreadsheet class="overflow-x-auto rounded-md border border-admin-line-strong bg-admin-surface">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                    <tr class="h-8">
                                        <th class="w-10 min-w-10 border-r border-b border-admin-line-strong bg-admin-tint"><span class="sr-only">Nomor baris</span></th>
                                        @foreach ($columns as $columnIndex => $column)
                                            <th
                                                wire:key="column-letter-{{ $columnIndex }}"
                                                class="group relative min-w-32 border-r border-b border-admin-line-strong bg-admin-tint px-2 py-1.5 text-center text-[12px] font-medium text-admin-ash last:border-r-0"
                                            >
                                                {{ chr(65 + $columnIndex) }}
                                                <span class="ml-1 text-[11px] font-normal text-admin-muted">{{ $columnIndex === 0 ? 'label' : 'seri' }}</span>
                                                @if ($columnIndex > 0 && count($columns) > 2)
                                                    <button type="button" wire:click="removeColumn({{ $columnIndex }})" wire:confirm="Hapus kolom beserta seluruh datanya?" class="absolute top-1/2 right-0.5 hidden size-6 -translate-y-1/2 place-items-center rounded text-admin-ember hover:bg-admin-tint-strong group-hover:grid focus-visible:grid" aria-label="Hapus kolom {{ chr(65 + $columnIndex) }}">
                                                        <x-admin.icon name="close" class="size-3.5" />
                                                    </button>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="h-9">
                                        <th class="admin-num border-r border-b border-admin-line-strong bg-admin-tint px-2 text-center text-[12px] font-medium text-admin-muted">1</th>
                                        @foreach ($columns as $columnIndex => $column)
                                            <td data-spreadsheet-cell data-row="0" data-column="{{ $columnIndex }}" class="pasopati-spreadsheet-cell border-r border-b border-admin-line-strong bg-admin-tint-strong p-0 last:border-r-0">
                                                <input type="text" wire:model="columns.{{ $columnIndex }}" aria-label="Nama kolom {{ chr(65 + $columnIndex) }}" class="h-9 w-full min-w-32 border-0 bg-transparent px-2 text-[12.5px] font-semibold text-admin-smoke outline-none ring-inset focus:ring-2 focus:ring-[#376A64]">
                                                @error("columns.$columnIndex") <p class="px-2 pb-1 text-[11.5px] font-medium text-admin-ember">{{ $message }}</p> @enderror
                                            </td>
                                        @endforeach
                                    </tr>

                                    @foreach ($rows as $rowIndex => $row)
                                        <tr wire:key="data-row-{{ $rowIndex }}" class="group h-9">
                                            <th class="admin-num relative border-r border-b border-admin-line-strong bg-admin-tint px-2 text-center text-[12px] font-medium text-admin-muted group-last:border-b-0">
                                                <span class="group-hover:hidden">{{ $rowIndex + 2 }}</span>
                                                <button type="button" wire:click="removeRow({{ $rowIndex }})" @disabled(count($rows) <= 1) class="hidden size-6 rounded text-admin-ember hover:bg-admin-tint-strong group-hover:inline-flex group-hover:items-center group-hover:justify-center disabled:cursor-not-allowed disabled:opacity-30" aria-label="Hapus baris {{ $rowIndex + 2 }}">
                                                    <x-admin.icon name="close" class="size-3.5" />
                                                </button>
                                            </th>
                                            @foreach ($columns as $columnIndex => $column)
                                                <td data-spreadsheet-cell data-row="{{ $rowIndex + 1 }}" data-column="{{ $columnIndex }}" class="pasopati-spreadsheet-cell border-r border-b border-admin-line-strong p-0 last:border-r-0 group-last:border-b-0">
                                                    <input
                                                        type="text"
                                                        wire:model="rows.{{ $rowIndex }}.{{ $columnIndex }}"
                                                        aria-label="Baris {{ $rowIndex + 2 }} kolom {{ chr(65 + $columnIndex) }}"
                                                        class="admin-num h-9 w-full min-w-32 border-0 bg-admin-surface px-2 text-[12.5px] text-admin-smoke outline-none ring-inset focus:ring-2 focus:ring-[#376A64]"
                                                    >
                                                    @error("rows.$rowIndex.$columnIndex") <p class="px-2 pb-1 text-[11.5px] font-medium text-admin-ember">{{ $message }}</p> @enderror
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('rows') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Alas pratinjau putih: sama dengan halaman publik tempat
                         grafik ini nanti tampil. --}}
                    <div x-cloak x-show="activeTab === 'preview'" role="tabpanel" class="rounded-b-[10px] bg-admin-surface p-4 sm:p-6">
                        <div class="relative min-h-[460px] w-full">
                            <canvas x-ref="formChartPreview" aria-label="Preview grafik dari data tabel" role="img"></canvas>
                        </div>
                    </div>
                </div>
                </section>
            </div>

            <div class="grid min-w-0 gap-5">
                <x-admin.card title="Publikasi">
                    <label class="flex cursor-pointer items-center gap-2.5 text-[13.5px] text-admin-smoke">
                        <input type="checkbox" wire:model="is_active" class="size-4">
                        Aktif dan siap ditampilkan
                    </label>
                    <p class="admin-hint">Grafik nonaktif tidak muncul di pemilih editor, dan sematannya berhenti tampil.</p>
                    @error('is_active') <p class="admin-error">{{ $message }}</p> @enderror
                </x-admin.card>

                <section class="admin-card">
                <div class="admin-card__head" x-bind:class="sections.typography || 'border-b-0'">
                    <button type="button" x-on:click="sections.typography = !sections.typography" x-bind:aria-expanded="sections.typography" class="flex min-w-0 flex-1 items-start gap-2.5 text-left">
                        <span class="mt-[3px] shrink-0 text-admin-muted transition-transform" x-bind:class="sections.typography || '-rotate-90'" aria-hidden="true">
                            <x-admin.icon name="down" class="size-3.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[14px] leading-[1.35] font-semibold text-admin-smoke">Teks &amp; tipografi</span>
                            <span class="mt-0.5 block text-[12.5px] text-admin-muted">Judul dan catatan pendukung grafik.</span>
                        </span>
                    </button>
                </div>
                    <div x-show="sections.typography" class="admin-card__body grid gap-5 [&>div+div]:border-t [&>div+div]:border-admin-line [&>div+div]:pt-5">
                        <div>
                            <label for="grafik-top-teks" class="admin-label">Teks atas <span class="font-normal text-admin-muted">(opsional)</span></label>
                            <input
                                id="grafik-top-teks"
                                type="text"
                                wire:model.live.debounce.300ms="top_text"
                                placeholder="Contoh: Luas deforestasi per bulan"
                                class="admin-input"
                                @error('top_text') aria-invalid="true" @enderror
                            >
                            <p class="admin-hint">Kosongkan jika tidak ingin menampilkan judul di atas grafik.</p>
                            @error('top_text') <p class="admin-error">{{ $message }}</p> @enderror
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label for="grafik-top-posisi" class="admin-label">Posisi</label>
                                    <select id="grafik-top-posisi" wire:model.live="top_align" class="admin-input">
                                        <option value="left">Kiri</option>
                                        <option value="center">Tengah</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="grafik-top-ukuran" class="admin-label">Ukuran huruf</label>
                                    <input id="grafik-top-ukuran" type="number" min="10" max="72" wire:model.live.debounce.300ms="top_font_size" class="admin-input admin-num">
                                </div>
                                <div>
                                    <label for="grafik-top-tebal" class="admin-label">Ketebalan</label>
                                    <select id="grafik-top-tebal" wire:model.live="top_font_weight" class="admin-input">
                                        <option value="normal">Regular</option>
                                        <option value="600">Semibold</option>
                                        <option value="bold">Bold</option>
                                        <option value="900">Black</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="grafik-top-huruf" class="admin-label">Jenis huruf</label>
                                    <select id="grafik-top-huruf" wire:model.live="top_font_family" class="admin-input">
                                        <option value="Poppins">Poppins</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Helvetica">Helvetica</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Verdana">Verdana</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                </div>
                            </div>
                            <label class="mt-3 flex cursor-pointer items-center gap-2 text-[13px] text-admin-ash">
                                <input type="checkbox" wire:model.live="top_italic" class="size-4">
                                <span class="italic">Italic</span>
                            </label>
                        </div>

                        <div>
                            <label for="grafik-bottom-teks" class="admin-label">Teks bawah <span class="font-normal text-admin-muted">(opsional)</span></label>
                            <input
                                id="grafik-bottom-teks"
                                type="text"
                                wire:model.live.debounce.300ms="bottom_text"
                                placeholder="Contoh: Sumber: Simontini, 2026"
                                class="admin-input"
                                @error('bottom_text') aria-invalid="true" @enderror
                            >
                            <p class="admin-hint">Kosongkan jika tidak ingin menampilkan catatan di bawah grafik.</p>
                            @error('bottom_text') <p class="admin-error">{{ $message }}</p> @enderror
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label for="grafik-bottom-posisi" class="admin-label">Posisi</label>
                                    <select id="grafik-bottom-posisi" wire:model.live="bottom_align" class="admin-input">
                                        <option value="left">Kiri</option>
                                        <option value="center">Tengah</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="grafik-bottom-ukuran" class="admin-label">Ukuran huruf</label>
                                    <input id="grafik-bottom-ukuran" type="number" min="10" max="72" wire:model.live.debounce.300ms="bottom_font_size" class="admin-input admin-num">
                                </div>
                                <div>
                                    <label for="grafik-bottom-tebal" class="admin-label">Ketebalan</label>
                                    <select id="grafik-bottom-tebal" wire:model.live="bottom_font_weight" class="admin-input">
                                        <option value="normal">Regular</option>
                                        <option value="600">Semibold</option>
                                        <option value="bold">Bold</option>
                                        <option value="900">Black</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="grafik-bottom-huruf" class="admin-label">Jenis huruf</label>
                                    <select id="grafik-bottom-huruf" wire:model.live="bottom_font_family" class="admin-input">
                                        <option value="Poppins">Poppins</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Helvetica">Helvetica</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Verdana">Verdana</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                </div>
                            </div>
                            <label class="mt-3 flex cursor-pointer items-center gap-2 text-[13px] text-admin-ash">
                                <input type="checkbox" wire:model.live="bottom_italic" class="size-4">
                                <span class="italic">Italic</span>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="admin-card">
                <div class="admin-card__head" x-bind:class="sections.legend || 'border-b-0'">
                    <button type="button" x-on:click="sections.legend = !sections.legend" x-bind:aria-expanded="sections.legend" class="flex min-w-0 flex-1 items-start gap-2.5 text-left">
                        <span class="mt-[3px] shrink-0 text-admin-muted transition-transform" x-bind:class="sections.legend || '-rotate-90'" aria-hidden="true">
                            <x-admin.icon name="down" class="size-3.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[14px] leading-[1.35] font-semibold text-admin-smoke">Legend</span>
                            <span class="mt-0.5 block text-[12.5px] text-admin-muted">Nama seri data dan posisinya.</span>
                        </span>
                    </button>
                </div>
                    <div x-show="sections.legend" class="admin-card__body grid gap-4">
                        <label class="flex cursor-pointer items-center gap-2.5 text-[13.5px] text-admin-smoke">
                            <input
                                type="checkbox"
                                wire:model.live="show_legend"
                                class="size-4"
                            >
                            Tampilkan nama seri data
                        </label>

                        <div>
                            <label for="grafik-legend-posisi" class="admin-label">Posisi legend</label>
                            <select
                                id="grafik-legend-posisi"
                                wire:model.live="legend_position"
                                class="admin-input"
                            >
                                <option value="top">Di atas grafik</option>
                                <option value="bottom">Di bawah grafik</option>
                            </select>
                            @error('legend_position') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                @if (in_array($chart_type, ['line', 'area', 'bar', 'column'], true))
                    <section class="admin-card">
                <div class="admin-card__head" x-bind:class="sections.axis || 'border-b-0'">
                    <button type="button" x-on:click="sections.axis = !sections.axis" x-bind:aria-expanded="sections.axis" class="flex min-w-0 flex-1 items-start gap-2.5 text-left">
                        <span class="mt-[3px] shrink-0 text-admin-muted transition-transform" x-bind:class="sections.axis || '-rotate-90'" aria-hidden="true">
                            <x-admin.icon name="down" class="size-3.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[14px] leading-[1.35] font-semibold text-admin-smoke">Skala sumbu nilai</span>
                            <span class="mt-0.5 block text-[12.5px] text-admin-muted">Jumlah baris dan angka yang tampil pada sumbu.</span>
                        </span>
                    </button>
                </div>
                        <div x-show="sections.axis" class="admin-card__body grid gap-4">
                            <div>
                                <label for="grafik-sumbu-baris" class="admin-label">Jumlah baris angka</label>
                                <input id="grafik-sumbu-baris" type="number" min="2" max="20" wire:model.live.debounce.300ms="value_axis_tick_count" placeholder="Otomatis"
                                       class="admin-input admin-num" @error('value_axis_tick_count') aria-invalid="true" @enderror>
                                <p class="admin-hint">Isi 2–20 baris. Kosongkan untuk skala otomatis.</p>
                                @error('value_axis_tick_count') <p class="admin-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="flex cursor-pointer items-center gap-2.5 text-[13.5px] text-admin-smoke">
                                    <input type="checkbox" wire:model.live="show_value_axis_line" class="size-4">
                                    Tampilkan garis sumbu
                                </label>
                                <p class="admin-hint">Matikan untuk menyembunyikan garis vertikal di sisi angka.</p>
                            </div>

                            @if (filled($value_axis_tick_count) && count($value_axis_ticks))
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($value_axis_ticks as $tickIndex => $tickValue)
                                        <div wire:key="value-axis-tick-{{ $tickIndex }}">
                                            <label for="grafik-sumbu-nilai-{{ $tickIndex }}" class="admin-label">Baris {{ $tickIndex + 1 }}</label>
                                            <input id="grafik-sumbu-nilai-{{ $tickIndex }}" type="number" step="any" wire:model.live.debounce.300ms="value_axis_ticks.{{ $tickIndex }}" placeholder="Nilai" class="admin-input admin-num">
                                            @error("value_axis_ticks.$tickIndex") <p class="admin-error">{{ $message }}</p> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endif
            </div>
        </form>
    </div>

    <x-admin.toast />
</div>
