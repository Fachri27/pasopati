<div>
    @if ($picker)
        <div class="mb-6 rounded-lg border border-admin-line bg-admin-surface px-4 py-3 text-sm text-admin-ash">
            @if ($multiple)
                @if ($pickerPurpose === 'before-after')
                    Pilih tepat 2 gambar: gambar pertama sebagai Before dan gambar kedua sebagai After.
                @else
                    Pilih satu atau beberapa gambar untuk galeri GLightbox. Urutan galeri mengikuti urutan gambar yang dipilih, dan caption dapat disesuaikan sebelum dimasukkan.
                @endif
            @else
                Pilih gambar untuk dimasukkan ke editor. File selain gambar tetap dapat disimpan dan diunduh dari Reference, tetapi tidak dapat dimasukkan sebagai gambar.
            @endif
        </div>
    @endif

    @if ($picker && $multiple)
        <div data-reference-gallery-panel
             data-reference-selection-limit="{{ $selectionLimit }}"
             data-reference-selection-exact="{{ $pickerPurpose === 'before-after' ? 2 : 0 }}"
             hidden
             class="sticky top-4 z-30 mb-6 rounded-lg border border-admin-amber bg-admin-surface p-4 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-semibold text-admin-smoke"><span data-reference-gallery-count>0</span> gambar dipilih</p>
                <button type="button" data-reference-gallery-insert data-editor-key="{{ $editorKey }}"
                        class="rounded-lg bg-admin-amber px-5 py-2.5 text-sm font-semibold text-white hover:bg-admin-amber/85">
                    {{ $pickerPurpose === 'before-after' ? 'Masukkan Before/After' : 'Masukkan Galeri ke Editor' }}
                </button>
            </div>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-admin-ash">Reference Files</h1>
            <p class="mt-1 text-sm text-admin-muted">Simpan dan kelola gambar, GIF, video, PDF, serta dokumen Word.</p>
        </div>

        <button type="button" wire:click="toggleForm"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-admin-amber px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-admin-amber/85">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
            </svg>
            {{ $showForm ? 'Tutup' : 'Upload File' }}
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-400 bg-green-500/10 px-4 py-3 text-sm text-green-400">
            {{ session('success') }}
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
            class="admin-card mb-7 p-6"
        >
            <div class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-admin-ash">File <span class="text-red-400">*</span></label>
                    <input
                        type="file"
                        wire:model="image"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm,.pdf,.doc,.docx,image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        x-on:livewire-upload-start="startUpload()"
                        x-on:livewire-upload-finish="finishUpload()"
                        x-on:livewire-upload-error="stopUpload()"
                        x-on:livewire-upload-cancel="stopUpload()"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="block w-full rounded-lg border border-admin-line bg-admin-surface p-2 text-sm text-admin-ash file:mr-4 file:rounded file:border-0 file:bg-admin-amber file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                    >
                    <p class="mt-2 text-xs text-admin-muted">JPG, PNG, WebP, GIF, MP4, MOV, WebM, PDF, DOC, atau DOCX. Maksimal 50 MB per file.</p>
                    @error('image') <p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
                </div>

                <div x-show="uploading" x-cloak class="rounded-lg border border-admin-line bg-admin-raised px-5 py-6" role="status" aria-live="polite">
                    <div class="flex items-center gap-3 text-sm font-semibold text-admin-amber">
                        <svg x-show="progress < 100" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <svg x-show="progress >= 100" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"></path>
                        </svg>
                        <span x-text="progress >= 100 ? 'Upload selesai, menampilkan preview...' : 'Mengunggah dan menyiapkan preview...'"></span>
                        <span class="ml-auto" x-text="`${progress}%`"></span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-admin-line">
                        <div class="h-full bg-admin-amber transition-[width] duration-200" x-bind:style="`width: ${progress}%`"></div>
                    </div>
                </div>

                <div x-show="!uploading">
                    @if ($image)
                        <div wire:key="reference-preview-{{ md5($image->getFilename()) }}">
                            @if (str_starts_with($image->getMimeType() ?: '', 'image/'))
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="max-h-72 w-full rounded-lg border border-admin-line object-contain">
                            @elseif (str_starts_with($image->getMimeType() ?: '', 'video/'))
                                <video src="{{ $image->temporaryUrl() }}" controls preload="metadata" class="max-h-72 w-full rounded-lg border border-admin-line bg-black object-contain"></video>
                            @elseif (($image->getMimeType() ?: '') === 'application/pdf')
                                <iframe src="{{ $image->temporaryUrl() }}" title="Preview PDF" class="h-72 w-full rounded-lg border border-admin-line bg-white"></iframe>
                            @else
                                <div class="rounded-lg border border-admin-line bg-admin-raised px-4 py-3 text-sm text-admin-ash">
                                    File dipilih: <span class="font-semibold text-admin-smoke">{{ $image->getClientOriginalName() }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-admin-ash">Judul <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="title" placeholder="Nama atau judul file" class="admin-input">
                    @error('title') <p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-admin-ash">Deskripsi / Alt text <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="alt_text" placeholder="Deskripsi file atau alt text untuk gambar" class="admin-input">
                    @error('alt_text') <p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" x-bind:disabled="uploading" wire:loading.attr="disabled" wire:target="save,image"
                            class="rounded-lg bg-admin-amber px-5 py-2.5 text-sm font-semibold text-white hover:bg-admin-amber/85 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Simpan File</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </form>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            @php
                $isImage = str_starts_with($item->mime_type ?: 'image/', 'image/');
                $isVideo = str_starts_with($item->mime_type ?: '', 'video/');
                $isPdf = ($item->mime_type ?: '') === 'application/pdf';
                // Gambar dipakai langsung di konten publik, jadi URL-nya harus
                // URL storage biasa. Berkas non-publik hanya bisa lewat route.
                $fileUrl = ($item->disk ?: 'public') === 'public'
                    ? url('storage/' . $item->image_path)
                    : route('reference.download', $item->id);
                $previewUrl = ($isVideo || $isPdf) ? route('reference.preview', $item->id) : null;
                $downloadUrl = route('reference.download', $item->id);
                $fileName = $item->original_name ?: basename($item->image_path);
                $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
            @endphp
            <article class="admin-card overflow-hidden">
                <div class="flex h-52 items-center justify-center overflow-hidden bg-admin-raised">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="{{ $item->alt_text ?: $item->title ?: 'Reference image' }}" class="h-full w-full object-cover">
                    @elseif ($isVideo)
                        <video src="{{ $previewUrl }}" controls preload="metadata" class="h-full w-full bg-black object-contain" aria-label="{{ $item->alt_text ?: $item->title ?: 'Reference video' }}"></video>
                    @elseif ($isPdf)
                        <iframe src="{{ $previewUrl }}" title="{{ $item->alt_text ?: $item->title ?: 'Reference PDF' }}" class="h-full w-full bg-white"></iframe>
                    @else
                        <div class="text-center">
                            <svg class="mx-auto h-16 w-16 text-admin-amber" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25" />
                            </svg>
                            <span class="mt-2 block text-sm font-bold text-admin-ash">{{ $extension }}</span>
                        </div>
                    @endif
                </div>

                <div class="space-y-3 p-4">
                    <div>
                        <h2 class="font-semibold text-admin-smoke">{{ $item->title ?: 'Tanpa judul' }}</h2>
                        <p class="mt-1 text-xs text-admin-muted">{{ $item->alt_text ?: 'Alt text belum diisi' }}</p>
                        <p class="mt-2 break-all text-xs text-admin-muted">{{ $fileName }}</p>
                        @if ($item->file_size)
                            <p class="mt-1 text-xs text-admin-muted">{{ number_format($item->file_size / 1024, 1) }} KB · {{ $item->mime_type ?: 'Tipe tidak diketahui' }}</p>
                        @endif
                    </div>

                    @if ($picker && $multiple && $isImage)
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-admin-ash" for="gallery-caption-{{ $item->id }}">Caption galeri</label>
                            <input
                                id="gallery-caption-{{ $item->id }}"
                                type="text"
                                value="{{ $item->alt_text ?: $item->title }}"
                                data-reference-gallery-caption
                                data-reference-id="{{ $item->id }}"
                                class="admin-input text-xs"
                            >
                        </div>
                        <button
                            type="button"
                            data-reference-gallery-toggle
                            data-reference-id="{{ $item->id }}"
                            data-image-url="{{ $fileUrl }}"
                            data-image-title="{{ $item->title }}"
                            data-image-alt="{{ $item->alt_text }}"
                            aria-pressed="false"
                            class="w-full rounded-lg bg-admin-amber px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                        >Tambah ke Galeri</button>
                    @elseif ($picker && $isImage)
                        <button
                            type="button"
                            data-reference-select
                            data-editor-key="{{ $editorKey }}"
                            data-image-url="{{ $fileUrl }}"
                            data-image-title="{{ $item->title }}"
                            data-image-alt="{{ $item->alt_text }}"
                            class="w-full rounded-lg bg-admin-amber px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                        >Pilih untuk Editor</button>
                    @elseif ($picker)
                        <div class="rounded-lg border border-amber-400/40 bg-amber-500/10 px-3 py-2 text-center text-xs text-amber-300">File ini bukan gambar</div>
                    @endif

                    <div x-data="{ copied: false }">
                        <input type="text" readonly value="{{ $fileUrl }}" class="admin-input text-xs">
                        <button type="button" x-on:click="navigator.clipboard.writeText(@js($fileUrl)); copied = true; setTimeout(() => copied = false, 1500)"
                                class="mt-2 w-full rounded-lg border border-admin-line bg-admin-surface px-3 py-2 text-xs font-semibold text-admin-ash hover:bg-admin-raised">
                            <span x-show="!copied">Salin URL</span>
                            <span x-show="copied" x-cloak>URL tersalin</span>
                        </button>
                    </div>

                    <a href="{{ $downloadUrl }}" class="block w-full rounded-lg border border-admin-amber bg-admin-surface px-3 py-2 text-center text-xs font-semibold text-admin-amber hover:bg-admin-raised">Download</a>

                    <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus file reference ini?"
                            class="w-full rounded-lg border border-red-400/40 bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-400 hover:bg-red-500/20">Hapus</button>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-admin-line bg-admin-surface px-6 py-14 text-center text-sm text-admin-muted sm:col-span-2 lg:col-span-3">
                Belum ada file reference.
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="admin-card mt-6 px-5 py-4">
            {{ $items->links() }}
        </div>
    @endif
</div>
