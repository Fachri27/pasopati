<div x-data="{ lang: 'id' }">
    <x-admin.page-header :title="$page ? 'Ubah artikel' : 'Artikel baru'"
                         :crumbs="[['Artikel', route('pages.index')]]"
                         :back="route('pages.index')">
        @if ($page)
            {{-- Pratinjau membaca versi yang TERSIMPAN, bukan isian yang
                 sedang diketik — simpan dulu kalau ingin melihat perubahan. --}}
            <a href="{{ route('page.preview', ['locale' => app()->getLocale(), 'page_type' => $page->page_type, 'slug' => $page->slug]) }}"
               target="_blank" rel="noopener" class="admin-btn admin-btn--quiet admin-btn--sm"
               title="Membuka versi yang terakhir disimpan">
                <x-admin.icon name="external" class="size-3.5" />
                Pratinjau
            </a>
        @endif
        <a href="{{ route('pages.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-artikel" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $page ? 'Simpan perubahan' : 'Simpan artikel' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>

        <x-slot:aside>
            {{-- Judul, ringkasan, isi, blok, dan berkas impor ditulis per
                 bahasa; isian lain berlaku untuk keduanya. --}}
            <div class="admin-tabs" role="group" aria-label="Bahasa isian">
                <button type="button" @click="lang = 'id'" :aria-pressed="lang === 'id' ? 'true' : 'false'">Indonesia</button>
                <button type="button" @click="lang = 'en'" :aria-pressed="lang === 'en' ? 'true' : 'false'">English</button>
            </div>
        </x-slot:aside>
    </x-admin.page-header>

    <div class="admin-wrap admin-body" x-data="{ page_type: @entangle('page_type') }">
        {{-- Isian dua bahasa tidak tampil bersamaan, jadi galat di bahasa yang
             sedang tersembunyi tidak akan terlihat di samping isiannya.
             Ringkasan ini menampilkan semuanya sekaligus. --}}
        @if ($errors->any())
            <div class="admin-alert mb-5" role="alert">
                <p class="font-medium">Artikel belum tersimpan.</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-artikel" wire:submit.prevent="save" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">

            {{-- ================= KOLOM UTAMA ================= --}}
            <div class="grid min-w-0 gap-5">

                <x-admin.card title="Judul">
                    {{-- Judul & slug Indonesia --}}
                    <div x-show="lang === 'id'" x-data="{
                        title: @js(old('title_id', $title_id ?? '')),
                        slug: @js(old('slugId', $slugId ?? '')),
                        makeSlug(text) {
                            return text.toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                     }" x-init="if(title && !slug){ slug = makeSlug(title) }" class="grid gap-4">
                        <div>
                            <label for="artikel-judul-id" class="admin-label">Judul (Indonesia)</label>
                            <input id="artikel-judul-id" type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                   class="admin-input" @error('title_id') aria-invalid="true" @enderror>
                            @error('title_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="artikel-slug-id" class="admin-label">Slug</label>
                            <input id="artikel-slug-id" type="text" x-model="slug" readonly
                                   class="admin-input font-admin-mono bg-admin-inset text-[13px] text-admin-ash">
                            <p class="admin-hint">Dibuat otomatis dari judul.</p>
                        </div>
                    </div>

                    {{-- Judul & slug English --}}
                    <div x-show="lang === 'en'" x-cloak x-data="{
                        title: @js(old('title_en', $title_en ?? '')),
                        slug: @js(old('slugEn', $slugEn ?? '')),
                        makeSlug(text) {
                            return text.toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                     }" x-init="if(title && !slug){ slug = makeSlug(title) }" class="grid gap-4">
                        <div>
                            <label for="artikel-judul-en" class="admin-label">Judul (English)</label>
                            <input id="artikel-judul-en" type="text" wire:model="title_en" x-model="title" @input="slug = makeSlug(title)"
                                   class="admin-input" @error('title_en') aria-invalid="true" @enderror>
                            @error('title_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="artikel-slug-en" class="admin-label">Slug</label>
                            <input id="artikel-slug-en" type="text" x-model="slug" readonly
                                   class="admin-input font-admin-mono bg-admin-inset text-[13px] text-admin-ash">
                            <p class="admin-hint">Dibuat otomatis dari judul.</p>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Ringkasan" description="Excerpt: pengantar singkat yang tampil di daftar artikel.">
                    <div x-show="lang === 'id'">
                        {{-- excerpt_editor_id --}}
                        @includeWhen(true, 'front.partials.tinymce-excerpt-id')
                    </div>

                    <div x-show="lang === 'en'">
                        {{-- excerpt_editor_en --}}
                        @includeWhen(true, 'front.partials.tinymce-excerpt-en')
                    </div>
                </x-admin.card>

                <x-admin.card title="Konten paragraf"
                              description="Isi artikel biasa. Untuk blok khusus (info acara, agenda, dll.) pakai bagian Blok konten terstruktur di bawah.">
                    <div x-show="lang === 'id'">
                        @includeWhen(true, 'front.partials.tinymce-content-id')
                    </div>

                    <div x-show="lang === 'en'">
                        @includeWhen(true, 'front.partials.tinymce-content-en')
                    </div>
                </x-admin.card>

                {{-- Blok konten: hanya untuk Expose. Markup blok Indonesia dan
                     English sama persis kecuali kode bahasanya, jadi keduanya
                     dirender dari satu perulangan supaya tidak pernah berbeda. --}}
                @php
                    $namaBlok = [
                        'paragraph' => 'Paragraf',
                        'image' => 'Gambar',
                        'event_info_box' => 'Info acara',
                        'agenda_day' => 'Agenda',
                        'speaker_bio' => 'Pembicara',
                        'quote' => 'Kutipan',
                    ];
                    $isianKecil = 'admin-input h-8 min-h-8 py-0 text-[13px]';
                @endphp

                <div x-show="page_type === 'expose'">
                    <x-admin.card title="Blok konten terstruktur"
                                  description="Blok khusus seperti info acara, agenda, bio pembicara, kutipan, atau gambar.">
                        @foreach (['id' => $content_blocks_id, 'en' => $content_blocks_en] as $loc => $blocks)
                            <div x-show="lang === '{{ $loc }}'" @if ($loc !== 'id') x-cloak @endif class="grid gap-4">
                                @foreach ($blocks as $index => $block)
                                    <div class="rounded-md border border-admin-line bg-admin-inset" wire:key="block_{{ $loc }}_{{ $index }}_{{ $blocksVersion }}">
                                        <div class="flex items-center gap-2 border-b border-admin-line py-1.5 pr-1.5 pl-3">
                                            <span class="admin-num text-[12px] text-admin-muted">{{ $index + 1 }}</span>
                                            <span class="admin-cap admin-cap--quiet">{{ $namaBlok[$block['type']] ?? $block['type'] }}</span>
                                            <div class="ml-auto flex items-center gap-0.5">
                                                <button type="button" wire:click="moveBlockUp('{{ $loc }}', {{ $index }})" class="admin-icon-btn size-7"
                                                        title="Naikkan blok" aria-label="Naikkan blok {{ $index + 1 }}" {{ $loop->first ? 'disabled' : '' }}>
                                                    <x-admin.icon name="up" class="size-3.5" />
                                                </button>
                                                <button type="button" wire:click="moveBlockDown('{{ $loc }}', {{ $index }})" class="admin-icon-btn size-7"
                                                        title="Turunkan blok" aria-label="Turunkan blok {{ $index + 1 }}" {{ $loop->last ? 'disabled' : '' }}>
                                                    <x-admin.icon name="down" class="size-3.5" />
                                                </button>
                                                <button type="button" wire:click="removeBlock('{{ $loc }}', {{ $index }})"
                                                        wire:confirm="Buang blok ini beserta isinya?"
                                                        class="admin-icon-btn size-7 hover:text-admin-ember"
                                                        title="Buang blok" aria-label="Buang blok {{ $index + 1 }}">
                                                    <x-admin.icon name="trash" class="size-3.5" />
                                                </button>
                                            </div>
                                        </div>

                                        <div class="p-3">
                                            @switch($block['type'])
                                                @case('paragraph')
                                                    @php $editorIdx = $loop->index; @endphp
                                                    <div x-data="{
                                                        html: $wire.$entangle('content_blocks_{{ $loc }}.{{ $editorIdx }}.data.html'),
                                                        initEditor() {
                                                            let id = 'paragraph_editor_{{ $loc }}_{{ $editorIdx }}';
                                                            if (tinymce.get(id)) tinymce.get(id).remove();
                                                            let self = this;
                                                            tinymce.init({
                                                                target: document.getElementById(id),
                                                                plugins: 'advlist link lists code',
                                                                toolbar: 'bold italic underline | bullist numlist | link | undo redo | code',
                                                                menubar: false,
                                                                statusbar: false,
                                                                // Bawaan TinyMCE 400px; satu artikel bisa berisi
                                                                // banyak blok paragraf, jadi dibuat lebih pendek
                                                                // supaya daftar bloknya tidak terlalu panjang.
                                                                height: 240,
                                                                skin: true,
                                                                content_css: true,
                                                                license_key: 'gpl',
                                                                promotion: false,
                                                                branding: false,
                                                                valid_elements: '*[*]',
                                                                entity_encoding: 'raw',
                                                                setup(editor) {
                                                                    editor.on('init', () => {
                                                                        editor.setContent(self.html || '');
                                                                    });
                                                                    editor.on('change keyup', () => {
                                                                        self.html = editor.getContent();
                                                                    });
                                                                }
                                                            });
                                                        }
                                                    }" x-init="cmsBilaTampak($el, () => initEditor())" wire:ignore>
                                                        <textarea id="paragraph_editor_{{ $loc }}_{{ $editorIdx }}" rows="5" class="admin-input font-admin-mono text-[13px]"></textarea>
                                                    </div>
                                                    @break
                                                @case('image')
                                                    <div class="grid gap-3" x-data="{
                                                        previewUrl: @js(
                                                            (isset($block['data']['src']) && $block['data']['src'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                                ? $block['data']['src']->temporaryUrl()
                                                                : (!empty($block['data']['src']) ? asset('storage/' . $block['data']['src']) : '')
                                                        )
                                                    }">
                                                        <div>
                                                            <label class="admin-label">Unggah gambar</label>
                                                            <input type="file" accept="image/*"
                                                                   x-on:change="
                                                                       let file = $event.target.files[0];
                                                                       if (file) {
                                                                           previewUrl = URL.createObjectURL(file);
                                                                           $wire.upload('content_blocks_{{ $loc }}.{{ $index }}.data.src', file, () => {}, () => {}, (event) => {});
                                                                       }"
                                                                   class="admin-input">
                                                        </div>
                                                        <template x-if="previewUrl">
                                                            <div>
                                                                <img :src="previewUrl" alt="Pratinjau gambar blok"
                                                                     class="h-28 rounded-md border border-admin-line"
                                                                     :class="{
                                                                         'mx-auto': $wire.content_blocks_{{ $loc }}[{{ $index }}].data.alignment === 'center',
                                                                         'ml-0 mr-auto': $wire.content_blocks_{{ $loc }}[{{ $index }}].data.alignment === 'left',
                                                                         'ml-auto mr-0': $wire.content_blocks_{{ $loc }}[{{ $index }}].data.alignment === 'right',
                                                                         'w-full': $wire.content_blocks_{{ $loc }}[{{ $index }}].data.alignment === 'full',
                                                                         'object-cover': $wire.content_blocks_{{ $loc }}[{{ $index }}].data.alignment !== 'full'
                                                                     }">
                                                            </div>
                                                        </template>
                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="admin-label">Perataan</label>
                                                                <select wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.alignment" class="admin-input">
                                                                    <option value="center">Tengah</option>
                                                                    <option value="left">Kiri (mengambang)</option>
                                                                    <option value="right">Kanan (mengambang)</option>
                                                                    <option value="full">Selebar kolom</option>
                                                                </select>
                                                            </div>
                                                            <div class="sm:col-span-2">
                                                                <label class="admin-label">Keterangan gambar</label>
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.caption" class="admin-input">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @break
                                                @case('event_info_box')
                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="admin-label">Format</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.format" placeholder="Online / Offline / Hybrid" class="admin-input">
                                                        </div>
                                                        <div>
                                                            <label class="admin-label">Tanggal</label>
                                                            <input type="date" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.date" class="admin-input">
                                                        </div>
                                                        <div>
                                                            <label class="admin-label">Waktu</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.time" placeholder="13:00 - 16:00 WIB" class="admin-input">
                                                        </div>
                                                        <div>
                                                            <label class="admin-label">Tempat</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.venue" class="admin-input">
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="admin-label">Link registrasi</label>
                                                            @foreach (($block['data']['registration_links'] ?? []) as $linkIndex => $link)
                                                                <div class="mb-1.5 flex items-center gap-2" wire:key="reglink_{{ $loc }}_{{ $index }}_{{ $linkIndex }}">
                                                                    <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.registration_links.{{ $linkIndex }}.day" placeholder="Hari 1" aria-label="Hari" class="{{ $isianKecil }} w-1/3">
                                                                    <input type="url" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.registration_links.{{ $linkIndex }}.url" placeholder="https://..." aria-label="URL registrasi" class="{{ $isianKecil }} min-w-0 flex-1">
                                                                    <button type="button" wire:click="removeRegLink('{{ $loc }}', {{ $index }}, {{ $linkIndex }})"
                                                                            class="admin-icon-btn size-7 shrink-0" title="Buang link" aria-label="Buang link {{ $linkIndex + 1 }}">
                                                                        <x-admin.icon name="close" class="size-3.5" />
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                            <button type="button" wire:click="addRegLink('{{ $loc }}', {{ $index }})" class="admin-btn admin-btn--quiet admin-btn--sm -ml-2">
                                                                <x-admin.icon name="plus" class="size-3.5" />
                                                                Tambah link
                                                            </button>
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="admin-label">Catatan</label>
                                                            <textarea wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.notes" rows="2" class="admin-input"></textarea>
                                                        </div>
                                                    </div>
                                                    @break
                                                @case('agenda_day')
                                                    <div class="sm:w-56">
                                                        <label class="admin-label">Hari/tanggal</label>
                                                        <input type="date" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.day" class="admin-input">
                                                    </div>
                                                    <p class="admin-label mt-3">Sesi</p>
                                                    @foreach (($block['data']['sessions'] ?? []) as $sessionIndex => $session)
                                                        <div class="mb-3 grid gap-1.5 border-l-2 border-admin-line-strong pl-3" wire:key="session_{{ $loc }}_{{ $index }}_{{ $sessionIndex }}">
                                                            <div class="grid grid-cols-4 gap-1.5">
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.time" placeholder="Waktu" aria-label="Waktu sesi" class="{{ $isianKecil }}">
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.title" placeholder="Judul sesi" aria-label="Judul sesi" class="{{ $isianKecil }} col-span-3">
                                                            </div>
                                                            <textarea wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.description" placeholder="Deskripsi" aria-label="Deskripsi sesi" rows="2" class="admin-input text-[13px]"></textarea>
                                                            <div class="grid gap-1.5 sm:grid-cols-3">
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.moderator" placeholder="Moderator" aria-label="Moderator" class="{{ $isianKecil }}">
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.commentator" placeholder="Komentator" aria-label="Komentator" class="{{ $isianKecil }}">
                                                                <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.sessions.{{ $sessionIndex }}.speakers" placeholder="Pembicara (pisahkan koma)" aria-label="Pembicara" class="{{ $isianKecil }}">
                                                            </div>
                                                            <div>
                                                                <button type="button" wire:click="removeSession('{{ $loc }}', {{ $index }}, {{ $sessionIndex }})"
                                                                        wire:confirm="Buang sesi ini beserta isinya?"
                                                                        class="admin-btn admin-btn--quiet-danger admin-btn--sm -ml-2">Buang sesi</button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <button type="button" wire:click="addSession('{{ $loc }}', {{ $index }})" class="admin-btn admin-btn--quiet admin-btn--sm -ml-2">
                                                        <x-admin.icon name="plus" class="size-3.5" />
                                                        Tambah sesi
                                                    </button>
                                                    @break
                                                @case('speaker_bio')
                                                    <div class="grid gap-3 sm:grid-cols-2" x-data="{
                                                        photoUrl: @js(
                                                            (isset($block['data']['photo']) && $block['data']['photo'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                                ? $block['data']['photo']->temporaryUrl()
                                                                : (!empty($block['data']['photo']) ? asset('storage/' . $block['data']['photo']) : '')
                                                        )
                                                    }">
                                                        <div>
                                                            <label class="admin-label">Nama</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.name" class="admin-input">
                                                        </div>
                                                        <div>
                                                            <label class="admin-label">Jabatan</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.title" class="admin-input">
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="admin-label">Unggah foto</label>
                                                            <div class="flex items-center gap-3">
                                                                <template x-if="photoUrl">
                                                                    <img :src="photoUrl" alt="Pratinjau foto pembicara" class="size-12 shrink-0 rounded-full border border-admin-line object-cover">
                                                                </template>
                                                                <input type="file" accept="image/*"
                                                                       x-on:change="
                                                                           let file = $event.target.files[0];
                                                                           if (file) {
                                                                               photoUrl = URL.createObjectURL(file);
                                                                               $wire.upload('content_blocks_{{ $loc }}.{{ $index }}.data.photo', file, () => {}, () => {}, (event) => {});
                                                                           }"
                                                                       class="admin-input min-w-0 flex-1">
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="admin-label">Bio</label>
                                                            <textarea wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.bio" rows="3" class="admin-input"></textarea>
                                                        </div>
                                                    </div>
                                                    @break
                                                @case('quote')
                                                    <div class="grid gap-3">
                                                        <div>
                                                            <label class="admin-label">Teks kutipan</label>
                                                            <textarea wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.text" rows="3" class="admin-input"></textarea>
                                                        </div>
                                                        <div>
                                                            <label class="admin-label">Sumber</label>
                                                            <input type="text" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.source" class="admin-input">
                                                        </div>
                                                    </div>
                                                    @break
                                            @endswitch

                                            {{-- Jarak atas/bawah blok di halaman publik; kosong = bawaan tema. --}}
                                            <div class="mt-3 flex flex-wrap gap-3 border-t border-admin-line pt-3">
                                                <div>
                                                    <label class="admin-label text-[12.5px] font-normal text-admin-muted">Jarak atas (px)</label>
                                                    <input type="number" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.mt" placeholder="auto" class="{{ $isianKecil }} admin-num w-24">
                                                </div>
                                                <div>
                                                    <label class="admin-label text-[12.5px] font-normal text-admin-muted">Jarak bawah (px)</label>
                                                    <input type="number" wire:model="content_blocks_{{ $loc }}.{{ $index }}.data.mb" placeholder="auto" class="{{ $isianKecil }} admin-num w-24">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (count($blocks) === 0)
                                    <p class="text-[13px] text-admin-muted">Belum ada blok. Tambahkan dari tombol di bawah.</p>
                                @endif

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($namaBlok as $jenis => $nama)
                                        <button type="button" wire:click="addBlock('{{ $loc }}', '{{ $jenis }}')" class="admin-btn admin-btn--ghost admin-btn--sm">
                                            <x-admin.icon name="plus" class="size-3.5" />
                                            {{ $nama }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </x-admin.card>
                </div>
            </div>

            {{-- ================= REL SAMPING ================= --}}
            {{-- top-[68px]: bilah atas halaman (48px) menempel di atas, jadi
                 kartu samping berhenti tepat di bawahnya. --}}
            <div class="grid gap-5 lg:sticky lg:top-[68px]">
                <x-admin.card title="Publikasi">
                    <div class="grid gap-4">
                        <div>
                            <label for="artikel-status" class="admin-label">Status</label>
                            <select id="artikel-status" wire:model="status" class="admin-input">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="artikel-terbit" class="admin-label">Tanggal terbit</label>
                            <input id="artikel-terbit" type="date" wire:model="published_at" class="admin-input">
                            @error('published_at') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="artikel-jenis" class="admin-label">Jenis</label>
                                <select id="artikel-jenis" wire:model="page_type" class="admin-input">
                                    <option value="expose">Expose</option>
                                    <option value="ngopini">Ngopini</option>
                                </select>
                            </div>
                            <div>
                                <label for="artikel-tampilan" class="admin-label">Tampilan</label>
                                <select id="artikel-tampilan" wire:model="type" class="admin-input">
                                    <option value="parallax">Parallax</option>
                                    <option value="default">Default</option>
                                </select>
                            </div>
                        </div>

                        {{-- Topik Expose (boleh lebih dari satu) --}}
                        <div x-show="page_type === 'expose'">
                            <p class="admin-label">Topik Expose</p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="item in ['deforestasi','kebakaran','pulp','mining']">
                                    <label class="cursor-pointer rounded-full border px-2.5 py-1 text-[12.5px] font-medium has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-admin-focus"
                                           :class="$wire.expose_type.includes(item) ? 'border-admin-smoke bg-admin-smoke text-admin-on-accent' : 'border-admin-line-strong bg-admin-surface text-admin-ash hover:bg-admin-tint'">

                                        <input type="checkbox" class="sr-only" wire:model="expose_type"
                                            :value="item">

                                        <span x-text="item.replace('_',' ')"></span>
                                    </label>
                                </template>
                            </div>
                            @error('expose_type') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Gambar utama">
                    @if ($featured_image)
                        <img src="{{ $featured_image->temporaryUrl() }}" alt="Pratinjau gambar utama baru"
                             class="mb-3 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                    @elseif ($old_featured_image)
                        <img src="{{ asset('storage/' . $old_featured_image) }}" alt="Gambar utama saat ini"
                             class="mb-3 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                    @endif
                    <input type="file" wire:model="featured_image" accept="image/png,image/jpeg" aria-label="Gambar utama" class="admin-input">
                    <p class="admin-hint" wire:loading.remove wire:target="featured_image">JPG atau PNG, paling besar 5 MB.</p>
                    <p class="admin-hint" wire:loading wire:target="featured_image">Mengunggah…</p>
                    @error('featured_image') <p class="admin-error">{{ $message }}</p> @enderror
                </x-admin.card>

                <x-admin.card title="Impor dokumen" description="Isi artikel bisa diambil dari berkas saat disimpan.">
                    <div class="grid gap-4">
                        <div>
                            <label for="artikel-sumber" class="admin-label">Sumber isi</label>
                            <select id="artikel-sumber" wire:model="source_type" class="admin-input">
                                <option value="manual">Manual</option>
                                <option value="docx">DOCX / DOC</option>
                                <option value="pdf">PDF (ekstrak teks)</option>
                            </select>
                        </div>

                        <div x-show="lang === 'id'">
                            <label for="artikel-berkas-id" class="admin-label">Berkas (Indonesia)</label>
                            <input id="artikel-berkas-id" type="file" wire:model="file_import_id" class="admin-input">
                            @error('file_import_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="lang === 'en'" x-cloak>
                            <label for="artikel-berkas-en" class="admin-label">Berkas (English)</label>
                            <input id="artikel-berkas-en" type="file" wire:model="file_import_en" class="admin-input">
                            @error('file_import_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </form>
    </div>

    <x-admin.toast />
</div>
