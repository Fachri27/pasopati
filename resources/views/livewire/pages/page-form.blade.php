<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md"
            x-data="{ page_type: @entangle('page_type') }">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="{{ route('pages.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                    Page Artikel
                </a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">
                    {{ $page ? '✏️ Edit Page' : 'Tambah Artikel' }}
                </span>
            </nav>

            <h1 class="text-2xl font-bold mb-8 text-gray-700">
                {{ $page ? '✏️ Edit Page' : '➕ Add Page' }}
            </h1>

            <form wire:submit.prevent="save">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="grid grid-cols-12 gap-6">

                    {{-- ================= LEFT COLUMN ================= --}}
                    <div class="col-span-12 lg:col-span-4">
                        <div class="bg-gray-50 border rounded-xl p-5 space-y-4 sticky top-6">

                            {{-- Language --}}
                            <div>
                                <label class="font-medium">🌐 Bahasa</label>
                                <select x-model="lang" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="id">Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            {{-- Title & Slug ID --}}
                            <div x-show="lang === 'id'" x-data="{
                                title: @js(old('title_id', $title_id ?? '')),
                                slug: @js(old('slugId', $slugId ?? '')),
                                makeSlug(text) {
                                    return text.toLowerCase()
                                        .replace(/[^a-z0-9]+/g, '-')
                                        .replace(/^-+|-+$/g, '');
                                }
                             }" x-init="if(title && !slug){ slug = makeSlug(title) }">

                                <label class="font-medium">Title (ID)</label>
                                <input type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                                @error('title_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <label class="text-sm mt-2 block">Slug</label>
                                <input type="text" x-model="slug" readonly
                                    class="w-full bg-gray-100 border rounded-lg px-3 py-2">
                            </div>

                            {{-- Title EN --}}
                            <div x-show="lang === 'en'" x-data="{
                                title: @js(old('title_en', $title_en ?? '')),
                                slug: @js(old('slugEn', $slugEn ?? '')),
                                makeSlug(text) {
                                    return text.toLowerCase()
                                        .replace(/[^a-z0-9]+/g, '-')
                                        .replace(/^-+|-+$/g, '');
                                }
                             }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                                <label class="font-medium">Title (EN)</label>
                                <input type="text" wire:model="title_en" x-model="title" @input="slug = makeSlug(title)"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                                @error('title_en') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <label class="text-sm mt-2 block">Slug</label>
                                <input type="text" x-model="slug" readonly
                                    class="w-full bg-gray-100 border rounded-lg px-3 py-2">
                            </div>

                            {{-- Publish + Page Type --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm">📅 Publish</label>
                                    <input type="date" wire:model="published_at"
                                        class="w-full border rounded-lg px-2 py-2">
                                </div>

                                <div>
                                    <label class="text-sm">Page Type</label>
                                    <select wire:model="page_type" class="w-full border rounded-lg px-2 py-2">
                                        <option value="expose">Expose</option>
                                        <option value="ngopini">Ngopini</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Source --}}
                            <div>
                                <label class="font-medium">Source Type</label>
                                <select wire:model="source_type" class="w-full border rounded-lg px-3 py-2">
                                    <option value="manual">Manual</option>
                                    <option value="docx">DOCX / DOC</option>
                                    <option value="pdf">PDF (ekstrak teks)</option>
                                </select>
                            </div>

                            {{-- Upload --}}
                            <div x-show="lang === 'id'">
                                <label class="text-sm">Upload File (ID)</label>
                                <input type="file" wire:model="file_import_id"
                                    class="w-full border rounded-lg px-2 py-2">
                                @error('file_import_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div x-show="lang === 'en'">
                                <label class="text-sm">Upload File (EN)</label>
                                <input type="file" wire:model="file_import_en"
                                    class="w-full border rounded-lg px-2 py-2">
                                @error('file_import_en') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Featured Image --}}
                            <div>
                                <label class="font-medium">Featured Image</label>
                                <input type="file" wire:model="featured_image"
                                    class="w-full border rounded-lg px-2 py-2 mt-1">

                                <div class="mt-3">
                                    @if ($featured_image)
                                        <img src="{{ $featured_image->temporaryUrl() }}"
                                            class="w-20 h-20 rounded-lg object-cover border">
                                    @elseif ($old_featured_image)
                                        <img src="{{ asset('storage/' . $old_featured_image) }}"
                                            class="w-20 h-20 rounded-lg object-cover border">
                                    @endif
                                </div>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="font-medium">Status</label>
                                <select wire:model="status" class="w-full border rounded-lg px-3 py-2">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label for="" class="font-medium">Type</label>
                                <select wire:model="type"
                                    class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                                    <option value="parallax">Parallax</option>
                                    <option value="default">Default</option>
                                </select>
                            </div>


                            {{-- Expose Type (MULTIPLE) --}}
                            <div x-show="page_type === 'expose'">
                                <label class="font-medium mb-2 block">Expose Type</label>

                                <div class="flex flex-wrap gap-2">
                                    <template x-for="item in ['deforestasi','kebakaran','pulp','mining']">
                                        <label class="px-3 py-1 border rounded-full cursor-pointer text-sm" :class="$wire.expose_type.includes(item)
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700'">

                                            <input type="checkbox" class="hidden" wire:model="expose_type"
                                                :value="item">

                                            <span x-text="item.replace('_',' ')"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>


                        </div>
                    </div>

                    {{-- ================= RIGHT COLUMN ================= --}}
                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        {{-- Excerpt --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Excerpt</h3>

                            <div x-show="lang === 'id'">
                                {{-- excerpt_editor_id --}}
                                @includeWhen(true, 'front.partials.tinymce-excerpt-id')
                            </div>

                            <div x-show="lang === 'en'">
                                {{-- excerpt_editor_en --}}
                                @includeWhen(true, 'front.partials.tinymce-excerpt-en')
                            </div>
                        </div>

                        {{-- Content Paragraph --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Konten Paragraf</h3>
                            <p class="text-xs text-gray-500 mb-2">Gunakan editor ini untuk konten paragraf biasa. Untuk blok khusus (info acara, agenda, dll.) gunakan bagian "Blok Konten Terstruktur" di bawah.</p>

                            <div x-show="lang === 'id'">
                                @includeWhen(true, 'front.partials.tinymce-content-id')
                            </div>

                            <div x-show="lang === 'en'">
                                @includeWhen(true, 'front.partials.tinymce-content-en')
                            </div>
                        </div>

                        {{-- Content Blocks --}}
                        <div x-show="page_type === 'expose'" class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Blok Konten Terstruktur</h3>
                            <p class="text-xs text-gray-500 mb-3">Tambahkan blok khusus seperti info acara, agenda, bio pembicara, quote, atau gambar.</p>

                            {{-- ID Blocks --}}
                            <div x-show="lang === 'id'" class="space-y-4">
                                @foreach ($content_blocks_id as $index => $block)
                                    <div class="border rounded-lg p-4 bg-gray-50" wire:key="block_id_{{ $index }}_{{ $blocksVersion }}">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                                {{ $block['type'] === 'paragraph' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $block['type'] === 'image' ? 'bg-purple-100 text-purple-700' : '' }}
                                                {{ $block['type'] === 'event_info_box' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $block['type'] === 'agenda_day' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $block['type'] === 'speaker_bio' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $block['type'] === 'quote' ? 'bg-gray-200 text-gray-700' : '' }}">
                                                {{ $block['type'] }}
                                            </span>
                                            <div class="flex gap-1">
                                                <button type="button" wire:click="moveBlockUp('id', {{ $index }})" class="px-2 py-1 text-sm border rounded hover:bg-gray-100" {{ $loop->first ? 'disabled' : '' }}>↑</button>
                                                <button type="button" wire:click="moveBlockDown('id', {{ $index }})" class="px-2 py-1 text-sm border rounded hover:bg-gray-100" {{ $loop->last ? 'disabled' : '' }}>↓</button>
                                                <button type="button" wire:click="removeBlock('id', {{ $index }})" class="px-2 py-1 text-sm border rounded text-red-600 hover:bg-red-50">×</button>
                                            </div>
                                        </div>

                                        @switch($block['type'])
                                            @case('paragraph')
                                                @php $editorIdx = $loop->index; @endphp
                                                <div x-data="{
                                                    html: $wire.$entangle('content_blocks_id.{{ $editorIdx }}.data.html'),
                                                    initEditor() {
                                                        let id = 'paragraph_editor_id_{{ $editorIdx }}';
                                                        if (tinymce.get(id)) tinymce.get(id).remove();
                                                        let self = this;
                                                        tinymce.init({
                                                            target: document.getElementById(id),
                                                            plugins: 'advlist link lists code',
                                                            toolbar: 'bold italic underline | bullist numlist | link | undo redo | code',
                                                            menubar: false,
                                                            statusbar: false,
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
                                                }" x-init="initEditor" wire:ignore>
                                                    <textarea id="paragraph_editor_id_{{ $editorIdx }}" rows="5" class="w-full border rounded-lg px-3 py-2 font-mono text-sm"></textarea>
                                                </div>
                                                @break
                                            @case('image')
                                                <div class="space-y-3" x-data="{
                                                    previewUrl: @js(
                                                        (isset($block['data']['src']) && $block['data']['src'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                            ? $block['data']['src']->temporaryUrl()
                                                            : (!empty($block['data']['src']) ? asset('storage/' . $block['data']['src']) : '')
                                                    )
                                                }">
                                                    <div>
                                                        <label class="text-xs font-medium">Upload Gambar</label>
                                                        <input type="file" accept="image/*"
                                                               x-on:change="
                                                                   let file = $event.target.files[0];
                                                                   if (file) {
                                                                       previewUrl = URL.createObjectURL(file);
                                                                       $wire.upload('content_blocks_id.{{ $index }}.data.src', file, () => {}, () => {}, (event) => {});
                                                                   }"
                                                               class="w-full border rounded-lg px-2 py-1.5 text-sm">
                                                    </div>
                                                    <template x-if="previewUrl">
                                                        <div>
                                                            <img :src="previewUrl"
                                                                 class="h-28 rounded border"
                                                                 :class="{
                                                                     'mx-auto': $wire.content_blocks_id[{{ $index }}].data.alignment === 'center',
                                                                     'ml-0 mr-auto': $wire.content_blocks_id[{{ $index }}].data.alignment === 'left',
                                                                     'ml-auto mr-0': $wire.content_blocks_id[{{ $index }}].data.alignment === 'right',
                                                                     'w-full': $wire.content_blocks_id[{{ $index }}].data.alignment === 'full',
                                                                     'object-cover': $wire.content_blocks_id[{{ $index }}].data.alignment !== 'full'
                                                                 }">
                                                        </div>
                                                    </template>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="text-xs font-medium">Alignment</label>
                                                            <select wire:model="content_blocks_id.{{ $index }}.data.alignment" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                                <option value="center">Center</option>
                                                                <option value="left">Left (float)</option>
                                                                <option value="right">Right (float)</option>
                                                                <option value="full">Full Width</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <label class="text-xs font-medium">Caption</label>
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.caption" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                @break
                                            @case('event_info_box')
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-xs font-medium">Format</label>
                                                        <input type="text" wire:model="content_blocks_id.{{ $index }}.data.format" placeholder="Online / Offline / Hybrid" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Tanggal</label>
                                                        <input type="date" wire:model="content_blocks_id.{{ $index }}.data.date" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Waktu</label>
                                                        <input type="text" wire:model="content_blocks_id.{{ $index }}.data.time" placeholder="13:00 - 16:00 WIB" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Tempat</label>
                                                        <input type="text" wire:model="content_blocks_id.{{ $index }}.data.venue" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Link Registrasi</label>
                                                        @foreach (($block['data']['registration_links'] ?? []) as $linkIndex => $link)
                                                            <div class="flex gap-2 mb-1" wire:key="reglink_id_{{ $index }}_{{ $linkIndex }}">
                                                                <input type="text" wire:model="content_blocks_id.{{ $index }}.data.registration_links.{{ $linkIndex }}.day" placeholder="Hari 1" class="w-1/3 border rounded px-2 py-1 text-sm">
                                                                <input type="url" wire:model="content_blocks_id.{{ $index }}.data.registration_links.{{ $linkIndex }}.url" placeholder="https://..." class="flex-1 border rounded px-2 py-1 text-sm">
                                                                <button type="button" wire:click="removeRegLink('id', {{ $index }}, {{ $linkIndex }})" class="text-red-500 text-sm px-2">×</button>
                                                            </div>
                                                        @endforeach
                                                        <button type="button" wire:click="addRegLink('id', {{ $index }})" class="text-sm text-blue-600 hover:underline">+ Tambah Link</button>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Catatan</label>
                                                        <textarea wire:model="content_blocks_id.{{ $index }}.data.notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                    </div>
                                                </div>
                                                @break
                                            @case('agenda_day')
                                                <div>
                                                    <label class="text-xs font-medium">Hari/Tanggal</label>
                                                    <input type="date" wire:model="content_blocks_id.{{ $index }}.data.day" class="w-full border rounded-lg px-3 py-2 text-sm mb-3">
                                                </div>
                                                <label class="text-xs font-medium mb-1 block">Sesi</label>
                                                @foreach (($block['data']['sessions'] ?? []) as $sessionIndex => $session)
                                                    <div class="border-l-2 border-blue-400 pl-3 mb-3" wire:key="session_id_{{ $index }}_{{ $sessionIndex }}">
                                                        <div class="grid grid-cols-4 gap-2 mb-1">
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.time" placeholder="Waktu" class="border rounded px-2 py-1 text-sm">
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.title" placeholder="Judul Sesi" class="col-span-3 border rounded px-2 py-1 text-sm">
                                                        </div>
                                                        <textarea wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.description" placeholder="Deskripsi" rows="2" class="w-full border rounded px-2 py-1 text-sm mb-1"></textarea>
                                                        <div class="grid grid-cols-3 gap-2 text-sm">
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.moderator" placeholder="Moderator" class="border rounded px-2 py-1">
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.commentator" placeholder="Komentator" class="border rounded px-2 py-1">
                                                            <input type="text" wire:model="content_blocks_id.{{ $index }}.data.sessions.{{ $sessionIndex }}.speakers" placeholder="Pembicara (pisahkan koma)" class="border rounded px-2 py-1">
                                                        </div>
                                                        <button type="button" wire:click="removeSession('id', {{ $index }}, {{ $sessionIndex }})" class="text-xs text-red-500 hover:underline mt-1">Hapus sesi</button>
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addSession('id', {{ $index }})" class="text-sm text-blue-600 hover:underline">+ Tambah Sesi</button>
                                                @break
                                            @case('speaker_bio')
                                                <div class="grid grid-cols-2 gap-3" x-data="{
                                                    photoUrl: @js(
                                                        (isset($block['data']['photo']) && $block['data']['photo'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                            ? $block['data']['photo']->temporaryUrl()
                                                            : (!empty($block['data']['photo']) ? asset('storage/' . $block['data']['photo']) : '')
                                                    )
                                                }">
                                                    <div>
                                                        <label class="text-xs font-medium">Nama</label>
                                                        <input type="text" wire:model="content_blocks_id.{{ $index }}.data.name" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Jabatan</label>
                                                        <input type="text" wire:model="content_blocks_id.{{ $index }}.data.title" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Upload Foto</label>
                                                        <input type="file" accept="image/*"
                                                               x-on:change="
                                                                   let file = $event.target.files[0];
                                                                   if (file) {
                                                                       photoUrl = URL.createObjectURL(file);
                                                                       $wire.upload('content_blocks_id.{{ $index }}.data.photo', file, () => {}, () => {}, (event) => {});
                                                                   }"
                                                               class="w-full border rounded-lg px-2 py-1.5 text-sm">
                                                        <template x-if="photoUrl">
                                                            <img :src="photoUrl" class="mt-1 h-16 w-16 rounded-full object-cover border">
                                                        </template>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Bio</label>
                                                        <textarea wire:model="content_blocks_id.{{ $index }}.data.bio" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                    </div>
                                                </div>
                                                @break
                                            @case('quote')
                                                <div>
                                                    <label class="text-xs font-medium">Teks Kutipan</label>
                                                    <textarea wire:model="content_blocks_id.{{ $index }}.data.text" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="text-xs font-medium">Sumber</label>
                                                    <input type="text" wire:model="content_blocks_id.{{ $index }}.data.source" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                </div>
                                                @break
                                        @endswitch
                                        <div class="flex gap-3 mt-3 pt-3 border-t border-gray-200">
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Margin Top (px)</label>
                                                <input type="number" wire:model="content_blocks_id.{{ $index }}.data.mt" placeholder="auto" class="w-24 border rounded px-2 py-1 text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Margin Bottom (px)</label>
                                                <input type="number" wire:model="content_blocks_id.{{ $index }}.data.mb" placeholder="auto" class="w-24 border rounded px-2 py-1 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="flex flex-wrap gap-2 pt-2">
                                    <button type="button" wire:click="addBlock('id', 'paragraph')" class="px-3 py-1.5 text-sm border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50">+ Paragraph</button>
                                    <button type="button" wire:click="addBlock('id', 'image')" class="px-3 py-1.5 text-sm border border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50">+ Image</button>
                                    <button type="button" wire:click="addBlock('id', 'event_info_box')" class="px-3 py-1.5 text-sm border border-red-300 text-red-700 rounded-lg hover:bg-red-50">+ Info Acara</button>
                                    <button type="button" wire:click="addBlock('id', 'agenda_day')" class="px-3 py-1.5 text-sm border border-green-300 text-green-700 rounded-lg hover:bg-green-50">+ Agenda</button>
                                    <button type="button" wire:click="addBlock('id', 'speaker_bio')" class="px-3 py-1.5 text-sm border border-yellow-300 text-yellow-700 rounded-lg hover:bg-yellow-50">+ Pembicara</button>
                                    <button type="button" wire:click="addBlock('id', 'quote')" class="px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">+ Quote</button>
                                </div>
                            </div>

                            {{-- EN Blocks --}}
                            <div x-show="lang === 'en'" class="space-y-4">
                                @foreach ($content_blocks_en as $index => $block)
                                    <div class="border rounded-lg p-4 bg-gray-50" wire:key="block_en_{{ $index }}_{{ $blocksVersion }}">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                                {{ $block['type'] === 'paragraph' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $block['type'] === 'image' ? 'bg-purple-100 text-purple-700' : '' }}
                                                {{ $block['type'] === 'event_info_box' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $block['type'] === 'agenda_day' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $block['type'] === 'speaker_bio' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $block['type'] === 'quote' ? 'bg-gray-200 text-gray-700' : '' }}">
                                                {{ $block['type'] }}
                                            </span>
                                            <div class="flex gap-1">
                                                <button type="button" wire:click="moveBlockUp('en', {{ $index }})" class="px-2 py-1 text-sm border rounded hover:bg-gray-100" {{ $loop->first ? 'disabled' : '' }}>↑</button>
                                                <button type="button" wire:click="moveBlockDown('en', {{ $index }})" class="px-2 py-1 text-sm border rounded hover:bg-gray-100" {{ $loop->last ? 'disabled' : '' }}>↓</button>
                                                <button type="button" wire:click="removeBlock('en', {{ $index }})" class="px-2 py-1 text-sm border rounded text-red-600 hover:bg-red-50">×</button>
                                            </div>
                                        </div>

                                        @switch($block['type'])
                                            @case('paragraph')
                                                @php $editorIdx = $loop->index; @endphp
                                                <div x-data="{
                                                    html: $wire.$entangle('content_blocks_en.{{ $editorIdx }}.data.html'),
                                                    initEditor() {
                                                        let id = 'paragraph_editor_en_{{ $editorIdx }}';
                                                        if (tinymce.get(id)) tinymce.get(id).remove();
                                                        let self = this;
                                                        tinymce.init({
                                                            target: document.getElementById(id),
                                                            plugins: 'advlist link lists code',
                                                            toolbar: 'bold italic underline | bullist numlist | link | undo redo | code',
                                                            menubar: false,
                                                            statusbar: false,
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
                                                }" x-init="initEditor" wire:ignore>
                                                    <textarea id="paragraph_editor_en_{{ $editorIdx }}" rows="5" class="w-full border rounded-lg px-3 py-2 font-mono text-sm"></textarea>
                                                </div>
                                                @break
                                            @case('image')
                                                <div class="space-y-3" x-data="{
                                                    previewUrl: @js(
                                                        (isset($block['data']['src']) && $block['data']['src'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                            ? $block['data']['src']->temporaryUrl()
                                                            : (!empty($block['data']['src']) ? asset('storage/' . $block['data']['src']) : '')
                                                    )
                                                }">
                                                    <div>
                                                        <label class="text-xs font-medium">Upload Image</label>
                                                        <input type="file" accept="image/*"
                                                               x-on:change="
                                                                   let file = $event.target.files[0];
                                                                   if (file) {
                                                                       previewUrl = URL.createObjectURL(file);
                                                                       $wire.upload('content_blocks_en.{{ $index }}.data.src', file, () => {}, () => {}, (event) => {});
                                                                   }"
                                                               class="w-full border rounded-lg px-2 py-1.5 text-sm">
                                                    </div>
                                                    <template x-if="previewUrl">
                                                        <div>
                                                            <img :src="previewUrl"
                                                                 class="h-28 rounded border"
                                                                 :class="{
                                                                     'mx-auto': $wire.content_blocks_en[{{ $index }}].data.alignment === 'center',
                                                                     'ml-0 mr-auto': $wire.content_blocks_en[{{ $index }}].data.alignment === 'left',
                                                                     'ml-auto mr-0': $wire.content_blocks_en[{{ $index }}].data.alignment === 'right',
                                                                     'w-full': $wire.content_blocks_en[{{ $index }}].data.alignment === 'full',
                                                                     'object-cover': $wire.content_blocks_en[{{ $index }}].data.alignment !== 'full'
                                                                 }">
                                                        </div>
                                                    </template>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="text-xs font-medium">Alignment</label>
                                                            <select wire:model="content_blocks_en.{{ $index }}.data.alignment" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                                <option value="center">Center</option>
                                                                <option value="left">Left (float)</option>
                                                                <option value="right">Right (float)</option>
                                                                <option value="full">Full Width</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <label class="text-xs font-medium">Caption</label>
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.caption" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                @break
                                            @case('event_info_box')
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-xs font-medium">Format</label>
                                                        <input type="text" wire:model="content_blocks_en.{{ $index }}.data.format" placeholder="Online / Offline / Hybrid" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Date</label>
                                                        <input type="date" wire:model="content_blocks_en.{{ $index }}.data.date" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Time</label>
                                                        <input type="text" wire:model="content_blocks_en.{{ $index }}.data.time" placeholder="13:00 - 16:00 WIB" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Venue</label>
                                                        <input type="text" wire:model="content_blocks_en.{{ $index }}.data.venue" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Registration Links</label>
                                                        @foreach (($block['data']['registration_links'] ?? []) as $linkIndex => $link)
                                                            <div class="flex gap-2 mb-1" wire:key="reglink_en_{{ $index }}_{{ $linkIndex }}">
                                                                <input type="text" wire:model="content_blocks_en.{{ $index }}.data.registration_links.{{ $linkIndex }}.day" placeholder="Day 1" class="w-1/3 border rounded px-2 py-1 text-sm">
                                                                <input type="url" wire:model="content_blocks_en.{{ $index }}.data.registration_links.{{ $linkIndex }}.url" placeholder="https://..." class="flex-1 border rounded px-2 py-1 text-sm">
                                                                <button type="button" wire:click="removeRegLink('en', {{ $index }}, {{ $linkIndex }})" class="text-red-500 text-sm px-2">×</button>
                                                            </div>
                                                        @endforeach
                                                        <button type="button" wire:click="addRegLink('en', {{ $index }})" class="text-sm text-blue-600 hover:underline">+ Add Link</button>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Notes</label>
                                                        <textarea wire:model="content_blocks_en.{{ $index }}.data.notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                    </div>
                                                </div>
                                                @break
                                            @case('agenda_day')
                                                <div>
                                                    <label class="text-xs font-medium">Day/Date</label>
                                                    <input type="date" wire:model="content_blocks_en.{{ $index }}.data.day" class="w-full border rounded-lg px-3 py-2 text-sm mb-3">
                                                </div>
                                                <label class="text-xs font-medium mb-1 block">Sessions</label>
                                                @foreach (($block['data']['sessions'] ?? []) as $sessionIndex => $session)
                                                    <div class="border-l-2 border-blue-400 pl-3 mb-3" wire:key="session_en_{{ $index }}_{{ $sessionIndex }}">
                                                        <div class="grid grid-cols-4 gap-2 mb-1">
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.time" placeholder="Time" class="border rounded px-2 py-1 text-sm">
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.title" placeholder="Session Title" class="col-span-3 border rounded px-2 py-1 text-sm">
                                                        </div>
                                                        <textarea wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.description" placeholder="Description" rows="2" class="w-full border rounded px-2 py-1 text-sm mb-1"></textarea>
                                                        <div class="grid grid-cols-3 gap-2 text-sm">
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.moderator" placeholder="Moderator" class="border rounded px-2 py-1">
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.commentator" placeholder="Commentator" class="border rounded px-2 py-1">
                                                            <input type="text" wire:model="content_blocks_en.{{ $index }}.data.sessions.{{ $sessionIndex }}.speakers" placeholder="Speakers (comma separated)" class="border rounded px-2 py-1">
                                                        </div>
                                                        <button type="button" wire:click="removeSession('en', {{ $index }}, {{ $sessionIndex }})" class="text-xs text-red-500 hover:underline mt-1">Remove session</button>
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addSession('en', {{ $index }})" class="text-sm text-blue-600 hover:underline">+ Add Session</button>
                                                @break
                                            @case('speaker_bio')
                                                <div class="grid grid-cols-2 gap-3" x-data="{
                                                    photoUrl: @js(
                                                        (isset($block['data']['photo']) && $block['data']['photo'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                            ? $block['data']['photo']->temporaryUrl()
                                                            : (!empty($block['data']['photo']) ? asset('storage/' . $block['data']['photo']) : '')
                                                    )
                                                }">
                                                    <div>
                                                        <label class="text-xs font-medium">Name</label>
                                                        <input type="text" wire:model="content_blocks_en.{{ $index }}.data.name" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Title</label>
                                                        <input type="text" wire:model="content_blocks_en.{{ $index }}.data.title" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium">Upload Photo</label>
                                                        <input type="file" accept="image/*"
                                                               x-on:change="
                                                                   let file = $event.target.files[0];
                                                                   if (file) {
                                                                       photoUrl = URL.createObjectURL(file);
                                                                       $wire.upload('content_blocks_en.{{ $index }}.data.photo', file, () => {}, () => {}, (event) => {});
                                                                   }"
                                                               class="w-full border rounded-lg px-2 py-1.5 text-sm">
                                                        <template x-if="photoUrl">
                                                            <img :src="photoUrl" class="mt-1 h-16 w-16 rounded-full object-cover border">
                                                        </template>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="text-xs font-medium">Bio</label>
                                                        <textarea wire:model="content_blocks_en.{{ $index }}.data.bio" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                    </div>
                                                </div>
                                                @break
                                            @case('quote')
                                                <div>
                                                    <label class="text-xs font-medium">Quote Text</label>
                                                    <textarea wire:model="content_blocks_en.{{ $index }}.data.text" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="text-xs font-medium">Source</label>
                                                    <input type="text" wire:model="content_blocks_en.{{ $index }}.data.source" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                </div>
                                                @break
                                        @endswitch
                                        <div class="flex gap-3 mt-3 pt-3 border-t border-gray-200">
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Margin Top (px)</label>
                                                <input type="number" wire:model="content_blocks_en.{{ $index }}.data.mt" placeholder="auto" class="w-24 border rounded px-2 py-1 text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Margin Bottom (px)</label>
                                                <input type="number" wire:model="content_blocks_en.{{ $index }}.data.mb" placeholder="auto" class="w-24 border rounded px-2 py-1 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="flex flex-wrap gap-2 pt-2">
                                    <button type="button" wire:click="addBlock('en', 'paragraph')" class="px-3 py-1.5 text-sm border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50">+ Paragraph</button>
                                    <button type="button" wire:click="addBlock('en', 'image')" class="px-3 py-1.5 text-sm border border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50">+ Image</button>
                                    <button type="button" wire:click="addBlock('en', 'event_info_box')" class="px-3 py-1.5 text-sm border border-red-300 text-red-700 rounded-lg hover:bg-red-50">+ Event Info</button>
                                    <button type="button" wire:click="addBlock('en', 'agenda_day')" class="px-3 py-1.5 text-sm border border-green-300 text-green-700 rounded-lg hover:bg-green-50">+ Agenda</button>
                                    <button type="button" wire:click="addBlock('en', 'speaker_bio')" class="px-3 py-1.5 text-sm border border-yellow-300 text-yellow-700 rounded-lg hover:bg-yellow-50">+ Speaker</button>
                                    <button type="button" wire:click="addBlock('en', 'quote')" class="px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">+ Quote</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SAVE --}}
                    <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="relative bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed
               text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">

                            {{-- Spinner --}}
                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            {{-- Text --}}
                            <span wire:loading.remove wire:target="save">
                                {{ $page ? '💾 Update' : '🚀 Create' }}
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>

                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- @include('front.components.floating') --}}
</div>