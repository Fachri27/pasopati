<?php
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
?>

<div x-data="{
        focused: false,
        captchaReady: <?php echo e($isGuest && $siteKey ? 'false' : 'true'); ?>,
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
            const el = document.getElementById('captcha-root-<?php echo e($variant); ?>');
            if (! el || el.getAttribute('data-rendered') === 'true') return;
            el.setAttribute('data-rendered', 'true');
            el.innerHTML = '';
            this.widgetId = window.turnstile.render(el, {
                sitekey: <?php echo \Illuminate\Support\Js::from($siteKey)->toHtml() ?>,
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
            this.captchaReady = <?php echo e($isGuest && $siteKey ? 'false' : 'true'); ?>;
            $wire.set('captchaToken', '');
            const el = document.getElementById('captcha-root-<?php echo e($variant); ?>');
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
        <div class="<?php echo e($isArchive ? 'flex flex-col sm:flex-row gap-3' : 'flex flex-col sm:flex-row gap-3 max-w-[520px] mx-auto'); ?>">
            <input
                wire:model="email"
                type="email"
                placeholder="<?php echo e($placeholder); ?>"
                autocomplete="email"
                value="<?php echo e($preFilledEmail); ?>"
                required
                class="<?php echo e($inputClass); ?>"
                x-on:focus="focused = true; ensureCaptcha();"
                x-on:input="focused = true; ensureCaptcha();"
            >
            <button
                type="submit"
                wire:loading.attr="disabled"
                :disabled="!canSubmit"
                class="<?php echo e($buttonClass); ?>"
            >
                <span wire:loading.remove><?php echo e($buttonText); ?></span>
                <span wire:loading wire:target="subscribe"><?php echo e($isId ? 'Memproses...' : 'Processing...'); ?></span>
            </button>
        </div>

        <!--[if BLOCK]><![endif]--><?php if($isGuest && $siteKey): ?>
            <div x-show="focused || $wire.email.trim() !== ''" x-cloak class="mt-4">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.1em] text-ink-3">
                    <?php echo e($isId ? 'Verifikasi keamanan' : 'Security verification'); ?>

                </p>
                <div id="captcha-root-<?php echo e($variant); ?>" wire:ignore class="min-h-[65px]">
                    <span class="text-xs text-ink-3"><?php echo e($isId ? 'Memuat CAPTCHA...' : 'Loading CAPTCHA...'); ?></span>
                </div>
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['captchaToken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-3 text-sm text-red-600"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
    </form>

    <!--[if BLOCK]><![endif]--><?php if($statusMessage): ?>
        <p class="text-sm <?php echo e($statusMessageType === 'success' ? 'text-forest' : 'text-ink-2'); ?>">
            <?php echo e($statusMessage); ?>

        </p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /Users/aiti/pasopati/resources/views/livewire/deforestory-subscribe.blade.php ENDPATH**/ ?>