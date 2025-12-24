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