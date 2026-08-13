@php
    $isGuest = ! auth()->check();
    $siteKey = config('services.turnstile.site_key');
    $locale = app()->getLocale();
    $monthsId = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $monthsEn = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $months = $locale === 'id' ? $monthsId : $monthsEn;

    // Kedalaman benang bersarang maksimum (root + 3 level balasan), sesuai
    // design reference. $totalComments & $openThreads dilewatkan dari komponen.
    $maxDepth = 3;

    $t = [
        'diskusi' => $locale === 'id' ? 'Diskusi' : 'Discussion',
        'komentar' => $locale === 'id' ? 'Komentar' : 'Comments',
        'komentar_count' => $locale === 'id' ? 'komentar' : 'comments',
        'nama' => $locale === 'id' ? 'Nama Anda' : 'Your name',
        'placeholder' => $locale === 'id' ? 'Apa pendapat Anda?' : 'What do you think?',
        'reply_placeholder' => $locale === 'id' ? 'Tulis balasan...' : 'Write a reply...',
        'verify' => $locale === 'id' ? 'Verifikasi keamanan' : 'Security check',
        'captcha_load' => $locale === 'id' ? 'Memuat CAPTCHA...' : 'Loading CAPTCHA...',
        'batal' => $locale === 'id' ? 'Batal' : 'Cancel',
        'kirim' => $locale === 'id' ? 'Kirim' : 'Post',
        'balas' => $locale === 'id' ? 'Balas' : 'Reply',
        'lihat' => $locale === 'id' ? 'Lihat' : 'Show',
        'tutup' => $locale === 'id' ? 'Tutup' : 'Hide',
        'balasan' => $locale === 'id' ? 'balasan' : 'replies',
        'empty' => $locale === 'id' ? 'Belum ada komentar. Jadilah yang pertama memberi tanggapan.' : 'No comments yet. Be the first to respond.',
        'moderasi' => $locale === 'id' ? 'Komentar akan dimoderasi sebelum tampil. Kata kasar otomatis disensor.' : 'Comments are moderated before appearing. Profanity is auto-filtered.',
        'login_sebagai' => $locale === 'id' ? 'Login sebagai' : 'Signed in as',
        'atau' => $locale === 'id' ? 'atau' : 'or',
        'google_login' => $locale === 'id' ? 'Masuk untuk ikut berdiskusi. Login hanya digunakan untuk komentar.' : 'Sign in to join the discussion. Login is only used for comments.',
        'google_btn' => $locale === 'id' ? 'Lanjutkan dengan Google' : 'Continue with Google',
        'keluar' => $locale === 'id' ? 'Keluar' : 'Sign out',
    ];
@endphp

{{-- Turnstile (guest root comment):
    - renderCaptcha: render widget sekali saat fokus. error-callback TIDAK
      hard-lock form (captchaReady tetap true) supaya hostname yang belum
      di-whitelist tetap bisa mencoba kirim; server menolak bila token kosong.
    - resetCaptcha: setelah submit, pakai turnstile.reset(widgetId) di tempat
      (bukan remove()+render() ulang) supaya widget lama yang sudah dimorph
      Livewire gak melempar warning "Cannot find Widget".
    - #captcha-root diberi wire:ignore agar Livewire gak menghapus DOM widget.
    Catatan: jangan taruh literal " (double-quote) di dalam x-data — itu
    menutup attribute HTML & merusak seluruh objek Alpine. --}}
<section
    x-data="{
        focused: false,
        replyTo: @entangle('replyingTo'),
        bodyDraft: @entangle('body'),
        replyBodyDraft: @entangle('replyBody'),
        nameDraft: @entangle('commentName'),
        replyNameDraft: @entangle('replyName'),
        captchaReady: {{ $isGuest ? 'false' : 'true' }},
        replyCaptchaReady: {{ $isGuest ? 'false' : 'true' }},
        openThreads: @js($openThreads),
        resizeTick: 0,
        // Dilewatkan dari PHP sekali sebagai boolean. JANGAN pakai directive
        // Blade bersyarat di dalam x-data: Laravel 12 membungkusnya dengan
        // marker komentar HTML, dan '<!--' adalah line-comment JS sehingga
        // marker itu merusak seluruh objek Alpine. Pakai if (this.isGuest)
        // dan if (this.captchaOn) sebagai pengganti.
        isGuest: {{ $isGuest ? 'true' : 'false' }},
        captchaOn: {{ ($isGuest && $siteKey) ? 'true' : 'false' }},
        init() {
            if (this.captchaOn) {
                this.widgetId = null;
                this.replyWidgetId = null;
                this.waitTurnstile();
                window.addEventListener('captcha:reset', () => this.resetCaptcha());
            }
            window.addEventListener('resize', () => this.resizeTick++);
            if (document.fonts) { document.fonts.ready.then(() => this.resizeTick++); }

            // Render captcha untuk form balasan begitu form terbuka;
            // buka juga thread balasan agar balasan baru langsung terlihat.
            this.$watch('replyTo', (value) => {
                if (this.captchaOn) {
                    if (value) {
                        this.$nextTick(() => this.renderReplyCaptcha(value));
                    } else {
                        this.resetReplyCaptcha();
                    }
                }
                if (value) {
                    this.openThreads[value] = true;
                    // Inisialisasi contenteditable balasan dari draft (biasanya
                    // kosong) begitu form terbuka — wire:ignore membuat Livewire
                    // gak me-reset isinya, jadi kita yang atur.
                    this.$nextTick(() => {
                        const ed = document.getElementById('ta-reply-' + value);
                        if (ed) { ed.innerHTML = this.mdToHtml(this.replyBodyDraft); }
                    });
                } else {
                    // Batal balas: kosongkan semua editor balasan (form tersembunyi,
                    // tapi contenteditable pakai wire:ignore jadi isinya gak
                    // auto-reset oleh Livewire).
                    document.querySelectorAll('[id^=\'ta-reply-\']').forEach((ed) => { ed.innerHTML = ''; });
                }
            });

            // Inisialisasi editor root dari draft (markdown → HTML).
            this.$nextTick(() => {
                const ed = document.getElementById('ta-root');
                if (ed) { ed.innerHTML = this.mdToHtml(this.bodyDraft); }
            });

            // Setelah komentar/balasan berhasil dikirim, pastikan thread
            // balasan tempat balasan baru berada tetap terbuka, dan kosongkan
            // editor (Livewire reset body/replyBody, tapi contenteditable pakai
            // wire:ignore jadi harus kita bersihkan manual).
            window.addEventListener('comment-posted', (e) => {
                const parentId = e.detail?.parentId ?? 0;
                if (parentId) {
                    this.openThreads[parentId] = true;
                }
                // Kosongkan editor yang dipakai: root kalau bukan balasan,
                // ta-reply-{parentId} kalau balasan.
                const ed = document.getElementById(parentId ? 'ta-reply-' + parentId : 'ta-root');
                if (ed) { ed.innerHTML = ''; }
            });
        },
        waitTurnstile() {
            if (window.turnstile) { this.renderCaptcha(); }
            else { setTimeout(() => this.waitTurnstile(), 80); }
        },
        renderCaptcha() {
            const el = document.getElementById('captcha-root');
            if (! el || ! window.turnstile) return;

            if (this.widgetId) {
                try { window.turnstile.remove(this.widgetId); } catch (e) {}
                this.widgetId = null;
            }

            el.innerHTML = '';
            this.widgetId = window.turnstile.render(el, {
                sitekey: @js($siteKey),
                callback: (token) => { this.captchaReady = true; this.captchaError = false; $wire.set('captchaToken', token); },
                'expired-callback': () => { this.captchaReady = false; $wire.set('captchaToken', ''); },
                'error-callback': () => { this.captchaError = true; this.captchaReady = true; },
            });
        },
        renderReplyCaptcha(commentId) {
            const el = document.getElementById('captcha-root-reply-' + commentId);
            if (! el || ! window.turnstile) return;

            if (this.replyWidgetId) {
                try { window.turnstile.remove(this.replyWidgetId); } catch (e) {}
                this.replyWidgetId = null;
            }

            el.innerHTML = '';
            this.replyWidgetId = window.turnstile.render(el, {
                sitekey: @js($siteKey),
                callback: (token) => { this.replyCaptchaReady = true; this.captchaError = false; $wire.set('captchaToken', token); },
                'expired-callback': () => { this.replyCaptchaReady = false; $wire.set('captchaToken', ''); },
                'error-callback': () => { this.captchaError = true; this.replyCaptchaReady = true; },
            });
        },
        resetCaptcha() {
            if (this.widgetId && window.turnstile) {
                try { window.turnstile.reset(this.widgetId); this.captchaReady = false; $wire.set('captchaToken', ''); return; } catch (e) {}
            }
            this.renderCaptcha();
        },
        resetReplyCaptcha() {
            if (this.replyWidgetId && window.turnstile) {
                try { window.turnstile.remove(this.replyWidgetId); } catch (e) {}
                this.replyWidgetId = null;
            }
            this.replyCaptchaReady = false;
            $wire.set('captchaToken', '');
        },
        // Saat user fokus ke textarea root, container captcha jadi terlihat
        // (x-show focused). Widget sudah di-render di init, tapi kalau dia
        // belum solve (mis. karena render di container tersembunyi), kita
        // reset di tempat sekarang sudah visible supaya callback jalan.
        nudgeCaptcha() {
            if (this.captchaOn) {
                if (! this.widgetId) { this.waitTurnstile(); return; }
                if (! this.captchaReady) {
                    try { window.turnstile.reset(this.widgetId); } catch (e) { this.renderCaptcha(); }
                }
            }
        },
        // Editor komentar pakai contenteditable (WYSIWYG: bold/italic/link
        // langsung kelihatan di kotak input). Tapi yang disimpan tetap MARKDOWN
        // — dikonversi dari HTML editor → markdown saat sync, dan markdown →
        // HTML saat init. Jadi gak ada HTML user yang tersimpan (aman dari XSS),
        // komentar lama (markdown) tetap kompatibel, dan Comment::formatBody
        // gak berubah.
        activeEditor() {
            return document.getElementById(this.replyTo ? 'ta-reply-' + this.replyTo : 'ta-root');
        },
        // markdown → HTML untuk di-render ke contenteditable saat init.
        // Escape dulu supaya <script> user jadi teks biasa, baru terapkan
        // penanda markdown menjadi tag.
        mdToHtml(md) {
            if (! md) return '';
            let h = String(md).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            h = h.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                 .replace(/_([^_]+)_/g, '<em>$1</em>')
                 .replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, '<a href=\'$2\'>$1</a>');
            // Blok daftar diparse dulu (baris '- ' / '1. ') sebelum sisa baris
            // baru dijadikan <br>, supaya <ul>/<ol>/<li> utuh bukan dipecah <br>.
            h = this.listsToHtml(h);
            h = h.replace(/\n/g, '<br>');
            return h;
        },
        // markdown daftar → <ul>/<ol>/<li> (dukung bersarang via indent 2 spasi).
        listsToHtml(h) {
            const lines = h.split('\n');
            const indentLen = (s) => s.replace(/\t/g, '  ').length;
            const parseBlock = () => {
                const m = lines[pos] && lines[pos].match(/^(\s*)(-|\d+\.)\s+(.*)$/);
                if (! m) return '';
                const base = indentLen(m[1]);
                const ordered = /\d+\./.test(m[2]);
                let html = '<' + (ordered ? 'ol' : 'ul') + '>';
                while (pos < lines.length) {
                    const lm = lines[pos] && lines[pos].match(/^(\s*)(-|\d+\.)\s+(.*)$/);
                    if (! lm || indentLen(lm[1]) !== base) break;
                    if ((/\d+\./.test(lm[2])) !== ordered) break;
                    const content = lm[3];
                    pos++;
                    let nested = '';
                    while (pos < lines.length) {
                        const nm = lines[pos] && lines[pos].match(/^(\s*)(-|\d+\.)\s+(.*)$/);
                        if (nm && indentLen(nm[1]) > base) {
                            const block = parseBlock();
                            if (block) nested += block; else break;
                        } else break;
                    }
                    html += '<li>' + content + nested + '</li>';
                }
                return html + '</' + (ordered ? 'ol' : 'ul') + '>';
            };
            let pos = 0;
            let out = '';
            while (pos < lines.length) {
                if (lines[pos].match(/^(\s*)(-|\d+\.)\s+(.*)$/)) {
                    out += parseBlock();
                } else {
                    out += lines[pos] + '\n';
                    pos++;
                }
            }
            return out;
        },
        // HTML contenteditable → markdown (simpan sebagai body/replyBody).
        // Jalan DOM node-demi-node supaya tag bersarang ikut tertangani.
        htmlToMd(node) {
            // Bisa dipanggil langsung pada node teks (dari listToMd yang iterasi
            // child <li>); node teks tidak punya childNodes, jadi tangani di sini.
            if (node.nodeType === 3) { return node.textContent; }
            let out = '';
            node.childNodes.forEach((child) => {
                if (child.nodeType === 3) {
                    out += child.textContent;
                } else if (child.nodeType === 1) {
                    const tag = child.tagName.toLowerCase();
                    if (tag === 'ul' || tag === 'ol') {
                        out += this.listToMd(child, tag === 'ol', '');
                    } else {
                        const inner = this.htmlToMd(child);
                        if (tag === 'strong' || tag === 'b') out += '**' + inner + '**';
                        else if (tag === 'em' || tag === 'i') out += '_' + inner + '_';
                        else if (tag === 'a') {
                            const href = child.getAttribute('href') || '';
                            out += /^https?:\/\//i.test(href) ? '[' + inner + '](' + href + ')' : inner;
                        } else if (tag === 'br') { out += '\n'; }
                        else if (tag === 'p' || tag === 'div') { out += inner + '\n'; }
                        else { out += inner; }
                    }
                }
            });
            return out;
        },
        // <ul>/<ol> editor → markdown ('- ' / '1. ', indent 2 spasi per level).
        // <li> dipisah: anak inline jadi teks marker, anak <ul>/<ol> jadi
        // blok daftar bersarang (indent lebih dalam).
        listToMd(list, ordered, indent) {
            let out = '';
            let i = 1;
            Array.from(list.children).forEach((li) => {
                if (li.tagName.toLowerCase() !== 'li') return;
                const marker = ordered ? (i++) + '. ' : '- ';
                let inline = '';
                let nested = '';
                Array.from(li.childNodes).forEach((c) => {
                    const tg = c.nodeType === 1 ? c.tagName.toLowerCase() : '';
                    if (tg === 'ul' || tg === 'ol') {
                        nested += this.listToMd(c, tg === 'ol', indent + '  ');
                    } else {
                        inline += this.htmlToMd(c, indent);
                    }
                });
                inline = inline.replace(/\n+/g, ' ').replace(/^\s+|\s+$/g, '');
                out += '\n' + indent + marker + inline + nested;
            });
            return out;
        },
        syncEditor(ed) {
            const md = this.htmlToMd(ed).replace(/\n{3,}/g, '\n\n').replace(/^\s+|\s+$/g, '');
            if (this.replyTo) { this.replyBodyDraft = md; } else { this.bodyDraft = md; }
            // Kosongkan beneran supaya :empty:before (placeholder) muncul lagi.
            if (! md) { ed.innerHTML = ''; }
        },
        // Pilih teks di dalam <a> terakhir (placeholder link baru) supaya user
        // tinggal mengetik ganti.
        selectLastLinkText(ed) {
            const links = ed.querySelectorAll('a');
            const a = links[links.length - 1];
            if (! a) return;
            const range = document.createRange();
            range.selectNodeContents(a);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        },
        fmt(type) {
            const ed = this.activeEditor();
            if (! ed) return;
            ed.focus();
            const sel = window.getSelection();
            if (type === 'bold') { document.execCommand('bold'); }
            else if (type === 'italic') { document.execCommand('italic'); }
            else if (type === 'bullet') { document.execCommand('insertUnorderedList'); }
            else if (type === 'number') { document.execCommand('insertOrderedList'); }
            else if (type === 'link') {
                const placeholder = @js($locale === 'id' ? 'teks tautan' : 'link text');
                if (! sel || sel.isCollapsed || ! sel.toString().trim()) {
                    document.execCommand('insertHTML', false, '<a href=\'https://\'>' + placeholder + '</a>');
                    this.selectLastLinkText(ed);
                } else {
                    document.execCommand('createLink', false, 'https://');
                }
            }
            this.syncEditor(ed);
            ed.focus();
        },
        startReply(id) {
            $wire.call('setReplyTo', id);
        },
        cancelReply() {
            $wire.call('setReplyTo', null);
            $wire.set('replyBody', '');
        },
        layoutConnector(cid, connector) {
            this.resizeTick;
            const article = connector.closest('article');
            if (! article) return;
            const avatar = article.querySelector('[data-avatar]');
            const replyList = article.querySelector('[data-replylist]');
            if (! avatar || ! replyList) { connector.classList.add('hidden'); return; }
            const lastReply = replyList.lastElementChild;
            const expanded = this.openThreads[cid];
            if (! expanded || ! lastReply) { connector.classList.add('hidden'); return; }
            const listRect = replyList.getBoundingClientRect();
            if (listRect.height === 0) { connector.classList.add('hidden'); return; }
            const aRect = avatar.getBoundingClientRect();
            const artRect = article.getBoundingClientRect();
            const lastRect = lastReply.getBoundingClientRect();
            const top = aRect.bottom - artRect.top;
            const bottom = lastRect.top - artRect.top - 32;
            const height = Math.max(0, bottom - top);
            connector.style.left = (aRect.left + (aRect.width / 2) - artRect.left) + 'px';
            connector.style.top = top + 'px';
            connector.style.height = height + 'px';
            connector.classList.toggle('hidden', height === 0);
        },
        get canPost() {
            const body = this.replyTo ? this.replyBodyDraft : this.bodyDraft;
            const name = this.replyTo ? this.replyNameDraft : this.nameDraft;
            const captchaOk = this.replyTo ? this.replyCaptchaReady : this.captchaReady;
            if (! body || body.trim() === '') return false;
            if (this.isGuest) {
                if (! name || ! name.trim()) return false;
                if (! captchaOk) return false;
            }
            return true;
        },
    }"
    x-effect="if (replyTo) { $nextTick(() => document.getElementById('reply-form-' + replyTo)?.scrollIntoView({ behavior: 'smooth', block: 'center' })); }"
    class="max-w-[720px] mx-auto px-5 mt-[100px] mb-[100px] border-t border-auriga-line pt-10"
    aria-label="Kolom komentar"
    x-init="init()"
>
    <style>
        [x-cloak] { display: none !important; }
        .ce-input:empty:before { content: attr(data-placeholder); color: rgba(122, 110, 96, 0.45); pointer-events: none; }
        .ce-input a { color: #2B5343; text-decoration: underline; }
        .ce-input ul, .ce-input ol { margin: 0.5rem 0 0.5rem 1.25rem; padding-left: 0.5rem; }
        .ce-input ul { list-style: disc outside; }
        .ce-input ol { list-style: decimal outside; }
        .ce-input li { margin: 0.15rem 0; }
        .ce-input li > ul, .ce-input li > ol { margin: 0.15rem 0 0.15rem 0.5rem; }
        .comment-list ul, .comment-list ol { margin: 0.4rem 0 0.4rem 1.1rem; padding-left: 0.4rem; }
        .comment-list ul { list-style: disc outside; }
        .comment-list ol { list-style: decimal outside; }
        .comment-list li { margin: 0.1rem 0; }
        .comment-list li > ul, .comment-list li > ol { margin: 0.1rem 0 0.1rem 0.5rem; }
    </style>

    {{-- Header --}}
    <div class="flex items-end justify-between gap-5">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#2B5343]">{{ $t['diskusi'] }}</p>
            <h2 class="mt-2 text-2xl font-black tracking-[-0.035em]">{{ $t['komentar'] }}</h2>
        </div>
        <p class="text-xs text-auriga-muted">{{ $totalComments }} {{ $t['komentar_count'] }}</p>
    </div>

    {{-- Form komentar utama (root) --}}
    <form wire:submit="submit" class="mt-8">
        @csrf
        <div class="hidden" aria-hidden="true">
            <label>Website (jangan diisi)<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
        </div>

        @if ($isGuest)
            <div class="flex items-center gap-3 px-1">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#2B5343] text-base font-semibold uppercase text-white"
                    aria-hidden="true"
                    x-text="(nameDraft || 'A').charAt(0).toUpperCase()"
                >A</div>
                <label for="comment-name" class="sr-only">{{ $t['nama'] }}</label>
                <input id="comment-name" type="text" x-model="nameDraft" maxlength="60" autocomplete="name" placeholder="{{ $t['nama'] }}" class="min-w-0 flex-1 border-x-0 border-t-0 border-b border-[#e5e7eb] bg-transparent px-1 py-2 text-base font-medium outline-none transition placeholder:font-normal placeholder:text-auriga-muted/55 focus:border-[#2B5343] focus:ring-0">
                <input type="hidden" wire:model="commentName">
            </div>
            @error('commentName') <p class="mt-1 px-1 text-xs text-auriga-red">{{ $message }}</p> @enderror

            {{-- Opsi login Google: nama + avatar otomatis terisi, captcha hilang.
                 Sembunyi otomatis begitu tamu mengisi nama (termasuk nama yang
                 sudah ter-isi dari cookie tamu lama) — jadi gak menumpuk dengan
                 jalur tamu yang sudah dipilih. --}}
            <div x-show="!(nameDraft || '').trim()" x-cloak>
                <div class="mt-4 flex items-center gap-3" aria-hidden="true">
                    <span class="h-px flex-1 bg-[#e2d8cc]"></span>
                    <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-auriga-muted">{{ $t['atau'] }}</span>
                    <span class="h-px flex-1 bg-[#e2d8cc]"></span>
                </div>
                <div class="mt-4 border border-[#e2d8cc] bg-[#f8f6f2] p-5 text-center">
                    <p class="text-sm text-[#6f665c]">{{ $t['google_login'] }}</p>
                    <a href="{{ route('comment.google.login') }}?intended={{ urlencode(url()->current()) }}"
                       class="mt-4 inline-flex items-center gap-3 bg-white px-5 py-3 text-xs font-bold shadow-sm ring-1 ring-[#d8cec2] transition hover:bg-gray-50">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.5-.2-2.2H12v4.3h5.4a4.6 4.6 0 0 1-2 3v2.8h3.5c2-1.9 3.2-4.6 3.2-7.9z"></path><path fill="#34A853" d="M12 22c2.9 0 5.3-.9 7-2.6l-3.5-2.8a6.4 6.4 0 0 1-9.6-3.4H2.3V16A10 10 0 0 0 12 22z"></path><path fill="#FBBC05" d="M5.9 13.2A6 6 0 0 1 5.6 12c0-.4.1-.8.2-1.2V8H2.3A10 10 0 0 0 2 12c0 1.4.3 2.8.8 4z"></path><path fill="#EA4335" d="M12 5.6c1.6 0 3 .5 4.1 1.6l3.1-3A10 10 0 0 0 2.3 8l3.6 2.8A6 6 0 0 1 12 5.6z"></path></svg>
                        {{ $t['google_btn'] }}
                    </a>
                </div>
            </div>
        @else
            @php
                $authUser = auth()->user();
                $authAvatar = $authUser->image
                    ? (Illuminate\Support\Str::startsWith($authUser->image, ['http://','https://']) ? $authUser->image : asset('storage/' . $authUser->image))
                    : null;
            @endphp
            <div class="flex items-center gap-3 px-1">
                @if ($authAvatar)
                    <img src="{{ $authAvatar }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover">
                @else
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#2B5343] text-base font-semibold uppercase text-white" aria-hidden="true">{{ strtoupper(mb_substr($authUser->name, 0, 1)) }}</div>
                @endif
                <p class="text-xs text-auriga-muted">
                    {{ $t['login_sebagai'] }} <span class="font-semibold text-auriga-ink">{{ $authUser->name }}</span>
                    <a href="{{ route('comment.logout') }}?intended={{ urlencode(url()->current()) }}" class="ml-2 font-medium text-[#2B5343] hover:underline">{{ $t['keluar'] }}</a>
                </p>
            </div>
        @endif

        <div class="mt-4 overflow-hidden bg-[#f5f5f5] transition-all duration-300 focus-within:bg-[#fafafa]">
            <label for="comment-message" class="sr-only">{{ $t['komentar'] }}</label>
            <div id="ta-root" wire:ignore contenteditable="true" role="textbox" aria-multiline="true" aria-label="{{ $t['komentar'] }}" data-placeholder="{{ $t['placeholder'] }}" x-on:focus="focused = true; $nextTick(() => nudgeCaptcha())" x-on:input="syncEditor($el)" :class="focused ? 'min-h-28' : 'min-h-16'" class="ce-input block w-full whitespace-pre-wrap break-words border-0 bg-transparent px-5 py-4 text-sm leading-7 outline-none transition-all duration-300 focus:ring-0 sm:text-base"></div>
            @error('body') <p class="px-5 pb-3 text-xs text-auriga-red">{{ $message }}</p> @enderror

            @if ($isGuest && $siteKey)
                <div x-cloak x-show="focused" class="border-t border-black/5 px-5 py-4">
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.1em] text-auriga-muted">{{ $t['verify'] }}</p>
                    <div id="captcha-root" wire:ignore class="min-h-[65px]"><span class="text-xs text-auriga-muted">{{ $t['captcha_load'] }}</span></div>
                    @error('captchaToken') <p class="mt-2 text-xs text-auriga-red">{{ $message }}</p> @enderror
                </div>
            @endif

            <div x-cloak x-show="focused" class="flex items-center justify-between gap-4 border-t border-black/5 px-5 py-4">
                <div class="flex items-center gap-5 text-auriga-muted" aria-label="Format">
                    <button type="button" x-on:click="fmt('bold')" class="font-serif text-xl font-bold transition hover:text-auriga-ink" aria-label="Tebal">B</button>
                    <button type="button" x-on:click="fmt('italic')" class="font-serif text-xl italic transition hover:text-auriga-ink" aria-label="Miring">i</button>
                    <button type="button" x-on:click="fmt('link')" class="text-lg transition hover:text-auriga-ink" aria-label="Tautan">↗</button>
                    <button type="button" x-on:click="fmt('bullet')" class="transition hover:text-auriga-ink" aria-label="Daftar poin">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="4" cy="6" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="6" x2="21" y2="6"/><circle cx="4" cy="12" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="12" x2="21" y2="12"/><circle cx="4" cy="18" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <button type="button" x-on:click="fmt('number')" class="transition hover:text-auriga-ink" aria-label="Daftar bernomor">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><text x="1.5" y="8" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">1.</text><line x1="10" y1="6" x2="21" y2="6"/><text x="1.5" y="15" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">2.</text><line x1="10" y1="13" x2="21" y2="13"/><text x="1.5" y="22" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">3.</text><line x1="10" y1="20" x2="21" y2="20"/></svg>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" x-on:click="focused = false" class="px-3 py-2 text-xs font-medium text-auriga-ink transition hover:text-[#2B5343]">{{ $t['batal'] }}</button>
                    <button type="submit" :disabled="!canPost" :class="canPost ? 'bg-[#2B5343] enabled:hover:bg-[#1f3d31]' : 'bg-[#2B5343] cursor-not-allowed opacity-35'" class="rounded-full px-5 py-2.5 text-[10px] font-bold uppercase tracking-[0.1em] text-white transition">{{ $t['kirim'] }}</button>
                </div>
            </div>
        </div>

        <p class="mt-3 min-h-5 px-1 text-xs text-auriga-muted">{{ $t['moderasi'] }}</p>
    </form>

    {{-- Daftar komentar (benang bersarang, dirender rekursif via partial). --}}
    <div class="comment-list mt-9 space-y-7">
        @forelse ($comments as $comment)
            @include('livewire.partials.comment-item', [
                'comment' => $comment,
                'depth' => 0,
                'maxDepth' => $maxDepth,
                't' => $t,
                'months' => $months,
                'isGuest' => $isGuest,
                'siteKey' => $siteKey,
                'locale' => $locale,
            ])
        @empty
            <p class="text-sm text-auriga-muted">{{ $t['empty'] }}</p>
        @endforelse
    </div>

    @push('scripts')
        @if ($isGuest && $siteKey)
            <script>
                document.addEventListener('alpine:initialized', () => {
                    document.querySelectorAll('[x-data*="captchaReady"]').forEach(function (el) {
                        const cleanup = () => {
                            const cmp = el._x_data_stack?.[0] || el._x?.dataStack?.[0] || (window.Alpine && Alpine.$data(el));
                            if (cmp?.widgetId && window.turnstile) {
                                try { window.turnstile.remove(cmp.widgetId); } catch (e) {}
                            }
                            if (cmp?.replyWidgetId && window.turnstile) {
                                try { window.turnstile.remove(cmp.replyWidgetId); } catch (e) {}
                            }
                        };
                        if (! el._x_cleanups) el._x_cleanups = [];
                        el._x_cleanups.push(cleanup);
                    });
                });
            </script>
        @endif
    @endpush
</section>