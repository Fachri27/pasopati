@php
    $isArchive = ($variant ?? 'archive') === 'archive';
    $isId = $locale === 'id';
    $placeholder = $isId ? 'Alamat email Anda' : 'Your email address';
    $buttonText = $isId ? 'Langganan' : 'Subscribe';

    $inputClass = $isArchive
        ? 'flex-1 px-[1.15rem] py-[.95rem] text-[.95rem] border border-line rounded-[2px] outline-none bg-paper focus:border-pasopati transition-colors placeholder:text-ink-3'
        : 'flex-1 px-4 py-3.5 text-[.9rem] sm:text-[.95rem] border border-line outline-none bg-paper focus:border-forest focus:ring-1 focus:ring-forest/20 transition-all placeholder:text-ink-3';

    $buttonClass = $isArchive
        ? 'inline-flex items-center justify-center gap-2 px-6 py-[.95rem] text-[.9rem] font-bold bg-pasopati text-white rounded-[2px] whitespace-nowrap hover:bg-pasopati-d transition-colors disabled:opacity-70'
        : 'inline-flex items-center justify-center gap-2 px-6 py-3.5 text-[.88rem] sm:text-[.9rem] font-bold bg-ink text-white whitespace-nowrap hover:bg-black transition-colors disabled:opacity-70';
@endphp

<div x-data="{
        focused: false,
        captchaReady: {{ $isGuest && $siteKey ? 'false' : 'true' }},
        widgetId: null,
        init() {
            window.addEventListener('captcha:reset', () => this.resetCaptcha());
        },
        ensureCaptcha() {
            if (! this.focused && this.$wire.email.trim() === '') return;
            if (this.widgetId) return;
            if (! window.turnstile) {
                setTimeout(() => this.ensureCaptcha(), 120);
                return;
            }
            const el = document.getElementById('captcha-root-{{ $variant }}');
            if (! el || el.getAttribute('data-rendered') === 'true') return;
            el.setAttribute('data-rendered', 'true');
            el.innerHTML = '';
            this.widgetId = window.turnstile.render(el, {
                sitekey: @js($siteKey),
                callback: (token) => { this.captchaReady = true; $wire.set('captchaToken', token); },
                'expired-callback': () => { this.captchaReady = false; $wire.set('captchaToken', ''); },
                'error-callback': () => { this.captchaReady = false; },
            });
        },
        resetCaptcha() {
            if (this.widgetId && window.turnstile) {
                try { window.turnstile.remove(this.widgetId); } catch (e) {}
            }
            this.widgetId = null;
            this.captchaReady = {{ $isGuest && $siteKey ? 'false' : 'true' }};
            $wire.set('captchaToken', '');
            const el = document.getElementById('captcha-root-{{ $variant }}');
            if (el) el.removeAttribute('data-rendered');
            setTimeout(() => { if (this.focused) this.ensureCaptcha(); }, 0);
        },
        get canSubmit() {
            return this.$wire.email.trim() !== '' && this.captchaReady;
        },
    }"
    x-init="init()"
>
    <form wire:submit="subscribe" class="w-full" x-show="! $wire.subscribed" x-cloak>
        <div class="{{ $isArchive ? 'flex flex-col sm:flex-row gap-3' : 'flex flex-col sm:flex-row gap-3 max-w-[520px] mx-auto' }}">
            <input
                wire:model="email"
                type="email"
                placeholder="{{ $placeholder }}"
                autocomplete="email"
                value="{{ $preFilledEmail }}"
                required
                class="{{ $inputClass }}"
                x-on:focus="focused = true; ensureCaptcha();"
                x-on:input="focused = true; ensureCaptcha();"
            >
            <button
                type="submit"
                wire:loading.attr="disabled"
                :disabled="!canSubmit"
                class="{{ $buttonClass }}"
            >
                <span wire:loading.remove>{{ $buttonText }}</span>
                <span wire:loading wire:target="subscribe">{{ $isId ? 'Memproses...' : 'Processing...' }}</span>
            </button>
        </div>

        @if ($isGuest && $siteKey)
            <div x-show="focused || $wire.email.trim() !== ''" x-cloak class="mt-4">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.1em] text-ink-3">
                    {{ $isId ? 'Verifikasi keamanan' : 'Security verification' }}
                </p>
                <div id="captcha-root-{{ $variant }}" wire:ignore class="min-h-[65px]">
                    <span class="text-xs text-ink-3">{{ $isId ? 'Memuat CAPTCHA...' : 'Loading CAPTCHA...' }}</span>
                </div>
                @error('captchaToken')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        @error('email')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>

    @if ($statusMessage)
        <p class="text-sm {{ $statusMessageType === 'success' ? 'text-forest' : 'text-ink-2' }}">
            {{ $statusMessage }}
        </p>
    @endif
</div>
