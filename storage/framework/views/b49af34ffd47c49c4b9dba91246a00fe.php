<?php
    $id = $comment->id;
    $isReply = $depth > 0;
    $initial = strtoupper(mb_substr($comment->displayName(), 0, 1));
    $dateLabel = $comment->created_at->day . ' ' . ($months[$comment->created_at->month - 1] ?? '') . ' ' . $comment->created_at->year;
    $replies = $comment->replies;
    $hasReplies = $replies->isNotEmpty();
    $descCount = $comment->descendantsCount();
    $canReply = $depth < $maxDepth;
?>

<article
    wire:key="comment-<?php echo e($id); ?>"
    class="relative <?php if($isReply): ?> comment-reply <?php else: ?> border-b border-auriga-line <?php endif; ?> pb-7"
>
    <div class="flex items-start gap-4">
        <div
            data-avatar="<?php echo e($id); ?>"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#e5ede9] text-sm font-black uppercase text-[#2B5343]"
            aria-hidden="true"
        ><?php echo e($initial); ?></div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h3 class="text-sm font-bold"><?php echo e($comment->displayName()); ?></h3>
                <time class="text-[10px] uppercase tracking-[0.08em] text-auriga-muted"><?php echo e($dateLabel); ?></time>
            </div>

            <div class="mt-1 text-sm leading-5 text-auriga-ink/75">
                <!--[if BLOCK]><![endif]--><?php if($isReply && $comment->mention_name): ?>
                    <span class="mr-1 font-semibold text-[#2B5343]">@ <?php echo e($comment->mention_name); ?></span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php echo App\Models\Comment::formatBody($comment->body); ?>

            </div>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                <!--[if BLOCK]><![endif]--><?php if($canReply): ?>
                    <button type="button" x-on:click="startReply(<?php echo e($id); ?>)" class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#2B5343] transition hover:text-[#1f3d31]"><?php echo e($t['balas']); ?></button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($hasReplies): ?>
                    <button type="button" x-on:click="openThreads[<?php echo e($id); ?>] = !openThreads[<?php echo e($id); ?>]" :aria-expanded="(openThreads[<?php echo e($id); ?>] || replyTo === <?php echo e($id); ?>) ? 'true' : 'false'" class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-[#2B5343] transition hover:text-[#1f3d31]">
                        <span class="flex h-4 w-4 shrink-0 items-center justify-center transition-transform" :class="(openThreads[<?php echo e($id); ?>] || replyTo === <?php echo e($id); ?>) ? 'rotate-180' : ''">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                        <span x-text="(openThreads[<?php echo e($id); ?>] || replyTo === <?php echo e($id); ?>) ? '<?php echo e($t['tutup']); ?> <?php echo e($descCount); ?> <?php echo e($t['balasan']); ?>' : '<?php echo e($t['lihat']); ?> <?php echo e($descCount); ?> <?php echo e($t['balasan']); ?>'"></span>
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!--[if BLOCK]><![endif]--><?php if($canReply): ?>
                
                <form wire:submit="submit" x-cloak x-show="replyTo === <?php echo e($id); ?>" id="reply-form-<?php echo e($id); ?>" class="mt-4">
                    <div class="hidden" aria-hidden="true">
                        <label>Website (jangan diisi)<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($isGuest): ?>
                        <div class="flex items-center gap-3 px-1">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#2B5343] text-sm font-semibold uppercase text-white" aria-hidden="true" x-text="(replyNameDraft || 'A').charAt(0).toUpperCase()">A</div>
                            <label for="reply-name-<?php echo e($id); ?>" class="sr-only"><?php echo e($t['nama']); ?></label>
                            <input id="reply-name-<?php echo e($id); ?>" type="text" x-model="replyNameDraft" maxlength="60" autocomplete="name" placeholder="<?php echo e($t['nama']); ?>" class="min-w-0 flex-1 border-x-0 border-t-0 border-b border-[#e5e7eb] bg-transparent px-1 py-2 text-sm font-medium outline-none transition placeholder:font-normal placeholder:text-auriga-muted/55 focus:border-[#2B5343] focus:ring-0">
                            <input type="hidden" wire:model="replyName">
                        </div>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['replyName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 px-1 text-xs text-auriga-red"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="<?php if($isGuest): ?> mt-3 <?php endif; ?> overflow-hidden bg-[#f5f5f5]">
                        <label for="ta-reply-<?php echo e($id); ?>" class="sr-only"><?php echo e($t['balas']); ?></label>
                        <div id="ta-reply-<?php echo e($id); ?>" wire:ignore contenteditable="true" role="textbox" aria-multiline="true" aria-label="<?php echo e($t['balas']); ?>" data-placeholder="<?php echo e($t['reply_placeholder']); ?>" x-on:input="syncEditor($el)" class="ce-input block min-h-28 w-full whitespace-pre-wrap break-words border-0 bg-transparent px-4 py-3 text-sm leading-6 outline-none focus:ring-0"></div>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['replyBody'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="px-4 pb-3 text-xs text-auriga-red"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($isGuest && $siteKey): ?>
                            <div class="border-t border-black/5 px-4 py-3">
                                <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.1em] text-auriga-muted"><?php echo e($t['verify']); ?></p>
                                <div id="captcha-root-reply-<?php echo e($id); ?>" wire:ignore class="min-h-[65px]"><span class="text-xs text-auriga-muted"><?php echo e($t['captcha_load']); ?></span></div>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['captchaToken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs text-auriga-red"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <div class="flex items-center justify-between gap-3 border-t border-black/5 px-4 py-3">
                            <div class="flex items-center gap-4 text-auriga-muted" aria-label="Format">
                                <button type="button" x-on:click="fmt('bold')" class="font-serif text-lg font-bold transition hover:text-auriga-ink" aria-label="Tebal">B</button>
                                <button type="button" x-on:click="fmt('italic')" class="font-serif text-lg italic transition hover:text-auriga-ink" aria-label="Miring">i</button>
                                <button type="button" x-on:click="fmt('link')" class="text-base transition hover:text-auriga-ink" aria-label="Tautan">↗</button>
                                <button type="button" x-on:click="fmt('bullet')" class="transition hover:text-auriga-ink" aria-label="Daftar poin">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="4" cy="6" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="6" x2="21" y2="6"/><circle cx="4" cy="12" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="12" x2="21" y2="12"/><circle cx="4" cy="18" r="1.4" fill="currentColor" stroke="none"/><line x1="9" y1="18" x2="21" y2="18"/></svg>
                                </button>
                                <button type="button" x-on:click="fmt('number')" class="transition hover:text-auriga-ink" aria-label="Daftar bernomor">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><text x="1.5" y="8" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">1.</text><line x1="10" y1="6" x2="21" y2="6"/><text x="1.5" y="15" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">2.</text><line x1="10" y1="13" x2="21" y2="13"/><text x="1.5" y="22" font-size="7.5" font-family="ui-sans-serif, system-ui, -apple-system, sans-serif" font-weight="700" fill="currentColor" stroke="none">3.</text><line x1="10" y1="20" x2="21" y2="20"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="cancelReply()" class="px-2 py-2 text-[10px] font-medium text-auriga-ink transition hover:text-[#2B5343]"><?php echo e($t['batal']); ?></button>
                                <button type="submit" :disabled="!canPost" :class="canPost ? 'bg-[#2B5343] enabled:hover:bg-[#1f3d31]' : 'bg-[#2B5343] cursor-not-allowed opacity-35'" class="rounded-full px-4 py-2 text-[9px] font-bold uppercase tracking-[0.1em] text-white transition"><?php echo e($t['kirim']); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($hasReplies): ?>
        <div
            data-replylist="<?php echo e($id); ?>"
            class="mt-5 space-y-0"
            x-show="openThreads[<?php echo e($id); ?>] || replyTo === <?php echo e($id); ?>"
            x-collapse
            x-init="(() => {
                const connector = document.getElementById('connector-<?php echo e($id); ?>');
                const cb = () => layoutConnector(<?php echo e($id); ?>, connector);
                cb();
                if (window.ResizeObserver) { new ResizeObserver(cb).observe($el); }
            })()"
        >
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('livewire.partials.comment-item', [
                    'comment' => $child,
                    'depth' => $depth + 1,
                    'maxDepth' => $maxDepth,
                    't' => $t,
                    'months' => $months,
                    'isGuest' => $isGuest,
                    'siteKey' => $siteKey,
                    'locale' => $locale,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <span id="connector-<?php echo e($id); ?>" class="pointer-events-none absolute z-0 w-px bg-[#e5e7eb] hidden" aria-hidden="true" x-effect="layoutConnector(<?php echo e($id); ?>, $el)"></span>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</article><?php /**PATH /Users/aiti/pasopati/resources/views/livewire/partials/comment-item.blade.php ENDPATH**/ ?>