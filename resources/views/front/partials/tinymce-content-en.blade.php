<div x-show="lang === 'en'" x-data="{
                    content_en: @entangle('content_en'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('editor_en')) tinymce.get('editor_en').remove();
                        tinymce.init({
                            target: this.$refs.editor_en,
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
                                    editor.setContent(self.content_en || '');
                                });
                                editor.on('change keyup', () => {
                                    self.content_en = editor.getContent();
                                });
                            }
                        });
                    }
                }" x-init="initEditor" wire:ignore>
    <label class="block font-medium mb-1">Content (EN)</label>
    <textarea x-ref="editor_en" id="editor_en"></textarea>
</div>