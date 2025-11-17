<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-4xl mx-auto bg-white py-10 px-20 rounded-lg shadow-md"
            x-data="{ page_type: @entangle('page_type') }">
            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="{{ route('pages.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                    Page Artikel
                </a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">
                    {{ $page ? '✏️ Edit Page' : 'Tambah Artikel' }}
                </span>
            </nav>

            <h1 class="text-2xl font-bold mb-6 text-gray-700">
                {{ $page ? '✏️ Edit Page' : '➕ Add Page' }}
            </h1>

            {{-- Language Switch --}}
            <div class="mb-6">
                <label class="font-medium text-gray-700">🌐 Pilih Bahasa</label>
                <select x-model="lang" class="border pr-8 py-2 rounded-lg ml-3">
                    <option value="id">Indonesia</option>
                    <option value="en">English</option>
                </select>
            </div>

            {{-- Form Start --}}
            <form wire:submit.prevent="save" class="space-y-6">
                {{-- Title & Slug (ID) --}}
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

                {{-- Published Date + Page Type --}}
                <div class="flex items-center space-x-2">
                    <div class="w-1/2">
                        <label class="block font-medium mb-1">📅 Tanggal Publikasi</label>
                        <input type="date" wire:model="published_at"
                            value="{{ old('published_at', $published_at ?? '') }}"
                            class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                        @error('published_at') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="w-1/2">
                        <label class="block font-medium mb-1">Page Type</label>
                        <select wire:model="page_type"
                            class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                            <option value="expose">Expose</option>
                            <option value="ngopini">Ngopini</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="font-semibold">Source Type</label>
                    <select wire:model="source_type" class="border p-2 rounded w-full">
                        <option value="manual">Manual</option>
                        <option value="pdf">Import dari PDF</option>
                        <option value="docx">Import dari Word (DOCX)</option>
                    </select>
                </div>

                <div class="mt-2" x-show="lang === 'id'">
                    <label class="font-semibold">Upload File ID (optional)</label>
                    <input type="file" wire:model="file_import_id" accept=".pdf,.docx" class="w-full border rounded p-2"
                        {{-- kalau source_type bukan manual, baru wajib --}} @if($source_type !=='manual' ) required
                        @endif>
                    @error('file_import_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-2" x-show="lang === 'en'">
                    <label class="font-semibold">Upload File EN (optional)</label>
                    <input type="file" wire:model="file_import_en" accept=".pdf,.docx" class="w-full border rounded p-2"
                        {{-- kalau source_type bukan manual, baru wajib --}} @if($source_type !=='manual' ) required
                        @endif>
                    @error('file_import_en')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>



                {{-- Featured Image --}}
                <div class="w-full max-w-full my-5">
                    <label class="block font-medium mb-1">Image</label>
                    <input type="file" wire:model="featured_image" class="border p-2 rounded-lg w-full">

                    <div class="mt-3">
                        @if ($featured_image)
                        {{-- Preview gambar baru --}}
                        <img src="{{ $featured_image->temporaryUrl() }}" alt="New Upload Preview"
                            class="w-16 h-16 rounded-full object-cover border">
                        @elseif ($old_featured_image)
                        {{-- Preview gambar lama --}}
                        <img src="{{ asset('storage/' . $old_featured_image) }}" alt="Current Image"
                            class="w-16 h-16 rounded-full object-cover border">
                        @endif
                    </div>
                </div>


                {{-- Type & Status --}}
                <div class="flex items-center space-x-2">
                    <div class="w-1/2">
                        <label class="block font-medium mb-1">Type</label>
                        <select wire:model="type"
                            class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                            <option value="parallax">Parallax</option>
                            <option value="default">Default</option>
                        </select>
                    </div>

                    <div class="w-1/2">
                        <label class="block font-medium mb-1">Status</label>
                        <select wire:model="status"
                            class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                {{-- excarpt ID --}}
                <div x-show="lang === 'id'" x-data="{
                    excerpt_id: @entangle('excerpt_id'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('excerpt_editor_id')) tinymce.get('excerpt_editor_id').remove();
                        tinymce.init({
                            target: this.$refs.excerpt_editor_id,
                            plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                            toolbar: 'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                            menubar: 'file edit view insert format tools table',
                            skin: true,
                            content_css: true,
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
                            forced_root_block: 'p',
                            valid_elements: '*[*]',
                            forced_root_block: '',
                            br_in_pre: false,
                            entity_encoding: 'raw',
                            setup(editor) {
                                editor.on('init', () => {
                                    editor.setContent(self.excerpt_id || '');
                                });
                                editor.on('change keyup', () => {
                                    self.excerpt_id = editor.getContent();
                                });
                            },
                        });
                    }
                }" x-init="initEditor" wire:ignore>
                    <label class="block font-medium mb-1">Excerpt (ID)</label>
                    <textarea x-ref="excerpt_editor_id" id="excerpt_editor_id"></textarea>
                </div>



                {{-- Excarpt EN --}}
                <div x-show="lang === 'en'" x-data="{
                    excerpt_en: @entangle('excerpt_en'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('excerpt_editor_en')) tinymce.get('excerpt_editor_en').remove();
                        tinymce.init({
                            target: this.$refs.excerpt_editor_en,
                            plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                            toolbar: 'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                            menubar: 'file edit view insert format tools table',
                            skin: true,
                            content_css: true,
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
                            forced_root_block: 'p',
                            valid_elements: '*[*]',
                            forced_root_block: '',
                            br_in_pre: false,
                            entity_encoding: 'raw',
                            setup(editor) {
                                editor.on('init', () => {
                                    editor.setContent(self.excerpt_en || '');
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
                </div>


                {{-- Content ID --}}
                <div x-show="lang === 'id'" x-data="{
                    content_id: @entangle('content_id'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('editor_id')) tinymce.get('editor_id').remove();
                        tinymce.init({
                            target: this.$refs.editor_id,
                            plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                            toolbar: 'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                            menubar: 'file edit view insert format tools table',
                            skin: true,
                            content_css: true,
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
                            forced_root_block: 'p',
                            valid_elements: '*[*]',
                            forced_root_block: '',
                            br_in_pre: false,
                            entity_encoding: 'raw',
                            setup(editor) {
                                editor.on('init', () => {
                                    editor.setContent(self.content_id || '');
                                });
                                editor.on('change keyup', () => {
                                    self.content_id = editor.getContent();
                                });
                            },
                            file_picker_callback(callback, value, meta) {
                                let cmsURL = '/laravel-filemanager?editor=' + meta.fieldname;
                                cmsURL += meta.filetype === 'image'
                                    ? '&type=image'
                                    : meta.filetype === 'media'
                                    ? '&type=video'
                                    : '&type=file';
                                tinymce.activeEditor.windowManager.openUrl({
                                    url: cmsURL,
                                    title: 'File Manager',
                                    width: window.innerWidth * 0.8,
                                    height: window.innerHeight * 0.8,
                                    onMessage: (api, message) => callback(message.url || message.content)
                                });
                            }
                        });
                    }
                }" x-init="initEditor" wire:ignore>
                    <label class="block font-medium mb-1">Content (ID)</label>
                    <textarea x-ref="editor_id" id="editor_id"></textarea>
                </div>




                {{-- Content EN --}}
                <div x-show="lang === 'en'" x-data="{
                    content_en: @entangle('content_en'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('editor_en')) tinymce.get('editor_en').remove();
                        tinymce.init({
                            target: this.$refs.editor_en,
                            plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                            toolbar: 'undo redo | styles | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                            menubar: 'file edit view insert format tools table',
                            skin: true,
                            content_css: true,
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
                            forced_root_block: 'p',
                            valid_elements: '*[*]',
                            forced_root_block: '',
                            br_in_pre: false,
                            entity_encoding: 'raw',
                            setup(editor) {
                                editor.on('init', () => {
                                    editor.setContent(self.content_en || '');
                                });
                                editor.on('change keyup', () => {
                                    self.content_en = editor.getContent();
                                });
                            },
                            file_picker_callback(callback, value, meta) {
                                let cmsURL = '/laravel-filemanager?editor=' + meta.fieldname;
                                cmsURL += meta.filetype === 'image'
                                    ? '&type=image'
                                    : meta.filetype === 'media'
                                    ? '&type=video'
                                    : '&type=file';
                                tinymce.activeEditor.windowManager.openUrl({
                                    url: cmsURL,
                                    title: 'File Manager',
                                    width: window.innerWidth * 0.8,
                                    height: window.innerHeight * 0.8,
                                    onMessage: (api, message) => callback(message.url || message.content)
                                });
                            }
                        });
                    }
                }" x-init="initEditor" wire:ignore>
                    <label class="block font-medium mb-1">Content (EN)</label>
                    <textarea x-ref="editor_en" id="editor_en"></textarea>
                </div>

                <div x-show="page_type === 'expose'">
                    <div class="w-full">
                        <label class="block font-medium mb-1">Expose Type</label>
                        <select wire:model="expose_type"
                            class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400">
                            <option value="deforetasi">Deforestasi</option>
                            <option value="kebakaran">Kebakaran</option>
                            <option value="pulp">Pulp & paper</option>
                            <option value="mining">Mining & energy</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    {{ $page ? '💾 Update' : '🚀 Create' }}
                </button>
            </form>
        </div>
    </div>
</div>