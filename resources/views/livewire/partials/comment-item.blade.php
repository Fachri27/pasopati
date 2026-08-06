@php
    $id = $comment->id;
    $isReply = $depth > 0;
    $initial = strtoupper(mb_substr($comment->displayName(), 0, 1));
    $dateLabel = $comment->created_at->day . ' ' . ($months[$comment->created_at->month - 1] ?? '') . ' ' . $comment->created_at->year;
    $replies = $comment->replies;
    $hasReplies = $replies->isNotEmpty();
    $descCount = $comment->descendantsCount();
    $canReply = $depth < $maxDepth;
@endphp

<article
    wire:key="comment-{{ $id }}"
    class="relative @if ($isReply) comment-reply @else border-b border-auriga-line @endif pb-7"
>
    <div class="flex items-start gap-4">
        <div
            data-avatar="{{ $id }}"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#e5ede9] text-sm font-black uppercase text-[#2B5343]"
            aria-hidden="true"
        >{{ $initial }}</div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h3 class="text-sm font-bold">{{ $comment->displayName() }}</h3>
                <time class="text-[10px] uppercase tracking-[0.08em] text-auriga-muted">{{ $dateLabel }}</time>
            </div>

            <p class="mt-1 text-sm leading-5 text-auriga-ink/75">
                @if ($isReply && $comment->mention_name)
                    <span class="mr-1 font-semibold text-[#2B5343]">@ {{ $comment->mention_name }}</span>
                @endif
                {!! App\Models\Comment::formatBody($comment->body) !!}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                @if ($canReply)
                    <button type="button" x-on:click="startReply({{ $id }})" class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#2B5343] transition hover:text-[#1f3d31]">{{ $t['balas'] }}</button>
                @endif

                @if ($hasReplies)
                    <button type="button" x-on:click="openThreads[{{ $id }}] = !openThreads[{{ $id }}]" :aria-expanded="(openThreads[{{ $id }}] || replyTo === {{ $id }}) ? 'true' : 'false'" class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-[#2B5343] transition hover:text-[#1f3d31]">
                        <span class="flex h-4 w-4 shrink-0 items-center justify-center transition-transform" :class="(openThreads[{{ $id }}] || replyTo === {{ $id }}) ? 'rotate-180' : ''">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                        <span x-text="(openThreads[{{ $id }}] || replyTo === {{ $id }}) ? '{{ $t['tutup'] }} {{ $descCount }} {{ $t['balasan'] }}' : '{{ $t['lihat'] }} {{ $descCount }} {{ $t['balasan'] }}'"></span>
                    </button>
                @endif
            </div>

            @if ($canReply)
                {{-- Form balasan inline (tamu tetap wajib captcha). --}}
                <form wire:submit="submit" x-cloak x-show="replyTo === {{ $id }}" id="reply-form-{{ $id }}" class="mt-4">
                    <div class="hidden" aria-hidden="true">
                        <label>Website (jangan diisi)<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    @if ($isGuest)
                        <div class="flex items-center gap-3 px-1">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#2B5343] text-sm font-semibold uppercase text-white" aria-hidden="true" x-text="(replyNameDraft || 'A').charAt(0).toUpperCase()">A</div>
                            <label for="reply-name-{{ $id }}" class="sr-only">{{ $t['nama'] }}</label>
                            <input id="reply-name-{{ $id }}" type="text" x-model="replyNameDraft" maxlength="60" autocomplete="name" placeholder="{{ $t['nama'] }}" class="min-w-0 flex-1 border-x-0 border-t-0 border-b border-[#e5e7eb] bg-transparent px-1 py-2 text-sm font-medium outline-none transition placeholder:font-normal placeholder:text-auriga-muted/55 focus:border-[#2B5343] focus:ring-0">
                            <input type="hidden" wire:model="replyName">
                        </div>
                        @error('replyName') <p class="mt-1 px-1 text-xs text-auriga-red">{{ $message }}</p> @enderror
                    @endif

                    <div class="@if ($isGuest) mt-3 @endif overflow-hidden bg-[#f5f5f5]">
                        <label for="ta-reply-{{ $id }}" class="sr-only">{{ $t['balas'] }}</label>
                        <textarea id="ta-reply-{{ $id }}" wire:model="replyBody" x-model="replyBodyDraft" rows="4" maxlength="1000" placeholder="{{ $t['reply_placeholder'] }}" class="block min-h-28 w-full resize-none border-0 bg-transparent px-4 py-3 text-sm leading-6 outline-none placeholder:text-auriga-muted/45 focus:ring-0"></textarea>
                        @error('replyBody') <p class="px-4 pb-3 text-xs text-auriga-red">{{ $message }}</p> @enderror

                        @if ($isGuest && $siteKey)
                            <div class="border-t border-black/5 px-4 py-3">
                                <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.1em] text-auriga-muted">{{ $t['verify'] }}</p>
                                <div id="captcha-root-reply-{{ $id }}" wire:ignore class="min-h-[65px]"><span class="text-xs text-auriga-muted">{{ $t['captcha_load'] }}</span></div>
                                @error('captchaToken') <p class="mt-2 text-xs text-auriga-red">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-3 border-t border-black/5 px-4 py-3">
                            <div class="flex items-center gap-4 text-auriga-muted" aria-label="Format">
                                <button type="button" x-on:click="fmt('bold')" class="font-serif text-lg font-bold transition hover:text-auriga-ink" aria-label="Tebal">B</button>
                                <button type="button" x-on:click="fmt('italic')" class="font-serif text-lg italic transition hover:text-auriga-ink" aria-label="Miring">i</button>
                                <button type="button" x-on:click="fmt('link')" class="text-base transition hover:text-auriga-ink" aria-label="Tautan">↗</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="cancelReply()" class="px-2 py-2 text-[10px] font-medium text-auriga-ink transition hover:text-[#2B5343]">{{ $t['batal'] }}</button>
                                <button type="submit" :disabled="!canPost" :class="canPost ? 'bg-[#2B5343] enabled:hover:bg-[#1f3d31]' : 'bg-[#2B5343] cursor-not-allowed opacity-35'" class="rounded-full px-4 py-2 text-[9px] font-bold uppercase tracking-[0.1em] text-white transition">{{ $t['kirim'] }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if ($hasReplies)
        <div
            data-replylist="{{ $id }}"
            class="mt-5 space-y-0"
            x-show="openThreads[{{ $id }}] || replyTo === {{ $id }}"
            x-collapse
            x-init="(() => {
                const connector = document.getElementById('connector-{{ $id }}');
                const cb = () => layoutConnector({{ $id }}, connector);
                cb();
                if (window.ResizeObserver) { new ResizeObserver(cb).observe($el); }
            })()"
        >
            @foreach ($replies as $child)
                @include('livewire.partials.comment-item', [
                    'comment' => $child,
                    'depth' => $depth + 1,
                    'maxDepth' => $maxDepth,
                    't' => $t,
                    'months' => $months,
                    'isGuest' => $isGuest,
                    'siteKey' => $siteKey,
                    'locale' => $locale,
                ])
            @endforeach
        </div>

        <span id="connector-{{ $id }}" class="pointer-events-none absolute z-0 w-px bg-[#e5e7eb] hidden" aria-hidden="true" x-effect="layoutConnector({{ $id }}, $el)"></span>
    @endif
</article>