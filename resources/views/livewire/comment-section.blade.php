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
        init() {
            @if ($isGuest && $siteKey)
                this.widgetId = null;
                this.replyWidgetId = null;
                this.waitTurnstile();
                window.addEventListener('captcha:reset', () => this.resetCaptcha());
            @endif
            window.addEventListener('resize', () => this.resizeTick++);
            if (document.fonts) { document.fonts.ready.then(() => this.resizeTick++); }

            // Render captcha untuk form balasan begitu form terbuka;
            // buka juga thread balasan agar balasan baru langsung terlihat.
            this.$watch('replyTo', (value) => {
                @if ($isGuest && $siteKey)
                    if (value) {
                        this.$nextTick(() => this.renderReplyCaptcha(value));
                    } else {
                        this.resetReplyCaptcha();
                    }
                @endif
                if (value) {
                    this.openThreads[value] = true;
                }
            });

            // Setelah komentar/balasan berhasil dikirim, pastikan thread
            // balasan tempat balasan baru berada tetap terbuka.
            window.addEventListener('comment-posted', (e) => {
                const parentId = e.detail?.parentId ?? 0;
                if (parentId) {
                    this.openThreads[parentId] = true;
                }
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
            @if ($isGuest && $siteKey)
                if (! this.widgetId) { this.waitTurnstile(); return; }
                if (! this.captchaReady) {
                    try { window.turnstile.reset(this.widgetId); } catch (e) { this.renderCaptcha(); }
                }
            @endif
        },
        activeTextarea() {
            return document.getElementById(this.replyTo ? 'ta-reply-' + this.replyTo : 'ta-root');
        },
        syncDraft(ta) {
            if (this.replyTo) {
                this.replyBodyDraft = ta.value;
            } else {
                this.bodyDraft = ta.value;
            }
        },
        fmt(type) {
            const ta = this.activeTextarea();
            if (! ta) return;
            const s = ta.selectionStart, e = ta.selectionEnd;
            const sel = ta.value.slice(s, e);
            let before = '', after = '', content = sel;
            if (type === 'bold') { before = '**'; after = '**'; }
            else if (type === 'italic') { before = '_'; after = '_'; }
            else if (type === 'link') { before = '['; after = '](https://)'; content = sel || (@js($locale === 'id' ? 'teks tautan' : 'link text')); }
            ta.setRangeText(before + content + after, s, e, 'end');
            this.syncDraft(ta);
            ta.focus();
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
            @if ($isGuest)
                if (! name || ! name.trim()) return false;
                if (! captchaOk) return false;
            @endif
            return true;
        },
    }"
    x-effect="if (replyTo) { $nextTick(() => document.getElementById('reply-form-' + replyTo)?.scrollIntoView({ behavior: 'smooth', block: 'center' })); }"
    class="max-w-[720px] mx-auto px-5 mt-[100px] mb-[100px] border-t border-auriga-line pt-10"
    aria-label="Kolom komentar"
    x-init="init()"
>
    <style>[x-cloak] { display: none !important; }</style>

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
        @else
            <p class="px-1 text-xs text-auriga-muted">{{ $t['login_sebagai'] }} <span class="font-semibold">{{ auth()->user()->name }}</span></p>
        @endif

        <div class="mt-4 overflow-hidden bg-[#f5f5f5] transition-all duration-300 focus-within:bg-[#fafafa]">
            <label for="comment-message" class="sr-only">{{ $t['komentar'] }}</label>
            <textarea id="ta-root" wire:model="body" x-model="bodyDraft" x-on:focus="focused = true; $nextTick(() => nudgeCaptcha())" rows="1" maxlength="1000" placeholder="{{ $t['placeholder'] }}" class="block min-h-16 w-full resize-none border-0 bg-transparent px-5 py-4 text-sm leading-7 outline-none transition-all duration-300 placeholder:text-auriga-muted/45 focus:ring-0 sm:text-base"></textarea>
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
    <div class="mt-9 space-y-7">
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