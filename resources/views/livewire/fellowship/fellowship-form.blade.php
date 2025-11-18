<div x-data="{ lang: 'id', selectedCategories: [] }">
    <div class="max-w-5xl mx-auto bg-white py-10 mb-20 px-20 rounded-lg shadow-md">
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="{{ route('fellowship.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                Page Fellowship
            </a>
            <span class="text-gray-400">›</span>
            <span class="text-blue-600 font-semibold">Tambah Fellowship</span>
        </nav>
        <h1 class="text-2xl font-bold mb-6 text-gray-700">
            {{ isset($fellowship) ? '✏️ Edit Fellowship' : '➕ Add Fellowship' }}
        </h1>
        {{-- Language Switch --}}
        <div class="mb-6">
            <label class="font-medium text-gray-700">🌐 Pilih Bahasa</label>
            <select x-model="lang" class="border pr-8 py-2 rounded-lg ml-3">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
            </select>
        </div>
        <form wire:submit.prevent='save' class="space-y-6">
            {{-- Title & Slug --}}
            <div x-show="lang === 'id'">
                <div x-data="{
            title: @js(old('title_id', $title_id ?? '')),
            slug: @js(old('slug', $slug ?? '')),
            makeSlug(text) {
                return text
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                    <label class="block font-medium mb-1">Title (ID)</label>
                    <input type="text" wire:model="title_id" name="title_id" x-model="title"
                        @input="slug = makeSlug(title)"
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                    @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

                    <label class="block font-medium mb-1 mt-3">Slug</label>
                    <input type="text" name="slug" x-model="slug" readonly
                        class="w-full border px-3 py-2 bg-gray-100 rounded-lg">
                </div>
            </div>

            {{-- Title & Slug (EN) --}}
            <div x-show="lang === 'en'">
                <div x-data="{
            title: @js(old('title_en', $title_en ?? '')),
            makeSlug(text) {
                return text
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                    <label class="block font-medium mb-1">Title (EN)</label>
                    <input type="text" wire:model="title_en" name="title_en" x-model="title"
                        @input="slug = makeSlug(title)"
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                    @error('title_en') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- sub-judul --}}
            <div x-show="lang === 'id'">
                <label class="block font-medium mb-1">Sub-judul (ID)</label>
                <input type="text" wire:model="sub_judul_id" name="sub_judul_id"
                    class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                {{-- @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror --}}
            </div>
            <div x-show="lang === 'en'">
                <label class="block font-medium mb-1">Sub-judul (EN)</label>
                <input type="text" wire:model="sub_judul_en" name="sub_judul_en"
                    class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                {{-- @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror --}}
            </div>

            {{-- Date --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">📅 Tanggal Mulai</label>
                    <input type="date" name="start_date" wire:model="start_date"
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                    @error('start_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

                </div>
                <div>
                    <label class="block font-medium mb-1">📅 Tanggal Selesai</label>
                    <input type="date" name="end_date" wire:model="end_date"
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                </div>
            </div>


            {{-- Status (ENUM) --}}
            <div>
                <label class="block font-medium mb-1">Status</label>
                <select wire:model="status" class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            @error('status') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            {{-- excarpt ID --}}
            <div x-show="lang === 'id'" x-data="{
        excerpt_id: @entangle('excerpt_id'),
        initEditor() {
            let self = this;
            if (tinymce.get('excerpt_editor_id')) tinymce.get('excerpt_editor_id').remove();
            tinymce.init({
                target: this.$refs.excerpt_editor_id,
                plugins:'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                toolbar:'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                menubar:'file edit view insert format tools table',
                skin: 'oxide',
                content_css: false,
                license_key: 'gpl',
                style_formats:[
                    {title:'Text Styles',items:[
                    {title:'Paragraph',format:'p'},
                    {title:'Headings',items:[{title:'H1',format:'h1'},{title:'H2',format:'h2'},{title:'H3',format:'h3'},{title:'H4',format:'h4'},{title:'H5',format:'h5'},{title:'H6',format:'h6'}]},
                    {title:'Inline',items:[{title:'Bold',inline:'b'},{title:'Italic',inline:'i'},{title:'Underline',inline:'u'},{title:'Strikethrough',inline:'strike'}]}
                    ]}
                ],
                block_formats:'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
                toolbar_sticky:true,
                promotion:false,
                branding:false,
                statusbar:true,
                elementpath:false,
                resize:true,
                forced_root_block:'p',
                setup(editor) {
                editor.on('init', () => {
                    // langsung set nilai awal
                    editor.setContent(self.excerpt_id || '');
                });
                editor.on('change keyup', () => {
                    self.excerpt_id = editor.getContent();
                });
            }
            });
        }
     }" x-init="initEditor" wire:ignore>

                <label class="block font-medium mb-1">Excerpt ID</label>
                <textarea x-ref="excerpt_editor_id" id="excerpt_editor_id"></textarea>
                <input type="hidden" name="excerpt_id" :value="excerpt_id">
            </div>


            {{-- Excarpt EN --}}
            <div x-show="lang === 'en'" x-data="{
        excerpt_en: @entangle('excerpt_en'),
        initEditor() {
            let self = this;
            if (tinymce.get('excerpt_editor_en')) tinymce.get('excerpt_editor_en').remove();
            tinymce.init({
                target: this.$refs.excerpt_editor_en,
                plugins:'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                toolbar:'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                menubar:'file edit view insert format tools table',
                skin: 'oxide',
                content_css: false,
                license_key: 'gpl',
                style_formats:[
                    {title:'Text Styles',items:[
                    {title:'Paragraph',format:'p'},
                    {title:'Headings',items:[{title:'H1',format:'h1'},{title:'H2',format:'h2'},{title:'H3',format:'h3'},{title:'H4',format:'h4'},{title:'H5',format:'h5'},{title:'H6',format:'h6'}]},
                    {title:'Inline',items:[{title:'Bold',inline:'b'},{title:'Italic',inline:'i'},{title:'Underline',inline:'u'},{title:'Strikethrough',inline:'strike'}]}
                    ]}
                ],
                block_formats:'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
                toolbar_sticky:true,
                promotion:false,
                branding:false,
                statusbar:true,
                elementpath:false,
                resize:true,
                forced_root_block:'p',
                setup(editor) {
                    editor.on('init', () => {
                        setTimeout(() => {
                            editor.setContent(self.excerpt_en || '');
                        }, 100);
                    });
                    editor.on('change keyup', () => {
                        self.excerpt_en = editor.getContent();
                    });
                },
            });
        }
     }" x-init="initEditor" wire:ignore>

                <label class="block font-medium mb-1">Excerpt (EN)</label>
                <textarea x-ref="excerpt_editor_en" id="excerpt_editor_en"></textarea>
                <input type="hidden" name="excerpt_en" :value="excerpt_en">
            </div>

            <div x-data="{
                    categories: {{ Js::from($categoriesData) }},
                    selectedCategories: @entangle('selectedCategories'),
                    contents: @entangle('selectedKategori'),
    
                    // toggle saat user klik checkbox
                    toggleCategory(event) {
                    let catId = event.target.value;
                    if (event.target.checked) {
                        if (!this.selectedCategories.includes(catId)) this.selectedCategories.push(catId);
                        this.$nextTick(() => {
                        this.initEditor(catId, 'id');
                        this.initEditor(catId, 'en');
                        });
                    } else {
                        this.selectedCategories = this.selectedCategories.filter(c => c !== catId);
                        if (tinymce.get('content_id_' + catId)) tinymce.get('content_id_' + catId).remove();
                        if (tinymce.get('content_en_' + catId)) tinymce.get('content_en_' + catId).remove();
                    }
                    },
    
                    // inisialisasi TinyMCE untuk textarea tertentu
                    initEditor(catId, locale) {
                    let textareaId = 'content_' + locale + '_' + catId;
                    let self = this;
    
                    if (tinymce.get(textareaId)) tinymce.get(textareaId).remove();
    
                    tinymce.init({
                        selector: '#' + textareaId,
                        plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                        toolbar: 
                            'undo redo | styles | addLiputanBlock | addMentorBlock | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                        menubar: 'file edit view insert format tools table',
                        skin: 'oxide',
                        content_css: false,
                        license_key: 'gpl',
                        style_formats:[
                            {title:'Text Styles',items:[
                            {title:'Paragraph',format:'p'},
                            {title:'Headings',items:[{title:'H1',format:'h1'},{title:'H2',format:'h2'},{title:'H3',format:'h3'},{title:'H4',format:'h4'},{title:'H5',format:'h5'},{title:'H6',format:'h6'}]}
                            ]}
                        ],
                        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
                        toolbar_sticky: true,
                        promotion: false,
                        branding: false,
                        statusbar: true,
                        elementpath: false,
                        resize: true,
                        valid_elements: '*[*]',
                        forced_root_block: '',
                        br_in_pre: false,
                        entity_encoding: 'raw',
    
                        // ✅ Integrasi LFM
                        file_picker_callback(callback, value, meta) {
                        let cmsURL = '/laravel-filemanager?editor=' + meta.fieldname;
    
                        if (meta.filetype === 'image') {
                            cmsURL += '&type=image';
                        } else if (meta.filetype === 'media') {
                            cmsURL += '&type=video';
                        } else {
                            cmsURL += '&type=file';
                        }
    
                        tinymce.activeEditor.windowManager.openUrl({
                            url: cmsURL,
                            title: 'File Manager',
                            width: window.innerWidth * 0.8,
                            height: window.innerHeight * 0.8,
                            onMessage: (api, message) => {
                            // fix: ambil message.url (LFM kirim url file)
                            callback(message.url || message.content);
                            }
                        });
                        },
    
                        setup(editor) {
                            editor.on('init', () => {
                                let existingContent =
                                (self.contents[catId] && self.contents[catId][locale])
                                ? self.contents[catId][locale]
                            : '';
    
                        // 🧩 Template otomatis khusus kategori 4
                        editor.setContent(existingContent);
    
                       editor.ui.registry.addButton('addLiputanBlock', {
    text: '+ Tambah Blok Liputan',
    onAction: function () {
        editor.insertContent(`
<section class='liputan-blok flex flex-col md:flex-row md:gap-3 gap-2 border-b pb-4'>

    <!-- Gambar -->
    <div class='liputan-gambar w-full md:w-2/5'>
        <img src='https://placehold.co/800x450'
             class='w-full h-auto object-cover rounded'>
    </div>

    <!-- Info -->
    <div class='liputan-info flex-1 mt-1 md:mt-0'>
        <h2 class='text-[20px] font-bold leading-tight'>Judul Liputan</h2>

        <div class='text-gray-700 leading-snug text-[15px]'>
            Tulis deskripsi singkat di sini. Misalnya ringkasan dari isi liputan...
        </div>

        <div class='text-red-600 font-semibold text-[14px] mt-1'>
            Nama Penulis
        </div>
    </div>

</section>
        `);
    }
});



                        editor.ui.registry.addButton('addMentorBlock', {
                            text: '+ Tambah Blok Mentor',
                            onAction: function () {
                                editor.insertContent(`
                                <section class='mentor-block'
                                    style='display:flex; flex-wrap:wrap; align-items:flex-start; gap:16px; margin:20px 0; border-bottom:1px solid #ddd; padding-bottom:20px;'>
                                    
                                    <!-- Foto Mentor -->
                                    <div style='flex:0 0 120px; width:120px; height:120px; overflow:hidden; border-radius:50%;'>
                                        <img src='https://placehold.co/150x150' alt='Gambar'
                                            style='width:100%; height:100%; object-fit:cover; border-radius:50%; display:block;'>
                                    </div>

                                    <!-- Info Mentor -->
                                    <div style='flex:1; min-width:250px;'>
                                        <h2 style='font-size:18px; font-weight:600; margin:0 0 6px;'>Nama Mentor</h2>
                                        <p style='color:#333; line-height:1.6; font-size:14px; margin:0;'>
                                            Tulis deskripsi singkat di sini. Misalnya ringkasan profil atau pengalaman mentor.
                                        </p>
                                    </div>
                                </section>
                                `);
                            }
                        });

                    });
    
                    editor.on('change keyup', () => {
                        if (!self.contents[catId]) self.contents[catId] = {};
                        self.contents[catId][locale] = editor.getContent();

                        if (window.Livewire) {
                            Livewire.emit('updateCategoryContent', {
                                catId: catId,
                                locale: locale,
                                content: editor.getContent(),
                            });
                        }
                    });
                    }
    
                    });
                    },
    
                    // saat page load (edit) -> init editor untuk semua kategori yg sudah terpilih
                    initSelected() {
                    this.$nextTick(() => {
                        this.selectedCategories.forEach(catId => {
                        this.initEditor(catId, 'id');
                        this.initEditor(catId, 'en');
                        });
                    });
                    }
                }" x-init="initSelected()" class="p-4" wire:ignore>

                <!-- daftar kategori -->
                <label class="block font-medium mb-2">Kategori</label>
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="cat in categories" :key="cat.id">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" :value="cat.id" x-model="selectedCategories"
                                @change="toggleCategory($event)">
                            <span x-show="lang === 'id'" x-text="cat.id_name"></span>
                            <span x-show="lang === 'en'" x-text="cat.en_name"></span>
                        </label>
                    </template>
                </div>

                <!-- textarea per kategori terpilih -->
                <template x-for="catId in selectedCategories" :key="catId">
                    <div class="mt-4 border p-3 rounded">
                        <h3 class="font-semibold" x-text="categories.find(c => c.id == catId)[(lang + '_name')]">
                        </h3>

                        <div x-show="lang === 'id'">
                            <template x-if="selectedCategories.includes(catId)">
                                <div>
                                    <textarea :id="'content_id_' + catId"></textarea>
                                    <input type="hidden" :name="'categories['+catId+'][content_id]'"
                                        :value="contents[catId] ? contents[catId]['id'] : ''">
                                </div>
                            </template>
                        </div>

                        <div x-show="lang === 'en'">
                            <template x-if="selectedCategories.includes(catId)">
                                <div>
                                    <textarea :id="'content_en_' + catId"></textarea>
                                    <input type="hidden" :name="'categories['+catId+'][content_en]'"
                                        :value="contents[catId] ? contents[catId]['en'] : ''">
                                </div>
                            </template>
                        </div>
                        <!-- Status Pivot -->
                        <div class="mt-3">
                            <label class="block text-sm font-medium">Status Pivot</label>
                            <select class="border rounded px-2 py-1" x-model="contents[catId].status"
                                @change="$wire.selectedKategori[catId] = Object.assign($wire.selectedKategori[catId] || {}, { status: contents[catId].status })">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </template>
            </div>


            <div class="flex flex-col sm:flex-row items-center gap-4">
                <div class="max-w-full w-full"
                    x-data="{ fileName: @js(isset($fellowship) && $fellowship->image ? basename($fellowship->image) : 'No File Selected') }">
                    <label class="block font-medium mb-1">Image</label>
                    <div class="relative group">
                        <input type="file" name="image" id="image" wire:model="image"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            @change="fileName = $event.target.files.length ? $event.target.files[0].name : 'No File Selected'">
                        <div class="flex items-center justify-between w-full border border-gray-300 shadow-sm rounded-md bg-white
                        transition-all duration-200 ease-in-out
                        focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-400/50
                        group-hover:shadow-md">
                            <div class="flex items-center gap-2">
                                <div class="bg-gray-100 p-2 rounded-lg text-gray-500">
                                    📁
                                </div>
                                <span x-text="fileName" class="text-gray-500 text-sm truncate w-44"></span>
                            </div>

                            <div class="text-gray-400">
                                📎
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                @if (is_object($image))
                @if ($image)
                {{-- Preview gambar baru --}}
                <img src="{{ $image->temporaryUrl() }}" alt="New Upload Preview"
                    class="w-16 h-16 rounded-full object-cover border">
                @elseif ($old_image)
                {{-- Preview gambar lama --}}
                <img src="{{ asset('storage/' . $old_image) }}" alt="Current Image"
                    class="w-16 h-16 rounded-full object-cover border">
                @endif
                @endif
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                {{ $fellowship ? '💾 Update' : '🚀 Create' }}
            </button>
        </form>
    </div>
    @include('front.components.floating')
</div>