/**
 * Editor TinyMCE untuk CMS — port dari editor Deforestory Simontini.
 *
 * TinyMCE sendiri dimuat sebagai skrip global di layouts/admin.blade.php
 * (public/js/tinymce/tinymce.min.js), bukan lewat bundel ini, jadi berkas ini
 * memakainya dari window. Berkas ini ikut termuat di halaman publik yang tidak
 * punya TinyMCE maupun editor; semua penginisialisasi di bawah karena itu
 * menjaga diri sendiri dan berhenti kalau tidak ada [data-tinymce-wrapper].
 *
 * Tiga tombol galeri (+ Image, + GLightbox, + Before/After) mengambil gambar
 * dari halaman Reference yang dibuka di dalam <iframe> modal; pilihan kembali
 * ke sini lewat postMessage, dengan localStorage sebagai jalur cadangan saat
 * postMessage tidak sampai.
 */

const inlineCaptionSelectionRanges = new WeakMap();

const getInlineCaptionShortcutCommand = (event) => {
    if ((!event.metaKey && !event.ctrlKey) || event.altKey) return null;

    const shortcutKey = event.key.toLowerCase();
    if (shortcutKey === 'b') return 'Bold';
    if (shortcutKey === 'i') return 'Italic';
    if (shortcutKey === 'u') return 'Underline';

    return null;
};

const isInlineCaptionRangeInside = (editableElement, range) => {
    if (!editableElement || !range) return false;

    const startElement = range.startContainer.nodeType === window.Node.ELEMENT_NODE
        ? range.startContainer
        : range.startContainer.parentElement;
    const endElement = range.endContainer.nodeType === window.Node.ELEMENT_NODE
        ? range.endContainer
        : range.endContainer.parentElement;

    return !!startElement
        && !!endElement
        && (editableElement === startElement || editableElement.contains(startElement))
        && (editableElement === endElement || editableElement.contains(endElement));
};

const saveInlineCaptionSelection = (editableElement) => {
    const selection = window.getSelection();
    if (!selection?.rangeCount) return;

    const range = selection.getRangeAt(0);
    if (range.collapsed || !isInlineCaptionRangeInside(editableElement, range)) return;

    inlineCaptionSelectionRanges.set(editableElement, range.cloneRange());
};

const applyInlineCaptionCommand = (editableElement, command, preferredRange = null) => {
    if (!editableElement || !command) return false;

    const selection = window.getSelection();
    const currentRange = selection?.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
    const selectedRange = isInlineCaptionRangeInside(editableElement, currentRange)
        ? currentRange
        : (isInlineCaptionRangeInside(editableElement, preferredRange) ? preferredRange.cloneRange() : null);
    const commandMap = {
        Bold: 'bold',
        Italic: 'italic',
        Underline: 'underline',
        InsertUnorderedList: 'insertUnorderedList',
        InsertOrderedList: 'insertOrderedList',
        Unlink: 'unlink',
        RemoveFormat: 'removeFormat',
    };

    const restoreSelection = () => {
        editableElement.focus({ preventScroll: true });
        if (!selectedRange || !selection) return;

        selection.removeAllRanges();
        selection.addRange(selectedRange);
    };

    restoreSelection();

    const getElementFromNode = (node) => {
        if (!node) return null;
        return node.nodeType === window.Node.ELEMENT_NODE ? node : node.parentElement;
    };
    const hasFormat = (element, formatCommand) => {
        if (!element || element === editableElement) return false;

        const tagName = element.tagName?.toLowerCase();
        const inlineStyle = element.getAttribute('style') || '';

        if (formatCommand === 'Bold') {
            return tagName === 'strong'
                || tagName === 'b'
                || /font-weight\s*:\s*(bold|[6-9]00|700)/i.test(inlineStyle);
        }

        if (formatCommand === 'Italic') {
            return tagName === 'em'
                || tagName === 'i'
                || /font-style\s*:\s*italic/i.test(inlineStyle);
        }

        if (formatCommand === 'Underline') {
            return tagName === 'u'
                || /text-decoration(?:-line)?\s*:[^;]*underline/i.test(inlineStyle);
        }

        return false;
    };
    const getClosestFormatWrapper = (range, formatCommand) => {
        const getClosestFromNode = (node) => {
            let element = getElementFromNode(node);

            while (element && element !== editableElement) {
                if (hasFormat(element, formatCommand)) return element;
                element = element.parentElement;
            }

            return null;
        };
        const startWrapper = getClosestFromNode(range.startContainer);
        const endWrapper = getClosestFromNode(range.endContainer);
        if (startWrapper && startWrapper === endWrapper) return startWrapper;

        let element = getElementFromNode(range.commonAncestorContainer);

        while (element && element !== editableElement) {
            if (hasFormat(element, formatCommand)) return element;
            element = element.parentElement;
        }

        return null;
    };
    const unwrapFormatWrapper = (wrapperElement) => {
        const firstChild = wrapperElement.firstChild;
        const lastChild = wrapperElement.lastChild;
        if (!firstChild || !lastChild) {
            wrapperElement.remove();
            return true;
        }

        const nextRange = document.createRange();
        const parent = wrapperElement.parentNode;
        while (wrapperElement.firstChild) {
            parent.insertBefore(wrapperElement.firstChild, wrapperElement);
        }
        nextRange.setStartBefore(firstChild);
        nextRange.setEndAfter(lastChild);
        wrapperElement.remove();
        selection.removeAllRanges();
        selection.addRange(nextRange);
        inlineCaptionSelectionRanges.set(editableElement, nextRange.cloneRange());
        return true;
    };

    const selectionIsInsideEditable = selectedRange
        && !selectedRange.collapsed
        && (editableElement === selectedRange.commonAncestorContainer || editableElement.contains(selectedRange.commonAncestorContainer));
    const richTextWrappers = {
        Bold: {
            tagName: 'strong',
            styles: { fontWeight: '700' },
        },
        Italic: {
            tagName: 'em',
            styles: { fontStyle: 'italic' },
        },
        Underline: {
            tagName: 'span',
            styles: { textDecoration: 'underline' },
        },
    };

    if (selectionIsInsideEditable && richTextWrappers[command]) {
        const existingFormatWrapper = getClosestFormatWrapper(selectedRange, command);
        if (existingFormatWrapper) return unwrapFormatWrapper(existingFormatWrapper);

        const wrapperConfig = richTextWrappers[command];
        const wrapperElement = document.createElement(wrapperConfig.tagName);
        Object.assign(wrapperElement.style, wrapperConfig.styles);
        wrapperElement.append(selectedRange.extractContents());
        selectedRange.insertNode(wrapperElement);

        const nextRange = document.createRange();
        nextRange.selectNodeContents(wrapperElement);
        selection.removeAllRanges();
        selection.addRange(nextRange);
        inlineCaptionSelectionRanges.set(editableElement, nextRange.cloneRange());
        return true;
    }

    if (command === 'mceLink') {
        const url = window.prompt('Masukkan URL link');
        if (!url) return false;

        restoreSelection();
        document.execCommand('createLink', false, url);
        return true;
    }

    const nativeCommand = commandMap[command];
    if (!nativeCommand) return false;

    document.execCommand(nativeCommand, false, null);
    return true;
};

const applyTinyMceCaptionCommand = (captionEditor, editableElement, command, preferredRange = null) => {
    if (!captionEditor) return applyInlineCaptionCommand(editableElement, command, preferredRange);

    captionEditor.focus();

    const formatterMap = {
        Bold: 'bold',
        Italic: 'italic',
        Underline: 'underline',
    };

    if (formatterMap[command]) {
        captionEditor.formatter.toggle(formatterMap[command]);
        captionEditor.dispatch('change');
        return true;
    }

    if (command === 'mceLink') {
        captionEditor.execCommand('mceLink');
        captionEditor.dispatch('change');
        return true;
    }

    captionEditor.execCommand(command);
    captionEditor.dispatch('change');
    return true;
};

/**
 * Dialog pemilih Data & Grafik.
 *
 * Dipisahkan dari penyetel editor supaya editor artikel — yang punya
 * konfigurasi TinyMCE sendiri dengan LFM dan tombol slider — bisa memakai
 * dialog yang sama tanpa menyalin ulang isinya. Cara menyisipkan hasilnya
 * diserahkan ke pemanggil: form Deforestory membungkusnya sebagai blok atomik
 * yang tak bisa disunting, editor artikel cukup menyisipkannya apa adanya.
 */
const bukaPemilihGrafik = async (editor, optionsUrl, sisipkan) => {
    if (!optionsUrl) return;

    let visualizations = [];
    try {
        const response = await fetch(optionsUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) throw new Error('Gagal memuat Data & Grafik.');
        const payload = await response.json();
        visualizations = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        editor.notificationManager.open({
            text: error.message || 'Gagal memuat Data & Grafik.',
            type: 'error',
        });
        return;
    }

    if (!visualizations.length) {
        editor.notificationManager.open({
            text: 'Belum ada Data & Grafik aktif. Publikasikan grafik dari menu Data & Grafik terlebih dahulu.',
            type: 'info',
        });
        return;
    }

    let selected = visualizations[0];
    const previousBodyOverflow = document.body.style.overflow;
    const modal = document.createElement('div');
    modal.dataset.visualizationCardPicker = '';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', 'Pilih Data dan Grafik');
    Object.assign(modal.style, {
        position: 'fixed',
        inset: '0',
        zIndex: '100000',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '16px',
        background: 'rgba(15, 23, 42, .55)',
    });

    const panel = document.createElement('div');
    Object.assign(panel.style, {
        display: 'flex',
        width: 'min(1400px, 100%)',
        maxHeight: 'calc(100vh - 32px)',
        flexDirection: 'column',
        overflow: 'hidden',
        border: '1px solid #dbe2e8',
        background: '#fff',
        boxShadow: '0 24px 70px rgba(15, 23, 42, .24)',
    });

    const header = document.createElement('div');
    Object.assign(header.style, {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: '16px',
        padding: '16px 18px',
        borderBottom: '1px solid #e5e7eb',
    });
    const heading = document.createElement('div');
    heading.innerHTML = '<h2 style="margin:0;color:#111827;font-size:18px;font-weight:700;">Pilih Data &amp; Grafik</h2><p style="margin:4px 0 0;color:#6b7280;font-size:12px;">Klik salah satu card grafik aktif yang ingin dimasukkan.</p>';
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.setAttribute('aria-label', 'Tutup');
    closeButton.textContent = '×';
    Object.assign(closeButton.style, {
        border: '0',
        background: 'transparent',
        color: '#374151',
        fontSize: '26px',
        lineHeight: '1',
        cursor: 'pointer',
    });
    header.append(heading, closeButton);

    const cardGrid = document.createElement('div');
    Object.assign(cardGrid.style, {
        display: 'grid',
        gridTemplateColumns: 'repeat(4, minmax(0, 1fr))',
        alignItems: 'start',
        gap: '12px',
        height: 'min(540px, calc(100vh - 250px))',
        overflowY: 'auto',
        padding: '16px 18px',
        background: '#f8fafc',
    });

    const cards = [];
    const pickerIframeWindows = [];
    const previewObservers = [];
    const visualizationsPerPage = 8;
    let currentVisualizationPage = 1;
    const updateVisualizationGridColumns = () => {
        cardGrid.style.gridTemplateColumns = window.innerWidth >= 1100
            ? 'repeat(4, minmax(0, 1fr))'
            : window.innerWidth >= 650
                ? 'repeat(2, minmax(0, 1fr))'
                : 'minmax(0, 1fr)';
    };
    const updateSelectedCard = () => {
        cards.forEach(({ card, badge, item }) => {
            const isSelected = String(item.id) === String(selected.id);
            card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            card.style.borderColor = isSelected ? '#376a64' : '#dbe2e8';
            card.style.boxShadow = isSelected ? '0 0 0 2px rgba(55, 106, 100, .18)' : 'none';
            badge.style.visibility = isSelected ? 'visible' : 'hidden';
        });
    };

    visualizations.forEach((item) => {
        const card = document.createElement('button');
        card.type = 'button';
        card.dataset.visualizationCard = String(item.id);
        Object.assign(card.style, {
            display: 'block',
            minWidth: '0',
            overflow: 'hidden',
            padding: '0',
            border: '1px solid #dbe2e8',
            background: '#fff',
            textAlign: 'left',
            cursor: 'pointer',
            outline: 'none',
        });

        const preview = document.createElement('div');
        Object.assign(preview.style, {
            position: 'relative',
            width: '100%',
            aspectRatio: '16 / 9',
            overflow: 'hidden',
            borderBottom: '1px solid #e5e7eb',
            background: '#fff',
        });
        const iframe = document.createElement('iframe');
        iframe.src = item.embed_url;
        iframe.title = item.title || 'Preview Data dan Grafik';
        iframe.loading = 'lazy';
        iframe.scrolling = 'no';
        iframe.tabIndex = -1;
        Object.assign(iframe.style, {
            position: 'absolute',
            top: '0',
            left: '0',
            width: '960px',
            height: '540px',
            border: '0',
            pointerEvents: 'none',
            transformOrigin: 'top left',
        });
        const fitPreview = () => {
            const scale = preview.clientWidth / 960;
            iframe.style.transform = `scale(${scale})`;
        };
        if (typeof ResizeObserver !== 'undefined') {
            const previewObserver = new ResizeObserver(fitPreview);
            previewObserver.observe(preview);
            previewObservers.push(previewObserver);
        } else {
            window.requestAnimationFrame(fitPreview);
        }
        iframe.addEventListener('load', () => {
            const iframeWindow = iframe.contentWindow;
            if (!iframeWindow) return;
            pickerIframeWindows.push(iframeWindow);
            iframeWindow.addEventListener('keydown', onPickerKeydown, true);
        });
        preview.append(iframe);

        const details = document.createElement('div');
        Object.assign(details.style, { padding: '10px 12px 12px' });
        const meta = document.createElement('div');
        Object.assign(meta.style, {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: '10px',
            marginBottom: '5px',
        });
        const type = document.createElement('span');
        type.textContent = String(item.chart_type || 'Grafik').replaceAll('-', ' ');
        Object.assign(type.style, {
            color: '#376a64',
            fontSize: '11px',
            fontWeight: '700',
            textTransform: 'uppercase',
            letterSpacing: '.06em',
        });
        const badge = document.createElement('span');
        badge.textContent = 'Dipilih';
        Object.assign(badge.style, {
            color: '#376a64',
            fontSize: '11px',
            fontWeight: '700',
        });
        meta.append(type, badge);
        const title = document.createElement('strong');
        title.textContent = item.title || 'Data & Grafik';
        Object.assign(title.style, {
            display: 'block',
            overflow: 'hidden',
            color: '#111827',
            fontSize: '13px',
            lineHeight: '1.45',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
        });
        details.append(meta, title);
        card.append(preview, details);
        card.addEventListener('click', () => {
            selected = item;
            updateSelectedCard();
        });
        cards.push({ card, badge, item });
        cardGrid.append(card);
    });

    const pagination = document.createElement('div');
    Object.assign(pagination.style, {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: '16px',
        padding: '10px 18px',
        borderTop: '1px solid #e5e7eb',
        background: '#f8fafc',
    });
    const paginationSummary = document.createElement('span');
    Object.assign(paginationSummary.style, {
        color: '#6b7280',
        fontSize: '12px',
    });
    const paginationButtons = document.createElement('div');
    Object.assign(paginationButtons.style, {
        display: 'flex',
        alignItems: 'center',
        gap: '6px',
    });
    pagination.append(paginationSummary, paginationButtons);

    const renderVisualizationPage = () => {
        const totalPages = Math.max(1, Math.ceil(cards.length / visualizationsPerPage));
        currentVisualizationPage = Math.min(totalPages, Math.max(1, currentVisualizationPage));
        const firstIndex = (currentVisualizationPage - 1) * visualizationsPerPage;
        const lastIndex = Math.min(firstIndex + visualizationsPerPage, cards.length);

        cards.forEach(({ card }, index) => {
            card.style.display = index >= firstIndex && index < lastIndex ? 'block' : 'none';
        });

        paginationSummary.textContent = `Menampilkan ${firstIndex + 1}–${lastIndex} dari ${cards.length} data`;
        paginationButtons.replaceChildren();

        const createPageButton = (label, page, disabled = false, active = false) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;
            button.setAttribute('aria-label', label === '‹' ? 'Halaman sebelumnya' : label === '›' ? 'Halaman berikutnya' : `Halaman ${label}`);
            if (active) button.setAttribute('aria-current', 'page');
            Object.assign(button.style, {
                display: 'inline-flex',
                width: '32px',
                height: '32px',
                alignItems: 'center',
                justifyContent: 'center',
                border: `1px solid ${active ? '#376a64' : '#d1d5db'}`,
                background: active ? '#376a64' : '#fff',
                color: active ? '#fff' : disabled ? '#9ca3af' : '#374151',
                fontSize: '12px',
                fontWeight: '700',
                cursor: disabled ? 'not-allowed' : 'pointer',
            });
            if (!disabled) {
                button.addEventListener('click', () => {
                    currentVisualizationPage = page;
                    renderVisualizationPage();
                    cardGrid.scrollTop = 0;
                });
            }
            return button;
        };

        paginationButtons.append(createPageButton('‹', currentVisualizationPage - 1, currentVisualizationPage === 1));
        for (let page = 1; page <= totalPages; page += 1) {
            paginationButtons.append(createPageButton(String(page), page, false, page === currentVisualizationPage));
        }
        paginationButtons.append(createPageButton('›', currentVisualizationPage + 1, currentVisualizationPage === totalPages));
    };

    const footer = document.createElement('div');
    Object.assign(footer.style, {
        display: 'flex',
        justifyContent: 'flex-end',
        gap: '10px',
        padding: '12px 18px',
        borderTop: '1px solid #e5e7eb',
        background: '#fff',
    });
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Batal';
    const insertButton = document.createElement('button');
    insertButton.type = 'button';
    insertButton.textContent = 'Masukkan Grafik';
    [cancelButton, insertButton].forEach((button) => {
        Object.assign(button.style, {
            minHeight: '38px',
            padding: '0 15px',
            border: '1px solid #d1d5db',
            background: '#fff',
            color: '#1f2937',
            fontSize: '13px',
            fontWeight: '700',
            cursor: 'pointer',
        });
    });
    Object.assign(insertButton.style, {
        borderColor: '#376a64',
        background: '#376a64',
        color: '#fff',
    });
    footer.append(cancelButton, insertButton);
    panel.append(header, cardGrid, pagination, footer);
    modal.append(panel);

    const closePicker = () => {
        if (!modal.isConnected) return;
        document.removeEventListener('keydown', onPickerKeydown, true);
        window.removeEventListener('keydown', onPickerKeydown, true);
        window.removeEventListener('resize', updateVisualizationGridColumns);
        pickerIframeWindows.forEach((iframeWindow) => {
            iframeWindow.removeEventListener('keydown', onPickerKeydown, true);
        });
        previewObservers.forEach((observer) => observer.disconnect());
        document.body.style.overflow = previousBodyOverflow;
        modal.remove();
        editor.focus();
    };
    const onPickerKeydown = (event) => {
        if (event.key !== 'Escape') return;
        event.preventDefault();
        event.stopPropagation();
        closePicker();
    };
    closeButton.addEventListener('click', closePicker);
    cancelButton.addEventListener('click', closePicker);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closePicker();
    });
    insertButton.addEventListener('click', () => {
        const embedUrl = editor.dom.encode(selected.embed_url);
        const title = editor.dom.encode(selected.title || 'Data & Grafik');
        sisipkan(`<figure class="story-data-visualization" data-story-data-visualization data-visualization-id="${selected.id}" contenteditable="false" style="display:block;width:100%;margin:24px 0;padding:0;"><div style="position:relative;width:100%;aspect-ratio:16 / 9;overflow:hidden;background:#fff;"><iframe src="${embedUrl}" title="${title}" width="100%" height="100%" frameborder="0" scrolling="no" loading="lazy" style="position:absolute;inset:0;display:block;width:100%;height:100%;border:0;overflow:hidden;" referrerpolicy="strict-origin-when-cross-origin"></iframe></div></figure>`);
        closePicker();
    });

    updateSelectedCard();
    updateVisualizationGridColumns();
    renderVisualizationPage();
    document.addEventListener('keydown', onPickerKeydown, true);
    window.addEventListener('keydown', onPickerKeydown, true);
    window.addEventListener('resize', updateVisualizationGridColumns, { passive: true });
    document.body.style.overflow = 'hidden';
    document.body.append(modal);
    cards[0]?.card.focus();
};

// Dipakai partial TinyMCE artikel yang dirakit di Blade, bukan di bundel ini.
window.bukaPemilihGrafik = bukaPemilihGrafik;

const initializeTinyMceEditors = () => {
    const tinymce = window.tinymce;
    if (!tinymce) return;

    document.querySelectorAll('[data-tinymce-wrapper]').forEach((wrapper) => {
        if (wrapper.dataset.tinymceInitialized === 'true') return;

        const editorElement = wrapper.querySelector('[data-tinymce-editor]');
        const input = wrapper.querySelector('[data-tinymce-input]');
        if (!editorElement || !input) return;

        const isCaptionEditor = wrapper.dataset.tinymcePreset === 'caption';
        const referenceStorageKey = `pasopati-reference-selection:${wrapper.dataset.tinymcePickerId}`;
        let lastReferenceSelection = 0;
        let referenceInsertionMode = 'image';
        let lightboxImageToReplace = null;
        let lightboxGalleryToAppend = null;
        let lightboxManagerDialogApi = null;
        let beforeAfterImageToReplace = null;
        let beforeAfterManagerDialogApi = null;
        let beforeAfterPointerGesture = null;
        let suppressedBeforeAfterClick = null;
        let preferredAtomicInsertionBlock = null;
        let referencePickerModal = null;

        wrapper.dataset.tinymceInitialized = 'true';

        tinymce.init({
            target: editorElement,
            license_key: 'gpl',
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            // Simontini memakai `skin: false` karena mereka mengimpor CSS oxide
            // lewat npm ke dalam bundelnya. Di sini TinyMCE dimuat sebagai
            // skrip global dari public/js/tinymce, jadi tidak ada yang
            // menyuplai CSS itu — dan tanpa skin, toolbar-nya dirender tanpa
            // gaya sama sekali. Menyebut skin-nya membuat TinyMCE memuatnya
            // sendiri dari public/js/tinymce/skins/ui/oxide.
            skin: 'oxide',
            content_css: false,
            height: isCaptionEditor ? 220 : 560,
            min_height: isCaptionEditor ? 180 : 360,
            resize: true,
            menubar: isCaptionEditor ? false : 'file edit view insert format tools table help',
            plugins: 'advlist anchor autolink charmap code codesample fullscreen image link lists media preview searchreplace table visualblocks wordcount',
            toolbar: isCaptionEditor
                ? 'undo redo | fontsize | bold italic underline | alignleft aligncenter alignright | link removeformat | code'
                : 'undo redo | blocks fontsize | addImage addLightbox addBeforeAfter addDataVisualization addVideo addBorderMerah addStopper | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code codesample removeformat | fullscreen preview',
            toolbar_sticky: true,
            promotion: false,
            branding: false,
            statusbar: true,
            elementpath: false,
            valid_elements: '*[*]',
            extended_valid_elements: 'iframe[src|width|height|frameborder|allowfullscreen|style|class|loading|referrerpolicy]',
            verify_html: false,
            entity_encoding: 'raw',
            sandbox_iframes: false,
            font_size_formats: isCaptionEditor
                ? '10px 11px 12px 14px 16px 18px 20px 24px'
                : '8pt 10pt 12pt 14pt 16pt 18pt 24pt 30pt 36pt 48pt',
            content_style: `body { font-family: Arial, sans-serif; font-size: ${isCaptionEditor ? '12px' : '16px'}; line-height: 1.7; padding: 16px; } img, video, iframe { max-width: 100%; } .story-inline-stopper { position: relative; top: 1px; display: inline-block; flex: 0 0 8px; width: 8px; height: 8px; margin-left: 1px; border-radius: 0; background: #d71920; vertical-align: middle; font-size: 0; line-height: 0; } .story-content-gallery { display: flex; width: 100%; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; } .story-content-gallery > .story-content-figure { flex: 0 0 100%; width: 100%; margin: 0; scroll-snap-align: start; } .story-before-after-figure, .story-before-after, .story-before-after img { -webkit-user-drag: none; user-select: none; } .story-before-after { position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; background: #e5e7eb; user-select: none; } .story-before-after-after { position: absolute; inset: 0; width: 100%; height: 100%; clip-path: inset(0 0 0 var(--before-after-position)); } .story-before-after-divider { position: absolute; top: 0; bottom: 0; left: var(--before-after-position); width: 3px; background: #fff; transform: translateX(-50%); pointer-events: none; } .story-before-after-handle { position: absolute; top: 50%; left: var(--before-after-position); z-index: 2; display: flex; width: 52px; height: 52px; align-items: center; justify-content: center; border: 3px solid #fff; border-radius: 9999px; background: rgba(0,0,0,.55); color: #fff; font-size: 22px; transform: translate(-50%, -50%); pointer-events: none; } .story-before-after-label { display: none !important; } .story-before-after-caption { display: flex; flex-wrap: wrap; gap: 4px 12px; margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5; } .story-before-after-caption strong { font-weight: 700; } .story-before-after-range { position: absolute; inset: 0; z-index: 3; width: 100%; height: 100%; margin: 0; cursor: ew-resize; opacity: 0; } .story-data-visualization { width: 100%; margin: 24px 0; } .story-data-visualization iframe { display: block; width: 100%; height: 100%; pointer-events: none; }`,
            setup(editor) {
                const atomicBlockSelector = '.story-lightbox-gallery, .story-before-after-figure, .story-data-visualization';
                const normalizeInlineStoppers = () => {
                    editor.getBody()?.querySelectorAll('.story-inline-stopper').forEach((stopper) => {
                        const previousNode = stopper.previousSibling;
                        if (previousNode?.nodeType === 3) {
                            previousNode.textContent = previousNode.textContent.replace(/[\u00a0 ]+$/, '');
                        }

                        stopper.style.position = 'relative';
                        stopper.style.top = '1px';
                        stopper.style.display = 'inline-block';
                        stopper.style.flex = '0 0 8px';
                        stopper.style.width = '8px';
                        stopper.style.height = '8px';
                        stopper.style.marginLeft = '1px';
                        stopper.style.borderRadius = '0';
                        stopper.style.background = '#d71920';
                        stopper.style.verticalAlign = 'middle';
                        stopper.style.fontSize = '0';
                        stopper.style.lineHeight = '0';
                    });
                };

                const closeReferencePickerModal = (resetMode = true) => {
                    if (!referencePickerModal) return;

                    document.removeEventListener('keydown', referencePickerModal.onKeydown, true);
                    referencePickerModal.iframeWindow?.removeEventListener('keydown', referencePickerModal.onKeydown, true);
                    document.body.style.overflow = referencePickerModal.previousBodyOverflow;
                    referencePickerModal.element.remove();
                    referencePickerModal = null;

                    if (resetMode) {
                        referenceInsertionMode = 'image';
                        lightboxImageToReplace = null;
                        lightboxGalleryToAppend = null;
                        beforeAfterImageToReplace = null;
                    }
                };

                const placeCaretAfterBlock = (atomicBlock, silent = false) => {
                    if (!atomicBlock?.isConnected) return false;

                    let paragraph = atomicBlock.nextElementSibling;
                    if (!paragraph || paragraph.tagName !== 'P') {
                        paragraph = editor.getDoc().createElement('p');
                        paragraph.innerHTML = '<br>';
                        editor.dom.insertAfter(paragraph, atomicBlock);
                    }

                    editor.getBody().querySelectorAll('[data-mce-selected], .mceSelected').forEach((selectedElement) => {
                        selectedElement.removeAttribute('data-mce-selected');
                        selectedElement.classList.remove('mceSelected');
                    });
                    if (silent) {
                        const range = editor.getDoc().createRange();
                        range.setStart(paragraph, 0);
                        range.collapse(true);
                        const nativeSelection = editor.getWin().getSelection();
                        nativeSelection.removeAllRanges();
                        nativeSelection.addRange(range);
                    } else {
                        editor.selection.setCursorLocation(paragraph, 0);
                    }
                    if (!silent) editor.nodeChanged();
                    return true;
                };

                const repaintEditorAfterAtomicDialog = (atomicBlock, scrollPosition) => {
                    if (!atomicBlock?.isConnected) return;

                    normalizeAtomicStoryBlocks();
                    placeCaretAfterBlock(atomicBlock);
                    editor.nodeChanged();
                    editor.dispatch('SelectionChange');

                    const editorBody = editor.getBody();
                    editorBody.style.transform = 'translateZ(0)';
                    void editorBody.offsetHeight;
                    editor.getWin().requestAnimationFrame(() => {
                        editorBody.style.removeProperty('transform');
                        editor.getWin().scrollTo(scrollPosition.x, scrollPosition.y);
                    });
                };

                const installBeforeAfterPointerControls = () => {
                    const editorDocument = editor.getDoc();
                    if (!editorDocument || editorDocument.documentElement.dataset.beforeAfterPointerControls === 'true') return;

                    editorDocument.documentElement.dataset.beforeAfterPointerControls = 'true';
                    const resolveComparison = (event) => {
                        const directComparison = event.target?.closest?.('[data-story-before-after]')
                            || event.target?.closest?.('.story-before-after-figure')?.querySelector('[data-story-before-after]');
                        if (directComparison) return directComparison;

                        return (editorDocument.elementsFromPoint?.(event.clientX, event.clientY) || [])
                            .map((element) => element.closest?.('[data-story-before-after]')
                                || element.closest?.('.story-before-after-figure')?.querySelector('[data-story-before-after]'))
                            .find(Boolean) || null;
                    };
                    const isHandlePoint = (comparison, event, padding = 14) => {
                        const bounds = comparison?.querySelector('.story-before-after-handle')?.getBoundingClientRect();
                        return Boolean(bounds)
                            && event.clientX >= bounds.left - padding
                            && event.clientX <= bounds.right + padding
                            && event.clientY >= bounds.top - padding
                            && event.clientY <= bounds.bottom + padding;
                    };
                    const stopPointerEvent = (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                    };

                    editorDocument.addEventListener('pointerdown', (event) => {
                        const comparison = resolveComparison(event);
                        if (!comparison || !isHandlePoint(comparison, event)) return;

                        const range = comparison.querySelector('[data-before-after-range]');
                        if (!range) return;

                        stopPointerEvent(event);
                        beforeAfterPointerGesture = {
                            range,
                            comparison,
                            figure: comparison.closest('.story-before-after-figure'),
                            pointerId: event.pointerId,
                            startX: event.clientX,
                            dragged: false,
                        };

                        try {
                            range.setPointerCapture?.(event.pointerId);
                        } catch (error) {
                            // The document-level listeners below remain the fallback.
                        }

                        const moveSlider = (moveEvent) => {
                            const gesture = beforeAfterPointerGesture;
                            if (!gesture || gesture.pointerId !== moveEvent.pointerId) return;

                            stopPointerEvent(moveEvent);
                            const bounds = gesture.comparison.getBoundingClientRect();
                            if (!bounds.width) return;

                            const position = Math.min(100, Math.max(0, ((moveEvent.clientX - bounds.left) / bounds.width) * 100));
                            gesture.dragged = gesture.dragged || Math.abs(moveEvent.clientX - gesture.startX) >= 1;
                            gesture.range.value = String(Math.round(position));
                            gesture.comparison.style.setProperty('--before-after-position', `${position}%`);
                        };
                        const finishSlider = (finishEvent) => {
                            const gesture = beforeAfterPointerGesture;
                            if (!gesture || gesture.pointerId !== finishEvent.pointerId) return;

                            stopPointerEvent(finishEvent);
                            gesture.range.setAttribute('value', gesture.range.value);
                            suppressedBeforeAfterClick = {
                                figure: gesture.figure,
                                until: Date.now() + 1000,
                            };

                            if (gesture.range.hasPointerCapture?.(finishEvent.pointerId)) {
                                gesture.range.releasePointerCapture(finishEvent.pointerId);
                            }
                            editorDocument.removeEventListener('pointermove', moveSlider, true);
                            editorDocument.removeEventListener('pointerup', finishSlider, true);
                            editorDocument.removeEventListener('pointercancel', finishSlider, true);
                            beforeAfterPointerGesture = null;
                            editor.nodeChanged();
                            editor.dispatch('change');
                        };

                        editorDocument.addEventListener('pointermove', moveSlider, true);
                        editorDocument.addEventListener('pointerup', finishSlider, true);
                        editorDocument.addEventListener('pointercancel', finishSlider, true);
                    }, true);

                    editorDocument.addEventListener('click', (event) => {
                        const comparison = resolveComparison(event);
                        const figure = comparison?.closest('.story-before-after-figure');
                        const recentlyDragged = figure
                            && suppressedBeforeAfterClick?.figure === figure
                            && Date.now() <= suppressedBeforeAfterClick.until;
                        if (!comparison || (!isHandlePoint(comparison, event) && !recentlyDragged)) return;

                        suppressedBeforeAfterClick = null;
                        stopPointerEvent(event);
                    }, true);

                    editorDocument.addEventListener('dragstart', (event) => {
                        if (!resolveComparison(event)) return;
                        stopPointerEvent(event);
                    }, true);
                };

                const placeCaretAfterAtomicBlock = () => {
                    const selectionNode = editor.selection.getNode();
                    const element = selectionNode?.nodeType === 1
                        ? selectionNode
                        : selectionNode?.parentElement;
                    const atomicBlock = element?.closest?.(atomicBlockSelector);
                    if (!atomicBlock) return false;
                    return placeCaretAfterBlock(atomicBlock);
                };

                const insertAtomicContent = (html) => {
                    if (preferredAtomicInsertionBlock?.isConnected) {
                        placeCaretAfterBlock(preferredAtomicInsertionBlock);
                    } else {
                        placeCaretAfterAtomicBlock();
                    }
                    preferredAtomicInsertionBlock = null;
                    const markerId = `story-caret-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
                    editor.insertContent(`${html}<p id="${markerId}"><br></p>`);
                    const marker = editor.getDoc().getElementById(markerId);
                    if (marker) {
                        marker.removeAttribute('id');
                        editor.selection.setCursorLocation(marker, 0);
                    }
                };

                const normalizeAtomicStoryBlocks = () => {
                    editor.getBody().querySelectorAll('.story-lightbox-gallery .story-before-after-figure, .story-lightbox-gallery .story-lightbox-gallery').forEach((nestedBlock) => {
                        const parentGallery = nestedBlock.parentElement?.closest('.story-lightbox-gallery');
                        const oldWrapper = nestedBlock.parentElement;
                        if (!parentGallery) return;

                        editor.dom.insertAfter(nestedBlock, parentGallery);
                        if (oldWrapper !== parentGallery && oldWrapper?.matches('figure') && !oldWrapper.textContent.trim() && !oldWrapper.querySelector('img, video, iframe')) {
                            oldWrapper.remove();
                        }
                    });

                    editor.getBody().querySelectorAll(atomicBlockSelector).forEach((block) => {
                        block.setAttribute('contenteditable', 'false');
                        block.setAttribute('data-mce-contenteditable', 'false');

                        if (block.matches('.story-before-after-figure')) {
                            block.setAttribute('draggable', 'false');
                            block.querySelectorAll('img, [data-before-after-range]').forEach((element) => {
                                element.setAttribute('draggable', 'false');
                            });
                            block.querySelectorAll('.story-before-after-label').forEach((element) => element.remove());
                            syncBeforeAfterCaption(block);
                        }

                        if (!block.nextElementSibling) {
                            const paragraph = editor.getDoc().createElement('p');
                            paragraph.innerHTML = '<br>';
                            editor.dom.insertAfter(paragraph, block);
                        }
                    });
                };

                const createLightboxGalleryItems = (images, galleryId) => images.map((image) => {
                    const imageUrl = editor.dom.encode(image.url);
                    const imageTitle = editor.dom.encode(image.title || '');
                    const imageDescription = editor.dom.encode(image.caption || image.alt_text || image.title || '');

                    return `<figure class="story-content-figure" style="width: 100%; margin: 0; padding: 0; box-sizing: border-box;"><a class="glightbox2 gbox" href="${imageUrl}" data-gallery="${galleryId}" data-glightbox="description: ${imageDescription}" style="display: block; width: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; overflow: hidden;"><img src="${imageUrl}" alt="${imageDescription}" title="${imageTitle}" style="display: block; width: 100%; height: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; object-fit: cover; object-position: center; cursor: zoom-in;"></a>${imageDescription ? `<figcaption class="story-content-caption" style="margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">${imageDescription}</figcaption>` : ''}</figure>`;
                }).join('');

                const beforeAfterCaptionHtmlToText = (html) => {
                    const template = document.createElement('template');
                    template.innerHTML = html || '';
                    return template.content.textContent?.replace(/\s+/g, ' ').trim() || '';
                };

                const sanitizeBeforeAfterCaptionHtml = (html) => {
                    const template = document.createElement('template');
                    template.innerHTML = html || '';
                    template.content.querySelectorAll('script, style, iframe, object, embed').forEach((element) => element.remove());
                    template.content.querySelectorAll('*').forEach((element) => {
                        Array.from(element.attributes).forEach((attribute) => {
                            if (/^on/i.test(attribute.name)) element.removeAttribute(attribute.name);
                        });
                    });

                    return template.innerHTML.trim();
                };

                const createBeforeAfterCaptionHtml = (description) => {
                    const captionHtml = sanitizeBeforeAfterCaptionHtml(description || '');

                    return captionHtml ? captionHtml : '';
                };

                const syncBeforeAfterCaption = (figure, nextCaption = null) => {
                    const currentCaption = figure.querySelector('.story-before-after-caption');
                    const description = nextCaption !== null
                        ? nextCaption
                        : currentCaption?.innerHTML?.trim()
                        || figure.getAttribute('data-before-after-caption')
                        || figure.querySelector('.story-before-after-image--before')?.getAttribute('alt')
                        || figure.querySelector('.story-before-after-image--after')?.getAttribute('alt')
                        || '';
                    const captionHtml = createBeforeAfterCaptionHtml(description);
                    const captionText = beforeAfterCaptionHtmlToText(captionHtml);

                    if (!captionHtml) {
                        figure.removeAttribute('data-before-after-caption');
                        currentCaption?.remove();
                        return;
                    }

                    figure.setAttribute('data-before-after-caption', captionText);
                    const caption = currentCaption || editor.getDoc().createElement('figcaption');
                    caption.className = 'story-before-after-caption';
                    caption.style.cssText = 'display: flex; flex-wrap: wrap; gap: 4px 12px; margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;';
                    caption.innerHTML = captionHtml;
                    if (!currentCaption) figure.append(caption);
                };

                const insertReferenceImage = (payload) => {
                    if (
                        !['pasopati-reference-selected', 'pasopati-reference-gallery-selected'].includes(payload?.type)
                        || payload.editor !== wrapper.dataset.tinymcePickerId
                        || (!payload.image?.url && !payload.images?.length)
                        || (payload.selectedAt && payload.selectedAt <= lastReferenceSelection)
                    ) return false;

                    lastReferenceSelection = payload.selectedAt || Date.now();
                    const selectedImages = (payload.images?.length ? payload.images : [payload.image])
                        .filter((image) => image?.url);

                    if (!selectedImages.length) return false;

                    if (referenceInsertionMode === 'replace-before-after' && beforeAfterImageToReplace?.figure?.isConnected) {
                        const replacement = selectedImages[0];
                        const targetFigure = beforeAfterImageToReplace.figure;
                        const targetSide = beforeAfterImageToReplace.side;
                        const targetImage = targetFigure.querySelector(`.story-before-after-image--${targetSide}`);
                        const description = replacement.alt_text || replacement.title || '';

                        editor.undoManager.transact(() => {
                            editor.dom.setAttrib(targetImage, 'src', replacement.url);
                            editor.dom.setAttrib(targetImage, 'alt', description);
                            syncBeforeAfterCaption(targetFigure);
                        });
                        editor.nodeChanged();
                        editor.dispatch('change');
                        beforeAfterImageToReplace = null;
                        beforeAfterManagerDialogApi?.close();
                        window.setTimeout(() => openBeforeAfterDialog(targetFigure), 0);
                    } else if (referenceInsertionMode === 'before-after') {
                        if (selectedImages.length !== 2) {
                            window.alert('Before/After membutuhkan tepat 2 gambar.');
                            return false;
                        }

                        const beforeImage = selectedImages[0];
                        const afterImage = selectedImages[1];
                        const beforeUrl = editor.dom.encode(beforeImage.url);
                        const afterUrl = editor.dom.encode(afterImage.url);
                        const beforeDescriptionRaw = beforeImage.caption || beforeImage.alt_text || beforeImage.title || 'Before';
                        const afterDescriptionRaw = afterImage.caption || afterImage.alt_text || afterImage.title || 'After';
                        const beforeDescription = editor.dom.encode(beforeDescriptionRaw);
                        const afterDescription = editor.dom.encode(afterDescriptionRaw);

                        const captionDescription = beforeDescriptionRaw || afterDescriptionRaw;

                        insertAtomicContent(`<figure class="story-before-after-figure" data-before-after-caption="${editor.dom.encode(captionDescription)}" contenteditable="false" data-mce-contenteditable="false" draggable="false" style="width: 100%; margin: 24px 0; padding: 0;"><div class="story-before-after" data-story-before-after style="--before-after-position: 50%; position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden;"><img class="story-before-after-image story-before-after-image--before" src="${beforeUrl}" alt="${beforeDescription}" draggable="false" style="display: block; width: 100%; height: 100%; object-fit: cover;"><div class="story-before-after-after" data-before-after-after style="position: absolute; inset: 0; clip-path: inset(0 0 0 var(--before-after-position));"><img class="story-before-after-image story-before-after-image--after" src="${afterUrl}" alt="${afterDescription}" draggable="false" style="display: block; width: 100%; height: 100%; object-fit: cover;"></div><span class="story-before-after-divider" aria-hidden="true"></span><span class="story-before-after-handle" aria-hidden="true">&#8596;</span><input class="story-before-after-range" data-before-after-range type="range" min="0" max="100" value="50" draggable="false" aria-label="Geser perbandingan gambar Before dan After"></div><figcaption class="story-before-after-caption" style="display: flex; flex-wrap: wrap; gap: 4px 12px; margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">${createBeforeAfterCaptionHtml(captionDescription)}</figcaption></figure>`);
                    } else if (referenceInsertionMode === 'append-lightbox' && lightboxGalleryToAppend?.isConnected) {
                        const galleryToRefresh = lightboxGalleryToAppend;
                        const galleryId = lightboxGalleryToAppend.getAttribute('data-story-lightbox-gallery')
                            || lightboxGalleryToAppend.querySelector('a.glightbox2')?.getAttribute('data-gallery')
                            || `story-gallery-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;

                        editor.undoManager.transact(() => {
                            lightboxGalleryToAppend.insertAdjacentHTML('beforeend', createLightboxGalleryItems(selectedImages, galleryId));
                        });
                        editor.nodeChanged();
                        editor.dispatch('change');
                        lightboxGalleryToAppend = null;
                        const refreshImage = galleryToRefresh.querySelector(':scope > figure:last-child img');
                        lightboxManagerDialogApi?.close();
                        window.setTimeout(() => {
                            if (refreshImage?.isConnected) openLightboxImageDialog(refreshImage);
                        }, 0);
                    } else if (referenceInsertionMode === 'replace-lightbox' && lightboxImageToReplace?.isConnected) {
                        const replacement = selectedImages[0];
                        const replacementAnchor = lightboxImageToReplace.closest('a.glightbox2');
                        const replacementFigure = lightboxImageToReplace.closest('figure');
                        const replacementCaption = replacementFigure?.querySelector('figcaption');
                        const description = replacement.alt_text || replacement.title || '';

                        editor.undoManager.transact(() => {
                            editor.dom.setAttrib(lightboxImageToReplace, 'src', replacement.url);
                            editor.dom.setAttrib(lightboxImageToReplace, 'alt', description);
                            editor.dom.setAttrib(lightboxImageToReplace, 'title', replacement.title || '');
                            editor.dom.setAttrib(replacementAnchor, 'href', replacement.url);
                            editor.dom.setAttrib(replacementAnchor, 'data-glightbox', `description: ${description}`);
                            if (replacementCaption) replacementCaption.textContent = description;
                        });
                        editor.nodeChanged();
                        editor.dispatch('change');
                        const refreshImage = lightboxImageToReplace;
                        lightboxImageToReplace = null;
                        lightboxManagerDialogApi?.close();
                        window.setTimeout(() => {
                            if (refreshImage?.isConnected) openLightboxImageDialog(refreshImage);
                        }, 0);
                    } else if (referenceInsertionMode === 'lightbox') {
                        const galleryId = `story-gallery-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
                        const galleryItems = createLightboxGalleryItems(selectedImages, galleryId);

                        insertAtomicContent(`<div class="story-content-gallery story-lightbox-gallery" data-story-gallery data-story-lightbox-gallery="${galleryId}" contenteditable="false" data-mce-contenteditable="false" style="width: 100%; margin: 24px 0; padding: 0; box-sizing: border-box;">${galleryItems}</div>`);
                    } else {
                        const image = selectedImages[0];
                        const imageUrl = editor.dom.encode(image.url);
                        const imageTitle = editor.dom.encode(image.title || '');
                        const imageDescription = editor.dom.encode(image.alt_text || image.title || '');
                        editor.insertContent(
                            `<figure class="media-caption story-single-image-figure" style="width: 100%; margin: 24px 0; padding: 0; box-sizing: border-box;"><div class="story-single-image" style="display: block; width: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; overflow: hidden; background: #e5e7eb;"><img src="${imageUrl}" alt="${imageDescription}" title="${imageTitle}" style="display: block; width: 100%; height: 100%; aspect-ratio: 16 / 9; margin: 0; padding: 0; object-fit: cover; object-position: center;"></div>${imageDescription ? `<figcaption class="media-caption-text story-content-caption" style="margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;">${imageDescription}</figcaption>` : ''}</figure>`,
                        );
                    }

                    referenceInsertionMode = 'image';
                    closeReferencePickerModal(false);

                    try {
                        window.localStorage.removeItem(referenceStorageKey);
                    } catch (error) {
                        // The image is already inserted.
                    }

                    return true;
                };

                const openReferencePicker = (mode) => {
                    referenceInsertionMode = mode;

                    try {
                        window.localStorage.removeItem(referenceStorageKey);
                    } catch (error) {
                        // Opening the picker does not require browser storage.
                    }

                    const pickerUrl = new URL(wrapper.dataset.tinymceReferencePageUrl, window.location.origin);
                    pickerUrl.searchParams.set('modal', '1');
                    if (['lightbox', 'append-lightbox', 'before-after'].includes(mode)) pickerUrl.searchParams.set('multiple', '1');
                    if (mode === 'before-after') {
                        pickerUrl.searchParams.set('limit', '2');
                        pickerUrl.searchParams.set('purpose', 'before-after');
                    }

                    closeReferencePickerModal(false);

                    const overlay = document.createElement('div');
                    overlay.dataset.tinymceReferenceModal = '';
                    overlay.style.cssText = 'position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.68);';

                    const dialog = document.createElement('div');
                    dialog.setAttribute('role', 'dialog');
                    dialog.setAttribute('aria-modal', 'true');
                    dialog.setAttribute('aria-label', 'Pilih gambar dari Reference');
                    dialog.style.cssText = 'display:flex;width:min(1280px,96vw);height:min(860px,92vh);flex-direction:column;overflow:hidden;border:1px solid #d1d5db;background:#fff;box-shadow:0 24px 60px rgba(15,23,42,.3);';

                    const header = document.createElement('div');
                    header.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid #e5e7eb;padding:14px 18px;';
                    const heading = document.createElement('strong');
                    heading.textContent = mode === 'before-after' ? 'Pilih Gambar Before/After' : 'Pilih Gambar dari Reference';
                    heading.style.cssText = 'color:#111827;font:600 16px/1.4 Arial,sans-serif;';
                    const closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.setAttribute('aria-label', 'Tutup pemilih gambar');
                    closeButton.textContent = '×';
                    closeButton.style.cssText = 'width:36px;height:36px;border:0;background:#f3f4f6;color:#374151;font:400 26px/1 Arial,sans-serif;cursor:pointer;';

                    const iframe = document.createElement('iframe');
                    iframe.src = pickerUrl.toString();
                    iframe.title = 'Reference Pasopati';
                    iframe.style.cssText = 'display:block;width:100%;height:100%;flex:1;border:0;background:#f9fafb;';

                    header.append(heading, closeButton);
                    dialog.append(header, iframe);
                    overlay.append(dialog);
                    document.body.append(overlay);

                    const previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    const onKeydown = (event) => {
                        if (!['Escape', 'Esc'].includes(event.key) && event.keyCode !== 27) return;
                        event.preventDefault();
                        closeReferencePickerModal();
                    };
                    referencePickerModal = { element: overlay, previousBodyOverflow, onKeydown, iframeWindow: null };
                    document.addEventListener('keydown', onKeydown, true);
                    closeButton.addEventListener('click', () => closeReferencePickerModal());
                    overlay.addEventListener('pointerdown', (event) => {
                        if (event.target === overlay) closeReferencePickerModal();
                    });
                    iframe.addEventListener('load', () => {
                        if (!referencePickerModal?.element.isConnected) return;

                        referencePickerModal.iframeWindow = iframe.contentWindow;
                        referencePickerModal.iframeWindow?.addEventListener('keydown', onKeydown, true);
                        iframe.contentWindow?.focus();
                    });
                };

                const consumeStoredReference = () => {
                    try {
                        const stored = window.localStorage.getItem(referenceStorageKey);
                        if (stored) insertReferenceImage(JSON.parse(stored));
                    } catch (error) {
                        // Ignore unavailable storage or invalid data.
                    }
                };

                const openLightboxImageDialog = (imageElement) => {
                    if (lightboxManagerDialogApi) return;

                    const anchor = imageElement.closest('a.glightbox2');
                    const figure = imageElement.closest('figure');
                    const gallery = figure?.closest('.story-lightbox-gallery');
                    if (!anchor || !figure || !gallery) return;

                    const galleryFigures = Array.from(gallery.querySelectorAll(':scope > figure'));
                    const editorWindow = editor.getWin();
                    const editorScrollPosition = { x: editorWindow.scrollX, y: editorWindow.scrollY };
                    const pageScrollPosition = { x: window.scrollX, y: window.scrollY };
                    const dialogId = `lightbox-order-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
                    let pendingFigures = [...galleryFigures];
                    const captionEditorIds = new Set();
                    const captionHtmlToText = (html) => {
                        const template = document.createElement('template');
                        template.innerHTML = html || '';
                        return template.content.textContent?.replace(/\s+/g, ' ').trim() || '';
                    };
                    const sanitizeCaptionHtml = (html) => {
                        const template = document.createElement('template');
                        template.innerHTML = html || '';
                        template.content.querySelectorAll('script, style, iframe, object, embed').forEach((element) => element.remove());
                        template.content.querySelectorAll('*').forEach((element) => {
                            Array.from(element.attributes).forEach((attribute) => {
                                if (/^on/i.test(attribute.name)) element.removeAttribute(attribute.name);
                            });
                        });

                        return template.innerHTML.trim();
                    };
                    const pendingCaptions = new Map(galleryFigures.map((galleryFigure) => [
                        galleryFigure,
                        galleryFigure.querySelector('figcaption')?.innerHTML?.trim() || '',
                    ]));
                    const thumbnailItems = galleryFigures.map((galleryFigure, index) => {
                        const thumbnail = galleryFigure.querySelector('img');
                        const thumbnailUrl = editor.dom.encode(thumbnail?.getAttribute('src') || '');
                        const itemCaption = sanitizeCaptionHtml(pendingCaptions.get(galleryFigure) || '');
                        const captionEditorId = `${dialogId}-caption-${index}`;
                        const captionToolbarId = `${captionEditorId}-toolbar`;
                        captionEditorIds.add(captionEditorId);

                        return `<div data-lightbox-order-item data-figure-index="${index}" style="min-width: 0; overflow: visible; border: 1px solid #d1d5db; border-radius: 7px; background: #fff; padding: 12px;"><div style="display: flex; min-width: 0; align-items: center; gap: 10px;"><button type="button" draggable="true" data-lightbox-drag-handle title="Tarik untuk mengubah urutan" aria-label="Tarik gambar ke-${index + 1}" style="display: flex; width: 34px; height: 52px; flex: 0 0 34px; align-items: center; justify-content: center; border: 0; border-radius: 5px; background: #111827; color: #fff; font-size: 18px; line-height: 1; cursor: grab;">&#8942;&#8942;</button><img src="${thumbnailUrl}" alt="" style="display: block; width: 120px; height: 68px; flex: 0 0 120px; border-radius: 4px; object-fit: cover; pointer-events: none;"><div data-lightbox-order-label style="min-width: 0; flex: 1; color: #1f2937; font-size: 13px; font-weight: 700;">Gambar ke-${index + 1}</div><button type="button" data-lightbox-replace data-figure-index="${index}" style="border: 1px solid #376a64; border-radius: 5px; background: #fff; padding: 7px 11px; color: #376a64; font-size: 12px; font-weight: 700; cursor: pointer;">Ganti</button><button type="button" data-lightbox-remove data-figure-index="${index}" style="border: 1px solid #dc2626; border-radius: 5px; background: #fff; padding: 7px 11px; color: #dc2626; font-size: 12px; font-weight: 700; cursor: pointer;">Hapus</button></div><label for="${captionEditorId}" style="display: block; margin-top: 10px; color: #4b5563; font-size: 12px; font-weight: 600;">Deskripsi gambar</label><div id="${captionToolbarId}" data-lightbox-caption-toolbar data-caption-editor-target="${captionEditorId}" style="display: flex; flex-wrap: wrap; gap: 4px; margin: 5px 0 0; border: 1px solid #cbd5e1; border-bottom: 0; border-radius: 5px 5px 0 0; background: #f8fafc; padding: 6px;"><button type="button" data-lightbox-caption-command="Bold" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 800; cursor: pointer;">B</button><button type="button" data-lightbox-caption-command="Italic" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-style: italic; font-weight: 700; cursor: pointer;">I</button><button type="button" data-lightbox-caption-command="Underline" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; text-decoration: underline; cursor: pointer;">U</button><button type="button" data-lightbox-caption-command="InsertUnorderedList" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">• List</button><button type="button" data-lightbox-caption-command="InsertOrderedList" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">1. List</button><button type="button" data-lightbox-caption-command="mceLink" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Link</button><button type="button" data-lightbox-caption-command="Unlink" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Unlink</button><button type="button" data-lightbox-caption-command="RemoveFormat" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Clear</button></div><div id="${captionEditorId}" contenteditable="true" data-lightbox-caption data-figure-index="${index}" data-placeholder="Tulis deskripsi gambar" style="display: block; width: 100%; min-height: 82px; margin: 0 0 3px; box-sizing: border-box; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 0 0 5px 5px; padding: 9px 10px; color: #1f2937; font-family: Arial, sans-serif; font-size: 13px; line-height: 1.5; outline: none;">${itemCaption}</div></div>`;
                    }).join('');

                    const syncCaptionEditorsToPending = () => {
                        captionEditorIds.forEach((captionEditorId) => {
                            const captionInput = document.getElementById(captionEditorId);
                            const captionFigure = galleryFigures[Number(captionInput?.dataset.figureIndex)];
                            const captionEditor = tinymce.get(captionEditorId);
                            if (captionFigure) pendingCaptions.set(captionFigure, sanitizeCaptionHtml(captionEditor?.getContent() || captionInput?.innerHTML || ''));
                        });
                    };

                    const destroyCaptionEditors = () => {
                        captionEditorIds.forEach((captionEditorId) => tinymce.get(captionEditorId)?.remove());
                    };

                    const initializeCaptionEditors = () => {
                        captionEditorIds.forEach((captionEditorId) => {
                            const captionInput = document.getElementById(captionEditorId);
                            if (!captionInput) return;

                            captionInput.style.minHeight = '160px';
                        });
                    };

                    const applyPendingOrder = () => {
                        galleryFigures
                            .filter((galleryFigure) => !pendingFigures.includes(galleryFigure))
                            .forEach((galleryFigure) => galleryFigure.remove());
                        if (!pendingFigures.length) {
                            gallery.remove();
                            return;
                        }
                        pendingFigures.forEach((galleryFigure) => gallery.append(galleryFigure));
                    };

                    const applyImageDetails = () => {
                        editor.undoManager.transact(() => {
                            syncCaptionEditorsToPending();
                            pendingFigures.forEach((galleryFigure) => {
                                const currentImage = galleryFigure.querySelector('img');
                                const currentAnchor = galleryFigure.querySelector('a.glightbox2');
                                const currentCaption = galleryFigure.querySelector('figcaption');
                                const descriptionHtml = sanitizeCaptionHtml(pendingCaptions.get(galleryFigure) || '');
                                const description = captionHtmlToText(descriptionHtml);

                                editor.dom.setAttrib(currentImage, 'alt', description);
                                editor.dom.setAttrib(currentAnchor, 'data-glightbox', `description: ${description}`);

                                if (descriptionHtml) {
                                    const caption = currentCaption || editor.getDoc().createElement('figcaption');
                                    caption.className = 'story-content-caption';
                                    caption.style.cssText = 'margin: 4px 0 0; padding: 0; color: #000; font-size: 12px; font-weight: 400; line-height: 1.5;';
                                    caption.innerHTML = descriptionHtml;
                                    if (!currentCaption) galleryFigure.append(caption);
                                } else {
                                    currentCaption?.remove();
                                }
                            });
                            applyPendingOrder();
                        });
                        editor.nodeChanged();
                        editor.dispatch('change');
                    };

                    let closeOnEscape = null;
                    let closeOnOutside = null;
                    let dialogElement = null;
                    let dialogClosed = false;
                    const escapeEventTargets = [window, document, editor.getWin(), editor.getDoc()]
                        .filter((target, index, targets) => target && targets.indexOf(target) === index);
                    const dialogApi = editor.windowManager.open({
                        title: 'Kelola Galeri GLightbox',
                        size: 'large',
                        body: {
                            type: 'panel',
                            items: [
                                {
                                    type: 'htmlpanel',
                                    html: `<div style="margin-bottom: 8px; color: #4b5563; font-size: 13px;">Galeri ini berisi <strong data-lightbox-image-count>${galleryFigures.length}</strong> gambar. Tarik pegangan hitam ke atas atau bawah untuk mengubah urutan.</div><div data-lightbox-order="${dialogId}" style="display: flex; max-height: 460px; flex-direction: column; gap: 10px; overflow-y: auto; margin-bottom: 12px; padding: 2px 7px 8px 2px;">${thumbnailItems}</div>`,
                                },
                            ],
                        },
                        buttons: [
                            { type: 'custom', name: 'append', text: 'Tambah Gambar' },
                            { type: 'cancel', text: 'Batal' },
                            { type: 'submit', text: 'Simpan', primary: true },
                        ],
                        onAction(api, details) {
                            if (details.name === 'append') {
                                applyImageDetails();
                                lightboxGalleryToAppend = gallery;
                                openReferencePicker('append-lightbox');
                            }

                        },
                        onSubmit(api) {
                            applyImageDetails();
                            api.close();
                        },
                        onClose() {
                            destroyCaptionEditors();
                            dialogClosed = true;
                            escapeEventTargets.forEach((target) => {
                                if (closeOnEscape) {
                                    target.removeEventListener('keydown', closeOnEscape, true);
                                    target.removeEventListener('keyup', closeOnEscape, true);
                                }
                            });
                            if (closeOnOutside) document.removeEventListener('pointerdown', closeOnOutside, true);
                            if (lightboxManagerDialogApi === dialogApi) lightboxManagerDialogApi = null;
                            preferredAtomicInsertionBlock = gallery;
                            const restoreEditorPosition = () => {
                                editorWindow.scrollTo(editorScrollPosition.x, editorScrollPosition.y);
                                window.scrollTo(pageScrollPosition.x, pageScrollPosition.y);
                            };
                            window.setTimeout(() => {
                                repaintEditorAfterAtomicDialog(gallery, editorScrollPosition);
                                restoreEditorPosition();
                            }, 0);
                        },
                    });
                    lightboxManagerDialogApi = dialogApi;

                    closeOnEscape = (event) => {
                        if (!['Escape', 'Esc'].includes(event.key) && event.keyCode !== 27) return;
                        if (dialogClosed) return;

                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        dialogApi.close();
                    };
                    escapeEventTargets.forEach((target) => {
                        target.addEventListener('keydown', closeOnEscape, true);
                        target.addEventListener('keyup', closeOnEscape, true);
                    });

                    const dialogWraps = document.querySelectorAll('.tox-dialog-wrap');
                    const currentDialogWrap = dialogWraps[dialogWraps.length - 1];
                    dialogElement = currentDialogWrap?.querySelector('.tox-dialog');
                    closeOnOutside = (event) => {
                        if (dialogElement?.contains(event.target)) return;
                        if (event.target?.closest?.('.tox-tinymce-aux, .tox-menu, .tox-pop, .tox-dialog')) return;
                        dialogApi.close();
                    };
                    document.addEventListener('pointerdown', closeOnOutside, true);

                    window.setTimeout(() => {
                        const orderContainer = document.querySelector(`[data-lightbox-order="${dialogId}"]`);
                        if (!orderContainer) return;
                        initializeCaptionEditors();

                        let draggedItem = null;

                        const syncPendingFigures = () => {
                            pendingFigures = Array.from(orderContainer.querySelectorAll('[data-lightbox-order-item]'))
                                .map((item) => galleryFigures[Number(item.dataset.figureIndex)])
                                .filter(Boolean);
                            orderContainer.querySelectorAll('[data-lightbox-order-item]').forEach((item, index) => {
                                const label = item.querySelector('[data-lightbox-order-label]');
                                if (label) label.textContent = `Gambar ke-${index + 1}`;
                            });
                            const count = document.querySelector('[data-lightbox-image-count]');
                            if (count) count.textContent = String(pendingFigures.length);
                        };

                        orderContainer.addEventListener('input', (event) => {
                            const captionInput = event.target.closest('[data-lightbox-caption]');
                            if (!captionInput) return;

                            saveInlineCaptionSelection(captionInput);
                            const captionFigure = galleryFigures[Number(captionInput.dataset.figureIndex)];
                            if (captionFigure) pendingCaptions.set(captionFigure, sanitizeCaptionHtml(captionInput.innerHTML));
                        });

                        ['mouseup', 'keyup'].forEach((eventName) => {
                            orderContainer.addEventListener(eventName, (event) => {
                                const captionInput = event.target.closest('[data-lightbox-caption]');
                                if (captionInput) saveInlineCaptionSelection(captionInput);
                            });
                        });

                        orderContainer.addEventListener('keydown', (event) => {
                            const captionInput = event.target.closest('[data-lightbox-caption]');
                            const shortcutCommand = getInlineCaptionShortcutCommand(event);
                            if (!captionInput || !shortcutCommand) return;

                            event.preventDefault();
                            saveInlineCaptionSelection(captionInput);
                            const captionEditor = tinymce.get(captionInput.id);
                            applyTinyMceCaptionCommand(captionEditor, captionInput, shortcutCommand, inlineCaptionSelectionRanges.get(captionInput));
                            const captionFigure = galleryFigures[Number(captionInput.dataset.figureIndex)];
                            if (captionFigure) pendingCaptions.set(captionFigure, sanitizeCaptionHtml(captionEditor?.getContent() || captionInput.innerHTML));
                        });

                        orderContainer.addEventListener('mousedown', (event) => {
                            const captionCommandButton = event.target.closest('[data-lightbox-caption-command]');
                            if (!captionCommandButton) return;

                            event.preventDefault();
                            event.stopPropagation();

                            const toolbar = captionCommandButton.closest('[data-lightbox-caption-toolbar]');
                            const captionInput = document.getElementById(toolbar?.dataset.captionEditorTarget);
                            if (!captionInput) return;

                            const captionEditor = tinymce.get(captionInput.id);
                            applyTinyMceCaptionCommand(captionEditor, captionInput, captionCommandButton.dataset.lightboxCaptionCommand, inlineCaptionSelectionRanges.get(captionInput));
                            const captionFigure = galleryFigures[Number(captionInput.dataset.figureIndex)];
                            if (captionFigure) pendingCaptions.set(captionFigure, sanitizeCaptionHtml(captionEditor?.getContent() || captionInput.innerHTML));
                        });

                        orderContainer.addEventListener('click', (event) => {
                            const captionCommandButton = event.target.closest('[data-lightbox-caption-command]');
                            if (captionCommandButton) {
                                event.preventDefault();
                                return;
                            }

                            const replaceButton = event.target.closest('[data-lightbox-replace]');
                            if (replaceButton) {
                                const targetFigure = galleryFigures[Number(replaceButton.dataset.figureIndex)];
                                const targetImage = targetFigure?.querySelector('img');
                                if (!targetImage) return;

                                applyImageDetails();
                                lightboxImageToReplace = targetImage;
                                openReferencePicker('replace-lightbox');
                                return;
                            }

                            const removeButton = event.target.closest('[data-lightbox-remove]');
                            if (!removeButton) return;

                            if (pendingFigures.length <= 1) {
                                window.alert('Galeri minimal memiliki satu gambar. Hapus blok GLightbox dari editor jika ingin menghapus seluruh galeri.');
                                return;
                            }

                            const targetFigure = galleryFigures[Number(removeButton.dataset.figureIndex)];
                            const targetItem = removeButton.closest('[data-lightbox-order-item]');
                            const targetCaptionEditorId = targetItem?.querySelector('[data-lightbox-caption]')?.id;
                            if (targetCaptionEditorId) {
                                tinymce.get(targetCaptionEditorId)?.remove();
                                captionEditorIds.delete(targetCaptionEditorId);
                            }
                            pendingFigures = pendingFigures.filter((galleryFigure) => galleryFigure !== targetFigure);
                            pendingCaptions.delete(targetFigure);
                            targetItem?.remove();
                            syncPendingFigures();
                        });

                        orderContainer.addEventListener('dragstart', (event) => {
                            const handle = event.target.closest('[data-lightbox-drag-handle]');
                            if (!handle) {
                                event.preventDefault();
                                return;
                            }

                            draggedItem = handle.closest('[data-lightbox-order-item]');
                            draggedItem.style.opacity = '.55';
                            event.dataTransfer.effectAllowed = 'move';
                        });

                        orderContainer.addEventListener('dragover', (event) => {
                            const targetItem = event.target.closest('[data-lightbox-order-item]');
                            if (!draggedItem || !targetItem || targetItem === draggedItem) return;

                            event.preventDefault();
                            const bounds = targetItem.getBoundingClientRect();
                            const insertBefore = event.clientY < bounds.top + bounds.height / 2;
                            targetItem.parentNode.insertBefore(draggedItem, insertBefore ? targetItem : targetItem.nextSibling);
                            syncPendingFigures();
                        });

                        orderContainer.addEventListener('drop', (event) => {
                            event.preventDefault();
                            syncPendingFigures();
                        });

                        orderContainer.addEventListener('dragend', () => {
                            if (draggedItem) draggedItem.style.opacity = '1';
                            draggedItem = null;
                            syncPendingFigures();
                        });
                    }, 0);
                };

                const openBeforeAfterDialog = (figure) => {
                    if (!figure?.isConnected) return;

                    const beforeImage = figure.querySelector('.story-before-after-image--before');
                    const afterImage = figure.querySelector('.story-before-after-image--after');
                    if (!beforeImage || !afterImage) return;

                    const editorWindow = editor.getWin();
                    const editorScrollPosition = { x: editorWindow.scrollX, y: editorWindow.scrollY };
                    const pageScrollPosition = { x: window.scrollX, y: window.scrollY };
                    const dialogId = `before-after-manager-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
                    const captionEditorId = `${dialogId}-caption`;
                    const captionToolbarId = `${captionEditorId}-toolbar`;
                    let beforeDescription = beforeImage.getAttribute('alt') || '';
                    let afterDescription = afterImage.getAttribute('alt') || '';
                    let captionDescription = figure.querySelector('.story-before-after-caption')?.innerHTML?.trim()
                        || figure.getAttribute('data-before-after-caption')
                        || beforeDescription
                        || afterDescription
                        || '';

                    const syncBeforeAfterCaptionEditor = () => {
                        const captionEditor = tinymce.get(captionEditorId);
                        const captionInput = document.getElementById(captionEditorId);
                        captionDescription = sanitizeBeforeAfterCaptionHtml(captionEditor?.getContent() || captionInput?.innerHTML || captionDescription || '');
                    };

                    const destroyBeforeAfterCaptionEditor = () => {
                        tinymce.get(captionEditorId)?.remove();
                    };

                    const initializeBeforeAfterCaptionEditor = () => {
                        const captionInput = document.getElementById(captionEditorId);
                        if (!captionInput) return;

                        captionInput.style.minHeight = '160px';
                    };

                    const applyDetails = () => {
                        syncBeforeAfterCaptionEditor();
                        editor.undoManager.transact(() => {
                            editor.dom.setAttrib(beforeImage, 'alt', beforeDescription);
                            editor.dom.setAttrib(afterImage, 'alt', afterDescription);
                            const captionText = beforeAfterCaptionHtmlToText(captionDescription);
                            if (captionText) {
                                editor.dom.setAttrib(figure, 'data-before-after-caption', captionText);
                            } else {
                                figure.removeAttribute('data-before-after-caption');
                            }
                            syncBeforeAfterCaption(figure, captionDescription);
                        });
                        editor.nodeChanged();
                        editor.dispatch('change');
                    };

                    const rowHtml = (side, image) => `<div style="border: 1px solid #d1d5db; border-radius: 7px; background: #fff; padding: 12px;"><div style="display: flex; align-items: center; gap: 12px;"><img data-before-after-preview="${side}" src="${editor.dom.encode(image.getAttribute('src') || '')}" alt="" style="display: block; width: 150px; height: 84px; flex: 0 0 150px; border-radius: 5px; object-fit: cover;"><strong style="flex: 1; color: #1f2937; font-size: 14px;">${side === 'before' ? 'Before' : 'After'}</strong><button type="button" data-before-after-replace="${side}" style="border: 1px solid #376a64; border-radius: 5px; background: #fff; padding: 8px 13px; color: #376a64; font-size: 12px; font-weight: 700; cursor: pointer;">Ganti Gambar</button></div></div>`;
                    const captionRowHtml = () => `<div style="border: 1px solid #d1d5db; border-radius: 7px; background: #fff; padding: 12px;"><label for="${captionEditorId}" style="display: block; color: #4b5563; font-size: 12px; font-weight: 600;">Deskripsi gambar</label><div id="${captionToolbarId}" data-before-after-caption-toolbar data-caption-editor-target="${captionEditorId}" style="display: flex; flex-wrap: wrap; gap: 4px; margin: 5px 0 0; border: 1px solid #cbd5e1; border-bottom: 0; border-radius: 5px 5px 0 0; background: #f8fafc; padding: 6px;"><button type="button" data-before-after-caption-command="Bold" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 800; cursor: pointer;">B</button><button type="button" data-before-after-caption-command="Italic" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-style: italic; font-weight: 700; cursor: pointer;">I</button><button type="button" data-before-after-caption-command="Underline" style="min-width: 30px; border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; text-decoration: underline; cursor: pointer;">U</button><button type="button" data-before-after-caption-command="InsertUnorderedList" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">• List</button><button type="button" data-before-after-caption-command="InsertOrderedList" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">1. List</button><button type="button" data-before-after-caption-command="mceLink" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Link</button><button type="button" data-before-after-caption-command="Unlink" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Unlink</button><button type="button" data-before-after-caption-command="RemoveFormat" style="border: 1px solid #cbd5e1; background: #fff; padding: 4px 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Clear</button></div><div id="${captionEditorId}" contenteditable="true" data-before-after-caption-input data-placeholder="Tulis deskripsi gambar" style="display: block; width: 100%; min-height: 82px; margin: 0 0 3px; box-sizing: border-box; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 0 0 5px 5px; padding: 9px 10px; color: #1f2937; font-family: Arial, sans-serif; font-size: 13px; line-height: 1.5; outline: none;">${sanitizeBeforeAfterCaptionHtml(captionDescription)}</div><p style="margin: 6px 0 0; color: #6b7280; font-size: 11px; line-height: 1.4;">Teks ini tampil di bawah gambar Before/After ukuran 12px.</p></div>`;

                    let closeOnEscape = null;
                    let closeOnOutside = null;
                    let dialogElement = null;
                    let dialogClosed = false;
                    const escapeEventTargets = [window, document, editor.getWin(), editor.getDoc()]
                        .filter((target, index, targets) => target && targets.indexOf(target) === index);
                    const dialogApi = editor.windowManager.open({
                        title: 'Kelola Before/After',
                        size: 'large',
                        body: {
                            type: 'panel',
                            items: [{
                                type: 'htmlpanel',
                                html: `<div data-before-after-manager="${dialogId}" style="display: flex; max-height: 510px; flex-direction: column; gap: 10px; overflow-y: auto; padding: 2px 7px 8px 2px;">${rowHtml('before', beforeImage)}${rowHtml('after', afterImage)}${captionRowHtml()}</div>`,
                            }],
                        },
                        buttons: [
                            { type: 'custom', name: 'swap', text: 'Tukar Before/After' },
                            { type: 'cancel', text: 'Batal' },
                            { type: 'submit', text: 'Simpan', primary: true },
                        ],
                        onAction(api, details) {
                            if (details.name !== 'swap') return;

                            applyDetails();
                            editor.undoManager.transact(() => {
                                const beforeSrc = beforeImage.getAttribute('src');
                                const beforeAlt = beforeImage.getAttribute('alt');
                                editor.dom.setAttrib(beforeImage, 'src', afterImage.getAttribute('src'));
                                editor.dom.setAttrib(beforeImage, 'alt', afterImage.getAttribute('alt'));
                                editor.dom.setAttrib(afterImage, 'src', beforeSrc);
                                editor.dom.setAttrib(afterImage, 'alt', beforeAlt);
                                syncBeforeAfterCaption(figure);
                            });
                            const previousBeforeDescription = beforeDescription;
                            beforeDescription = afterDescription;
                            afterDescription = previousBeforeDescription;

                            const manager = document.querySelector(`[data-before-after-manager="${dialogId}"]`);
                            const beforePreview = manager?.querySelector('[data-before-after-preview="before"]');
                            const afterPreview = manager?.querySelector('[data-before-after-preview="after"]');
                            if (beforePreview) beforePreview.src = beforeImage.getAttribute('src') || '';
                            if (afterPreview) afterPreview.src = afterImage.getAttribute('src') || '';

                            editor.dispatch('change');
                        },
                        onSubmit(api) {
                            applyDetails();
                            api.close();
                        },
                        onClose() {
                            destroyBeforeAfterCaptionEditor();
                            dialogClosed = true;
                            escapeEventTargets.forEach((target) => {
                                if (closeOnEscape) {
                                    target.removeEventListener('keydown', closeOnEscape, true);
                                    target.removeEventListener('keyup', closeOnEscape, true);
                                }
                            });
                            if (closeOnOutside) document.removeEventListener('pointerdown', closeOnOutside, true);
                            if (beforeAfterManagerDialogApi === dialogApi) beforeAfterManagerDialogApi = null;
                            preferredAtomicInsertionBlock = figure;
                            window.setTimeout(() => {
                                repaintEditorAfterAtomicDialog(figure, editorScrollPosition);
                                window.scrollTo(pageScrollPosition.x, pageScrollPosition.y);
                            }, 0);
                        },
                    });
                    beforeAfterManagerDialogApi = dialogApi;

                    closeOnEscape = (event) => {
                        if (!['Escape', 'Esc'].includes(event.key) && event.keyCode !== 27) return;
                        if (dialogClosed) return;
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        dialogApi.close();
                    };
                    escapeEventTargets.forEach((target) => {
                        target.addEventListener('keydown', closeOnEscape, true);
                        target.addEventListener('keyup', closeOnEscape, true);
                    });

                    const dialogWraps = document.querySelectorAll('.tox-dialog-wrap');
                    const currentDialogWrap = dialogWraps[dialogWraps.length - 1];
                    dialogElement = currentDialogWrap?.querySelector('.tox-dialog');
                    closeOnOutside = (event) => {
                        if (dialogElement?.contains(event.target)) return;
                        if (event.target?.closest?.('.tox-tinymce-aux, .tox-menu, .tox-pop, .tox-dialog')) return;
                        dialogApi.close();
                    };
                    document.addEventListener('pointerdown', closeOnOutside, true);

                    window.setTimeout(() => {
                        const manager = document.querySelector(`[data-before-after-manager="${dialogId}"]`);
                        if (!manager) return;
                        initializeBeforeAfterCaptionEditor();

                        manager.addEventListener('input', (event) => {
                            const field = event.target.closest('[data-before-after-caption-input]');
                            if (!field) return;
                            saveInlineCaptionSelection(field);
                            captionDescription = sanitizeBeforeAfterCaptionHtml(field.innerHTML);
                        });

                        ['mouseup', 'keyup'].forEach((eventName) => {
                            manager.addEventListener(eventName, (event) => {
                                const captionInput = event.target.closest('[data-before-after-caption-input]');
                                if (captionInput) saveInlineCaptionSelection(captionInput);
                            });
                        });

                        manager.addEventListener('keydown', (event) => {
                            const captionInput = event.target.closest('[data-before-after-caption-input]');
                            const shortcutCommand = getInlineCaptionShortcutCommand(event);
                            if (!captionInput || !shortcutCommand) return;

                            event.preventDefault();
                            saveInlineCaptionSelection(captionInput);
                            const captionEditor = tinymce.get(captionInput.id);
                            applyTinyMceCaptionCommand(captionEditor, captionInput, shortcutCommand, inlineCaptionSelectionRanges.get(captionInput));
                            captionDescription = sanitizeBeforeAfterCaptionHtml(captionEditor?.getContent() || captionInput.innerHTML);
                        });

                        manager.addEventListener('mousedown', (event) => {
                            const captionCommandButton = event.target.closest('[data-before-after-caption-command]');
                            if (!captionCommandButton) return;

                            event.preventDefault();
                            event.stopPropagation();

                            const toolbar = captionCommandButton.closest('[data-before-after-caption-toolbar]');
                            const captionInput = document.getElementById(toolbar?.dataset.captionEditorTarget);
                            if (!captionInput) return;

                            const captionEditor = tinymce.get(captionInput.id);
                            applyTinyMceCaptionCommand(captionEditor, captionInput, captionCommandButton.dataset.beforeAfterCaptionCommand, inlineCaptionSelectionRanges.get(captionInput));
                            captionDescription = sanitizeBeforeAfterCaptionHtml(captionEditor?.getContent() || captionInput.innerHTML);
                        });

                        manager.addEventListener('click', (event) => {
                            const captionCommandButton = event.target.closest('[data-before-after-caption-command]');
                            if (captionCommandButton) {
                                event.preventDefault();
                                return;
                            }

                            const replaceButton = event.target.closest('[data-before-after-replace]');
                            if (!replaceButton) return;

                            applyDetails();
                            beforeAfterImageToReplace = { figure, side: replaceButton.dataset.beforeAfterReplace };
                            openReferencePicker('replace-before-after');
                        });
                    }, 0);
                };

                editor.ui.registry.addButton('addImage', {
                    text: '+ Image',
                    tooltip: 'Pilih gambar dari Reference',
                    onAction: () => openReferencePicker('image'),
                });
                editor.ui.registry.addButton('addLightbox', {
                    text: '+ GLightbox',
                    tooltip: 'Pilih gambar dari Reference dan buka sebagai lightbox',
                    onAction: () => openReferencePicker('lightbox'),
                });
                editor.ui.registry.addButton('addBeforeAfter', {
                    text: '+ Before/After',
                    tooltip: 'Bandingkan dua gambar dengan penggeser Before dan After',
                    onAction: () => openReferencePicker('before-after'),
                });
                editor.ui.registry.addButton('addDataVisualization', {
                    text: '+ Data/Grafik',
                    tooltip: 'Pilih Data & Grafik yang sudah dipublikasikan',
                    onAction: () => bukaPemilihGrafik(
                        editor,
                        wrapper.dataset.tinymceVisualizationOptionsUrl,
                        (html) => {
                            insertAtomicContent(html);
                            normalizeAtomicStoryBlocks();
                        },
                    ),
                });
                editor.ui.registry.addButton('addVideo', {
                    text: '+ Video',
                    onAction: () => editor.insertContent("<figure class='media-caption'><video controls width='100%'><source src='' type='video/mp4'></video><figcaption class='media-caption-text'>Tulis caption video</figcaption></figure>"),
                });
                editor.ui.registry.addButton('addBorderMerah', {
                    text: '+ Border',
                    onAction: () => editor.insertContent("<div style='border:1px solid red;padding:20px;'>Konten</div>"),
                });
                editor.ui.registry.addButton('addStopper', {
                    text: '+ Stopper',
                    tooltip: 'Tambahkan kotak merah di ujung kalimat',
                    onAction: () => {
                        editor.focus();
                        editor.undoManager.transact(() => {
                            editor.insertContent('<span class="story-inline-stopper" data-story-inline-stopper="true" contenteditable="false" aria-hidden="true" style="position:relative;top:1px;display:inline-block;flex:0 0 8px;width:8px;height:8px;margin-left:1px;border-radius:0;background:#d71920;vertical-align:middle;font-size:0;line-height:0;">&nbsp;</span>');
                        });
                        editor.nodeChanged();
                    },
                });
                editor.on('init', () => {
                    editor.setContent(input.value || editorElement.value || '');
                    normalizeInlineStoppers();
                    normalizeAtomicStoryBlocks();
                    installBeforeAfterPointerControls();
                    wrapper.tinyMceEditor = editor;
                });
                editor.on('change input undo redo blur', () => {
                    input.value = editor.getContent();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                editor.on('click', (event) => {
                    const selectionNode = editor.selection.getNode();
                    const clickedBeforeAfter = event.target?.closest?.('.story-before-after-figure')
                        || selectionNode?.closest?.('.story-before-after-figure')
                        || (selectionNode?.matches?.('.story-before-after-figure') ? selectionNode : null);
                    if (clickedBeforeAfter) {
                        const clickedHandleBounds = clickedBeforeAfter
                            .querySelector('.story-before-after-handle')
                            ?.getBoundingClientRect();
                        const clickedOnHandle = Boolean(clickedHandleBounds)
                            && event.clientX >= clickedHandleBounds.left - 12
                            && event.clientX <= clickedHandleBounds.right + 12
                            && event.clientY >= clickedHandleBounds.top - 12
                            && event.clientY <= clickedHandleBounds.bottom + 12;
                        if (
                            clickedOnHandle
                            || (
                                suppressedBeforeAfterClick?.figure === clickedBeforeAfter
                                && Date.now() <= suppressedBeforeAfterClick.until
                            )
                        ) {
                            suppressedBeforeAfterClick = null;
                            event.preventDefault();
                            event.stopPropagation();
                            return;
                        }
                        event.preventDefault();
                        openBeforeAfterDialog(clickedBeforeAfter);
                        return;
                    }

                    const clickedGallery = event.target?.closest?.('.story-lightbox-gallery')
                        || selectionNode?.closest?.('.story-lightbox-gallery')
                        || (selectionNode?.matches?.('.story-lightbox-gallery') ? selectionNode : null);
                    const image = clickedGallery?.querySelector('a.glightbox2 img');
                    if (!image) {
                        preferredAtomicInsertionBlock = null;
                        return;
                    }

                    event.preventDefault();
                    openLightboxImageDialog(image);
                });
                editor.on('input change', (event) => {
                    const range = event.target?.closest?.('[data-before-after-range]');
                    const comparison = range?.closest?.('[data-story-before-after]');
                    if (range && comparison) comparison.style.setProperty('--before-after-position', `${range.value}%`);
                });
                editor.on('keydown', (event) => {
                    if (event.key !== 'Enter') return;
                    if (!placeCaretAfterAtomicBlock()) return;

                    event.preventDefault();
                });

                window.addEventListener('message', (event) => {
                    if (event.origin === window.location.origin) insertReferenceImage(event.data);
                });
                window.addEventListener('storage', (event) => {
                    if (event.key !== referenceStorageKey || !event.newValue) return;

                    try {
                        insertReferenceImage(JSON.parse(event.newValue));
                    } catch (error) {
                        // Ignore malformed storage events.
                    }
                });
                window.addEventListener('focus', () => window.setTimeout(consumeStoredReference, 150));
            },
        }).catch(() => {
            wrapper.dataset.tinymceInitialized = 'false';
        });
    });
};

const syncContentEditorsFromInputs = () => {
    document.querySelectorAll('[data-tinymce-wrapper]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-tinymce-input]');
        const editor = wrapper.tinyMceEditor;
        if (!input || !editor) return;

        if (editor.getContent() !== input.value) editor.setContent(input.value || '');
    });
};

const referenceGallerySelection = new Map();

const updateReferenceGalleryControls = () => {
    const panel = document.querySelector('[data-reference-gallery-panel]');
    const count = document.querySelector('[data-reference-gallery-count]');
    const insertButton = document.querySelector('[data-reference-gallery-insert]');
    const exactSelection = Number(panel?.dataset.referenceSelectionExact || 0);
    if (panel) panel.hidden = referenceGallerySelection.size === 0;
    if (count) count.textContent = String(referenceGallerySelection.size);
    if (insertButton && exactSelection > 0) {
        insertButton.disabled = referenceGallerySelection.size !== exactSelection;
        insertButton.style.opacity = insertButton.disabled ? '.5' : '1';
        insertButton.style.cursor = insertButton.disabled ? 'not-allowed' : 'pointer';
    }
};

document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-reference-gallery-caption]');
    if (!input) return;

    const selected = referenceGallerySelection.get(input.dataset.referenceId);
    if (selected) selected.caption = input.value;
});

document.addEventListener('click', (event) => {
    const galleryToggle = event.target.closest('[data-reference-gallery-toggle]');
    if (galleryToggle) {
        event.preventDefault();
        const referenceId = galleryToggle.dataset.referenceId;

        if (referenceGallerySelection.has(referenceId)) {
            referenceGallerySelection.delete(referenceId);
            galleryToggle.setAttribute('aria-pressed', 'false');
            galleryToggle.textContent = 'Tambah ke Galeri';
            galleryToggle.classList.remove('bg-[#8B2A1A]');
            galleryToggle.classList.add('bg-[#376A64]');
        } else {
            const selectionLimit = Number(document.querySelector('[data-reference-gallery-panel]')?.dataset.referenceSelectionLimit || 0);
            if (selectionLimit > 0 && referenceGallerySelection.size >= selectionLimit) {
                window.alert(`Maksimal pilih ${selectionLimit} gambar.`);
                return;
            }

            const captionInput = document.querySelector(`[data-reference-gallery-caption][data-reference-id="${CSS.escape(referenceId)}"]`);
            referenceGallerySelection.set(referenceId, {
                url: galleryToggle.dataset.imageUrl,
                title: galleryToggle.dataset.imageTitle,
                alt_text: galleryToggle.dataset.imageAlt,
                caption: captionInput?.value || galleryToggle.dataset.imageAlt || galleryToggle.dataset.imageTitle,
            });
            galleryToggle.setAttribute('aria-pressed', 'true');
            galleryToggle.textContent = 'Batalkan Pilihan';
            galleryToggle.classList.remove('bg-[#376A64]');
            galleryToggle.classList.add('bg-[#8B2A1A]');
        }

        updateReferenceGalleryControls();
        return;
    }

    const galleryInsert = event.target.closest('[data-reference-gallery-insert]');
    if (galleryInsert) {
        event.preventDefault();
        if (!referenceGallerySelection.size) return;

        const payload = {
            type: 'pasopati-reference-gallery-selected',
            editor: galleryInsert.dataset.editorKey,
            images: Array.from(referenceGallerySelection.values()),
            selectedAt: Date.now(),
        };

        try {
            window.localStorage.setItem(`pasopati-reference-selection:${payload.editor}`, JSON.stringify(payload));
        } catch (error) {
            // postMessage below remains available when browser storage is restricted.
        }

        if (window.opener && !window.opener.closed) {
            window.opener.postMessage(payload, window.location.origin);
            window.setTimeout(() => window.close(), 100);
        } else if (window.parent !== window) {
            window.parent.postMessage(payload, window.location.origin);
        }
        return;
    }

    const button = event.target.closest('[data-reference-select]');
    if (!button) return;

    event.preventDefault();
    const payload = {
        type: 'pasopati-reference-selected',
        editor: button.dataset.editorKey,
        image: {
            url: button.dataset.imageUrl,
            title: button.dataset.imageTitle,
            alt_text: button.dataset.imageAlt,
        },
        selectedAt: Date.now(),
    };

    try {
        window.localStorage.setItem(`pasopati-reference-selection:${payload.editor}`, JSON.stringify(payload));
    } catch (error) {
        // postMessage below remains available when browser storage is restricted.
    }

    if (window.opener && !window.opener.closed) {
        window.opener.postMessage(payload, window.location.origin);
        window.setTimeout(() => window.close(), 100);
    } else if (window.parent !== window) {
        window.parent.postMessage(payload, window.location.origin);
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTinyMceEditors);
} else {
    initializeTinyMceEditors();
}

document.addEventListener('livewire:init', initializeTinyMceEditors);
document.addEventListener('livewire:init', () => {
    // `morphed` berjalan sekali setelah seluruh komponen diperbarui. Memakai
    // `morph.updated` di sini akan berjalan sekali untuk SETIAP elemen DOM yang
    // berubah, dan itu berulang kali menginisialisasi ulang editor.
    window.Livewire?.hook('morphed', () => {
        window.setTimeout(() => {
            initializeTinyMceEditors();
            syncContentEditorsFromInputs();
        }, 0);
    });
    window.Livewire?.hook('morph.removing', ({ el }) => {
        el.querySelectorAll?.('[data-tinymce-wrapper]').forEach((wrapper) => {
            wrapper.tinyMceEditor?.remove();
        });
    });
});
document.addEventListener('livewire:navigated', initializeTinyMceEditors);
