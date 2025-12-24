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
                <input type="checkbox" :value="cat.id" x-model="selectedCategories" @change="toggleCategory($event)">
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
                    <option value="">Pilih Status</option>
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </template>
</div>