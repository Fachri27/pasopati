<div x-show="lang === 'id'" x-data="{
                    content_id: @entangle('content_id'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('editor_id')) tinymce.get('editor_id').remove();
                        tinymce.init({
                            target: this.$refs.editor_id,
                            plugins: 'advlist anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen insertdatetime help preview',
                            toolbar: 'undo redo | styles | addImage | addVideo | addBorderMerah | addSlider | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code removeformat | fullscreen preview',
                            extended_valid_elements: 'iframe[src|width|height|frameborder|allowfullscreen|style|class|loading|referrerpolicy]',
                            verify_html: false,
                            forced_root_block: '',
                            allow_html_in_named_anchor: true,
                            cleanup: false,
                            valid_children: '+body[iframe]',
                            sandbox_iframes: false,

                            // Jangan hapus atribut iframe saat paste
                            paste_enable_default_filters: false,
                            paste_as_text: false,

                            content_style: 'iframe { width:100%; height:400px; }',

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
                            valid_elements: '*[*]',
                            br_in_pre: false,
                            entity_encoding: 'raw',
                            setup(editor) {
                                editor.on('init', () => {
                                    editor.setContent(self.content_id || '');
                                });
                                editor.on('change keyup', () => {
                                    self.content_id = editor.getContent();
                                });

                                {{-- editor.ui.registry.addButton('addImage', {
                                    text: '+ Tambah Image',
                                    onAction: function () {
                                        editor.insertContent(`
                                        <section class='add-vidio'
                                            <div class='caption-foto' style='line-height: 80% !important;'><span style='color:#000000;'>
                                                <video autoplay='' controls='' loop='' muted='' style='max-width: 1000px; display: block; margin: 0 auto;' width='100%'><source src='https://placehold.co/800x450' type='video/mp4'></video>
                                                <small style='font-size: 12px !important;''>Deforestasi di wilayah konsesi PT Industrial Forest Plantation, 2021–25 © Auriga / Earthsight. Sumber gambar: Sentinel-2 melalui Google Earth Engine</small></span></div>
                                                
                                        </section>
                                        `);
                                    }
                                }); --}}

                                editor.ui.registry.addButton('addVideo', {
                                    text: '+ Tambah Video',
                                    onAction: function () {
                                        editor.insertContent(`
                                    <figure class='media-caption'>

                                        <video autoplay='' controls='' loop='' muted='' style='max-width: 1000px; display: block; margin: 0 auto;' width='100%'><source src='https://placehold.co/800x450' type='video/mp4'></video>

                                        <figcaption class='media-caption-text'>
                                            Deforestasi di wilayah konsesi PT Industrial Forest Plantation, 2021–25 © Auriga / Earthsight.
                                            Sumber gambar: Sentinel-2 melalui Google Earth Engine
                                        </figcaption>

                                    </figure>
                                            `);
                                        }
                                });

                                editor.ui.registry.addButton('addImage', {
                                    text: '+ Tambah Image',
                                    onAction: function () {
                                        editor.insertContent(`
                                    <figure class='media-caption'>

                                        <img alt='' data-widget='image' height='100%' src='https://placehold.co/800x450' width='100%'>
                                        <figcaption class='media-caption-text'>
                                            Deforestasi di wilayah konsesi PT Industrial Forest Plantation, 2021–25 © Auriga / Earthsight.
                                            Sumber gambar: Sentinel-2 melalui Google Earth Engine
                                        </figcaption>

                                    </figure>
                                            `);
                                        }
                                });

                                editor.ui.registry.addButton('addBorderMerah', {
                                    text: '+ Tambah Border',
                                    onAction: function () {
                                        editor.insertContent(`
                                        <div style='border: 1px solid red; padding: 20px;'>
                                            Konten
                                        </div>

                                            `);
                                        }
                                });

                                editor.ui.registry.addButton('addSlider', {
                                    text: '+ Tambah Slider',
                                    onAction: function () {
                                        editor.insertContent(`
                                    <div class='tmce-slider' data-index='0'>
                                        <div class='tmce-slides'>
                                            <figure class='active'>
                                                <img alt='' data-widget='image' height='100%' src='https://placehold.co/800x450' width='100%'>
                                                <figcaption class='media-caption-text'>Caption gambar pertama</figcaption>
                                            </figure>

                                                <figure>
                                                    <img alt='' data-widget='image' height='100%' src='https://placehold.co/800x450' width='100%'>
                                                <figcaption class='media-caption-text'>Caption gambar kedua</figcaption>
                                            </figure>

                                                <figure>
                                                    <img alt='' data-widget='image' height='100%' src='https://placehold.co/800x450' width='100%'>
                                                <figcaption class='media-caption-text'>Caption gambar ketiga</figcaption>
                                            </figure>
                                        </div>

                                        <div class='tmce-controls'>
                                            <button class='prev'>Prev</button>
                                            <button class='next'>Next</button>
                                        </div>
                                    </div>


                                            `);
                                        }
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

