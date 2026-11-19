<div>
    @php
        // Panduan untuk peran pemilih; kosong saat Reference dibuka sebagai
        // halaman CMS biasa.
        $panduan = match (true) {
            ! $picker => null,
            $multiple && $pickerPurpose === 'before-after' => 'Pilih tepat 2 gambar: gambar pertama sebagai Before dan gambar kedua sebagai After.',
            $multiple => 'Pilih satu atau beberapa gambar untuk galeri GLightbox. Urutan galeri mengikuti urutan gambar yang dipilih, dan caption dapat disesuaikan sebelum dimasukkan.',
            default => 'Pilih gambar untuk dimasukkan ke editor. File selain gambar tetap dapat disimpan dan diunduh dari Reference, tetapi tidak dapat dimasukkan sebagai gambar.',
        };
    @endphp

    @if ($modal)
        {{-- Di dalam modal editor (layouts.picker) tidak ada kop CMS: dialog
             pembungkusnya sudah berjudul dan punya tombol tutup sendiri, dan
             bilah atas yang menempel hanya memakan tinggi iframe. Cukup
             panduan memilih dan tombol unggah. --}}
        <div class="mb-4 flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
            <div class="min-w-0 flex-1 basis-72">
                <h1 class="text-[15px] font-semibold text-admin-smoke">Reference</h1>
                <p class="mt-1 text-[13px] leading-normal text-admin-ash">
                    {{ $panduan ?? 'Simpan dan kelola gambar, GIF, video, PDF, serta dokumen Word.' }}
                </p>
            </div>
            <button type="button" wire:click="toggleForm" class="admin-btn admin-btn--ghost admin-btn--sm shrink-0">
                <x-admin.icon :name="$showForm ? 'close' : 'upload'" class="size-3.5" />
                {{ $showForm ? 'Tutup' : 'Unggah berkas' }}
            </button>
        </div>
    @else
        <x-admin.page-header title="Reference" description="Simpan dan kelola gambar, GIF, video, PDF, serta dokumen Word.">
            <button type="button" wire:click="toggleForm"
                    class="admin-btn admin-btn--sm {{ $showForm ? 'admin-btn--ghost' : 'admin-btn--primary' }}">
                <x-admin.icon :name="$showForm ? 'close' : 'upload'" class="size-3.5" />
                {{ $showForm ? 'Tutup' : 'Unggah berkas' }}
            </button>
        </x-admin.page-header>
    @endif

    <div @class(['admin-wrap admin-body' => ! $modal])>
    @if ($panduan && ! $modal)
        <div class="mb-4 flex items-start gap-2.5 rounded-md border border-admin-line bg-admin-inset px-3 py-2.5 text-[13px] text-admin-ash">
            <x-admin.icon name="info" class="mt-0.5 shrink-0 text-admin-muted" />
            <p>{{ $panduan }}</p>
        </div>
    @endif

    @if ($picker && $multiple)
        {{-- Panel pilihan galeri; atribut hidden-nya dinyalakan-matikan oleh
             cms-tinymce.js. Di halaman CMS ia berhenti di bawah bilah atas
             halaman (48px); di modal tidak ada bilah itu. --}}
        <div data-reference-gallery-panel
             data-reference-selection-limit="{{ $selectionLimit }}"
             data-reference-selection-exact="{{ $pickerPurpose === 'before-after' ? 2 : 0 }}"
             hidden
             class="admin-card sticky {{ $modal ? 'top-3' : 'top-[60px]' }} z-30 mb-4 border-admin-line-strong px-4 py-2.5 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-[13.5px] font-medium text-admin-smoke"><span class="admin-num" data-reference-gallery-count>0</span> gambar dipilih</p>
                <button type="button" data-reference-gallery-insert data-editor-key="{{ $editorKey }}"
                        class="admin-btn admin-btn--primary admin-btn--sm">
                    {{ $pickerPurpose === 'before-after' ? 'Masukkan Before/After' : 'Masukkan Galeri ke Editor' }}
                </button>
            </div>
        </div>
    @endif

    @if ($showForm)
        <form
            wire:submit="save"
            x-data="{
                uploading: false,
                progress: 0,
                finishTimer: null,
                startUpload() {
                    clearTimeout(this.finishTimer);
                    this.progress = 0;
                    this.uploading = true;
                },
                finishUpload() {
                    this.progress = 100;
                    clearTimeout(this.finishTimer);
                    this.finishTimer = setTimeout(() => { this.uploading = false }, 700);
                },
                stopUpload() {
                    clearTimeout(this.finishTimer);
                    this.uploading = false;
                    this.progress = 0;
                },
            }"
            class="mb-5"
        >
            <x-admin.card title="Unggah berkas" description="JPG, PNG, WebP, GIF, MP4, MOV, WebM, PDF, DOC, atau DOCX. Paling besar 50 MB per berkas.">
            <div class="grid gap-4">
                <div>
                    <label for="reference-berkas" class="admin-label">Berkas <span class="text-admin-ember" aria-hidden="true">*</span></label>
                    <input
                        id="reference-berkas"
                        type="file"
                        wire:model="image"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm,.pdf,.doc,.docx,image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        x-on:livewire-upload-start="startUpload()"
                        x-on:livewire-upload-finish="finishUpload()"
                        x-on:livewire-upload-error="stopUpload()"
                        x-on:livewire-upload-cancel="stopUpload()"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="admin-input"
                        @error('image') aria-invalid="true" @enderror
                    >
                    @error('image') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="uploading" x-cloak class="rounded-md border border-admin-line bg-admin-inset px-4 py-3.5" role="status" aria-live="polite">
                    <div class="flex items-center gap-2.5 text-[13px] font-medium text-admin-smoke">
                        <svg x-show="progress < 100" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <svg x-show="progress >= 100" x-cloak class="size-4 text-admin-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"></path>
                        </svg>
                        <span x-text="progress >= 100 ? 'Unggahan selesai, menampilkan pratinjau…' : 'Mengunggah dan menyiapkan pratinjau…'"></span>
                        <span class="admin-num ml-auto text-admin-muted" x-text="`${progress}%`"></span>
                    </div>
                    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-admin-tint-strong">
                        <div class="h-full rounded-full bg-admin-smoke transition-[width] duration-200" x-bind:style="`width: ${progress}%`"></div>
                    </div>
                </div>

                {{-- Pembungkusnya hanya ada kalau sudah ada berkas: kosong pun ia
                     tetap satu baris kisi dan menambah jarak di atas "Judul". --}}
                @if ($image)
                    <div x-show="!uploading">
                        <div wire:key="reference-preview-{{ md5($image->getFilename()) }}">
                            @if (str_starts_with($image->getMimeType() ?: '', 'image/'))
                                <img src="{{ $image->temporaryUrl() }}" alt="Pratinjau berkas" class="max-h-72 w-full rounded-md border border-admin-line bg-admin-inset object-contain">
                            @elseif (str_starts_with($image->getMimeType() ?: '', 'video/'))
                                <video src="{{ $image->temporaryUrl() }}" controls preload="metadata" class="max-h-72 w-full rounded-md border border-admin-line bg-admin-smoke object-contain"></video>
                            @elseif (($image->getMimeType() ?: '') === 'application/pdf')
                                <iframe src="{{ $image->temporaryUrl() }}" title="Pratinjau PDF" class="h-72 w-full rounded-md border border-admin-line bg-admin-surface"></iframe>
                            @else
                                <div class="rounded-md border border-admin-line bg-admin-inset px-3 py-2.5 text-[13px] text-admin-ash">
                                    Berkas dipilih: <span class="font-medium text-admin-smoke">{{ $image->getClientOriginalName() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    <label for="reference-judul" class="admin-label">Judul <span class="text-admin-ember" aria-hidden="true">*</span></label>
                    <input id="reference-judul" type="text" wire:model="title" placeholder="Nama atau judul berkas" class="admin-input"
                           @error('title') aria-invalid="true" @enderror>
                    @error('title') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="reference-alt" class="admin-label">Deskripsi / alt text <span class="text-admin-ember" aria-hidden="true">*</span></label>
                    <input id="reference-alt" type="text" wire:model="alt_text" placeholder="Deskripsi berkas, atau alt text untuk gambar" class="admin-input"
                           @error('alt_text') aria-invalid="true" @enderror>
                    @error('alt_text') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-slot:foot>
                <button type="submit" x-bind:disabled="uploading" wire:loading.attr="disabled" wire:target="save,image"
                        class="admin-btn admin-btn--primary admin-btn--sm ml-auto">
                    <span wire:loading.remove wire:target="save">Simpan berkas</span>
                    <span wire:loading wire:target="save">Menyimpan…</span>
                </button>
            </x-slot:foot>
            </x-admin.card>
        </form>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            @php
                $isImage = str_starts_with($item->mime_type ?: 'image/', 'image/');
                $isVideo = str_starts_with($item->mime_type ?: '', 'video/');
                $isPdf = ($item->mime_type ?: '') === 'application/pdf';
                $publik = ($item->disk ?: 'public') === 'public';
                // Dua bentuk URL, sengaja.
                //
                // $editorUrl yang ditanam ke dalam konten laporan: host-relatif,
                // mengikuti config filesystems disk public. Konten kaya tidak
                // pernah keluar lewat API Deforestory (yang dikirim hanya judul,
                // excerpt, dan gambar), jadi tidak ada yang butuh bentuk absolut
                // — sementara menanam nama host membuat gambar mati begitu situs
                // diakses lewat host lain. Itu persis yang bikin thumbnail LFM
                // dan media kejadian sempat kosong.
                //
                // $fileUrl untuk mata manusia: tombol "Salin URL" dan pratinjau
                // di kartu, jadi tetap absolut supaya bisa ditempel ke mana pun.
                $editorUrl = $publik
                    ? Storage::disk('public')->url($item->image_path)
                    : route('reference.download', $item->id);
                $fileUrl = url($editorUrl);
                $previewUrl = ($isVideo || $isPdf) ? route('reference.preview', $item->id) : null;
                $downloadUrl = route('reference.download', $item->id);
                $fileName = $item->original_name ?: basename($item->image_path);
                $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
            @endphp
            {{-- wire:key: kartu punya keadaan yang dipasang JS (tombol galeri
                 yang sedang terpilih), jadi saat pindah halaman Livewire harus
                 mengganti kartunya, bukan menambal kartu berkas lain. --}}
            <article wire:key="reference-{{ $item->id }}" class="admin-card flex flex-col overflow-hidden">
                <div class="flex aspect-[16/10] items-center justify-center overflow-hidden border-b border-admin-line bg-admin-inset">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="{{ $item->alt_text ?: $item->title ?: 'Reference image' }}" loading="lazy" class="h-full w-full object-cover">
                    @elseif ($isVideo)
                        <video src="{{ $previewUrl }}" controls preload="metadata" class="h-full w-full bg-admin-smoke object-contain" aria-label="{{ $item->alt_text ?: $item->title ?: 'Reference video' }}"></video>
                    @elseif ($isPdf)
                        <iframe src="{{ $previewUrl }}" title="{{ $item->alt_text ?: $item->title ?: 'Reference PDF' }}" loading="lazy" class="h-full w-full bg-admin-surface"></iframe>
                    @else
                        <div class="grid justify-items-center gap-1.5 text-admin-muted">
                            <x-admin.icon name="file" class="size-9" />
                            <span class="admin-num text-[12px] font-semibold text-admin-ash">{{ $extension }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-1 flex-col gap-3 p-3.5">
                    <div class="min-w-0">
                        <h2 class="truncate text-[13.5px] font-semibold text-admin-smoke" title="{{ $item->title }}">{{ $item->title ?: 'Tanpa judul' }}</h2>
                        <p class="mt-0.5 line-clamp-2 text-[12.5px] text-admin-ash">{{ $item->alt_text ?: 'Alt text belum diisi' }}</p>
                        <p class="admin-num mt-1.5 break-all text-[11.5px] text-admin-muted">{{ $fileName }}</p>
                        @if ($item->file_size)
                            <p class="admin-num mt-0.5 text-[11.5px] text-admin-muted">{{ number_format($item->file_size / 1024, 1) }} KB · {{ $item->mime_type ?: 'Tipe tidak diketahui' }}</p>
                        @endif
                    </div>

                    @if ($picker && $multiple && $isImage)
                        <div>
                            <label class="admin-label text-[12.5px]" for="gallery-caption-{{ $item->id }}">Caption galeri</label>
                            <input
                                id="gallery-caption-{{ $item->id }}"
                                type="text"
                                value="{{ $item->alt_text ?: $item->title }}"
                                data-reference-gallery-caption
                                data-reference-id="{{ $item->id }}"
                                class="admin-input h-8 min-h-8 py-0 text-[12.5px]"
                            >
                        </div>
                        {{-- Rupa terpilih digantungkan pada aria-pressed, dengan
                             "!": cms-tinymce.js masih menukar kelas warna lama
                             (bg-[#376A64] / bg-[#8B2A1A]) pada tombol ini tiap
                             diklik, dan utilitas itu tidak boleh menang. Teks
                             awal harus sama dengan yang ditulis JS saat pilihan
                             dibatalkan. --}}
                        <button
                            type="button"
                            data-reference-gallery-toggle
                            data-reference-id="{{ $item->id }}"
                            data-image-url="{{ $editorUrl }}"
                            data-image-title="{{ $item->title }}"
                            data-image-alt="{{ $item->alt_text }}"
                            aria-pressed="false"
                            class="admin-btn admin-btn--ghost admin-btn--sm w-full !bg-admin-surface aria-pressed:!border-admin-smoke aria-pressed:!bg-admin-smoke aria-pressed:!text-admin-on-accent"
                        >Tambah ke Galeri</button>
                    @elseif ($picker && $isImage)
                        <button
                            type="button"
                            data-reference-select
                            data-editor-key="{{ $editorKey }}"
                            data-image-url="{{ $editorUrl }}"
                            data-image-title="{{ $item->title }}"
                            data-image-alt="{{ $item->alt_text }}"
                            class="admin-btn admin-btn--primary admin-btn--sm w-full"
                        >Pilih untuk Editor</button>
                    @elseif ($picker)
                        <p class="rounded-md bg-admin-tint px-3 py-1.5 text-center text-[12px] text-admin-muted">Berkas ini bukan gambar</p>
                    @endif

                    <div x-data="{ copied: false }" class="mt-auto flex gap-1.5">
                        <input type="text" readonly value="{{ $fileUrl }}" aria-label="URL berkas" x-on:focus="$el.select()"
                               class="admin-input admin-num h-7 min-h-7 min-w-0 py-0 text-[11.5px] text-admin-ash">
                        <button type="button" x-on:click="navigator.clipboard.writeText(@js($fileUrl)); copied = true; setTimeout(() => copied = false, 1500)"
                                class="admin-btn admin-btn--ghost admin-btn--sm shrink-0">
                            <span x-show="!copied">Salin URL</span>
                            <span x-show="copied" x-cloak>Tersalin</span>
                        </button>
                    </div>
                </div>

                <div class="admin-card__foot justify-between px-2 py-1.5">
                    <a href="{{ $downloadUrl }}" class="admin-btn admin-btn--quiet admin-btn--sm">
                        <x-admin.icon name="download" class="size-3.5" />
                        Unduh
                    </a>
                    <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus file reference ini?"
                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                </div>
            </article>
        @empty
            <x-admin.empty title="Belum ada berkas" icon="image" class="admin-empty--dashed sm:col-span-2 lg:col-span-3">
                @if ($picker)
                    Belum ada gambar di Reference. Unggah dulu lewat tombol "Unggah berkas".
                @else
                    Unggah gambar, video, atau dokumen untuk dipakai di artikel dan laporan.
                @endif
            </x-admin.empty>
        @endforelse
    </div>

    {{-- Paginasinya dirancang sebagai kaki tabel; di bawah kisi kartu ia diberi
         bingkai sendiri, dan garis atas kakinya dibuang supaya tidak dobel
         dengan bingkai itu. livewire => true: di peran pemilih, tautan biasa
         akan memuat ulang iframe dan membuang gambar yang sudah dipilih. --}}
    @if ($items->hasPages())
        <div class="admin-table-frame mt-4 [&>nav]:border-t-0">
            {{ $items->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>
    @endif
    </div>

    <x-admin.toast />
</div>
