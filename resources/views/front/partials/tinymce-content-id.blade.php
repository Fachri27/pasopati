<div x-show="lang === 'id'" x-data="{
                    content_id: @entangle('content_id'),
                    initEditor() {
                        let self = this;
                        if (tinymce.get('editor_id')) tinymce.get('editor_id').remove();
                        tinymce.init({
                            target: this.$refs.editor_id,
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
                                    editor.setContent(self.content_id || '');
                                });
                                editor.on('change keyup', () => {
                                    self.content_id = editor.getContent();
                                });
                            }
                        });
                    }
                }" x-init="initEditor" wire:ignore>
    <label class="block font-medium mb-1">Content (ID)</label>
    <textarea x-ref="editor_id" id="editor_id"></textarea>
</div>

