<style>
    #comment-form:focus-within #comment-cancel { display: block; }
    #comment-form:focus-within #comment-toolbar { display: flex; }
    .comment-item {
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--color-line);
    }
    .comment-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .replies,
    .reply-form {
        margin-left: 1.5rem;
        width: calc(100% - 1.5rem);
        border-left: 3px solid var(--color-line);
    }
    .reply-item {
        padding-left: 1rem;
    }
    .reply-item[data-depth="2"] { padding-left: 2rem; }
    .reply-item[data-depth="3"] { padding-left: 3rem; }
    .reply-item[data-depth="4"] { padding-left: 4rem; }
    @media (min-width: 640px) {
        .replies,
        .reply-form {
            margin-left: 2rem;
            width: calc(100% - 2rem);
        }
        .reply-item { padding-left: 1.5rem; }
        .reply-item[data-depth="2"] { padding-left: 3rem; }
        .reply-item[data-depth="3"] { padding-left: 4.5rem; }
        .reply-item[data-depth="4"] { padding-left: 6rem; }
    }
</style>

@php
    $isId = app()->getLocale() === 'id';
    $ownEmail = auth()->check() ? null : (string) (\Illuminate\Support\Facades\Cookie::get('pasopati_comment_email', ''));

    $renderBody = function (?string $text): string {
        $text = (string) ($text ?? '');
        $links = [];
        $text = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/', function ($m) use (&$links) {
            $i = array_push($links, '<a href="'.e($m[2]).'" target="_blank" rel="noopener noreferrer" class="text-[#2563EB] font-medium hover:underline">'.e($m[1]).'</a>');
            return "\x00L".$i."\x00";
        }, $text);
        $text = e($text);
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<![A-Za-z0-9])_([^_]+)_(?![A-Za-z0-9])/', '<em>$1</em>', $text);
        $text = nl2br($text);
        foreach ($links as $i => $link) {
            $text = str_replace("\x00L".($i + 1)."\x00", $link, $text);
        }
        return $text;
    };

    $likeCount = fn ($c) => $c->reactions->where('type', 'like')->count();

    $avatarColor = function (string $name): string {
        $palette = ['#DC2626', '#2B5343', '#4B5563'];
        $hash = 0;
        for ($i = 0, $len = strlen($name); $i < $len; $i++) {
            $hash = ($hash * 31 + ord($name[$i])) % 1000003;
        }
        return $palette[abs($hash) % 3];
    };
@endphp

<section
    class="bg-paper border-t border-line px-5 py-12 sm:py-16"
    aria-label="Kolom komentar"
    x-data="{
        focused: false,
        draft: @entangle('body'),
        replying: @entangle('replyingTo'),
        replyingName: @entangle('replyingToName'),
        replyBody: @entangle('replyBody'),
        replyFocused: false,
        format(type) {
            const ta = this.$refs.body;
            if (! ta) return;
            const s = ta.selectionStart, e = ta.selectionEnd, sel = ta.value.slice(s, e);
            if (type === 'link') {
                const url = prompt('Tempel URL tautan:', 'https://');
                if (! url) return;
                ta.setRangeText('[' + (sel || 'tautan') + '](' + url + ')', s, e, 'end');
            } else {
                const w = type === 'bold' ? '**' : '_';
                const lbl = type === 'bold' ? 'Tebal' : 'Miring';
                ta.setRangeText(w + (sel || lbl) + w, s, e, 'end');
            }
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.focus();
        },
        formatReply(type) {
            const ta = this.$refs.replyBody;
            if (! ta) return;
            const s = ta.selectionStart, e = ta.selectionEnd, sel = ta.value.slice(s, e);
            if (type === 'link') {
                const url = prompt('Tempel URL tautan:', 'https://');
                if (! url) return;
                ta.setRangeText('[' + (sel || 'tautan') + '](' + url + ')', s, e, 'end');
            } else {
                const w = type === 'bold' ? '**' : '_';
                const lbl = type === 'bold' ? 'Tebal' : 'Miring';
                ta.setRangeText(w + (sel || lbl) + w, s, e, 'end');
            }
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.focus();
        },
        cancelReply() {
            replying = null;
            replyBody = '';
            replyFocused = false;
        }
    }"
>
    <div class="max-w-[720px] mx-auto">
        <div class="mb-8 sm:mb-10">
            <p class="font-mono-ui text-[.68rem] sm:text-[.72rem] font-bold uppercase tracking-[.14em] text-ink-3 mb-2">Diskusi</p>
            <h2 class="font-display font-bold text-[1.35rem] sm:text-[1.6rem] leading-[1.2] tracking-[-.015em] mb-3">Tanggapi artikel ini</h2>
            <p class="text-[.92rem] sm:text-[.95rem] text-ink-2 leading-[1.7]">Komentar Anda akan ditinjau sebelum ditampilkan. Diskusi kami moderatif, fokus pada substansi dan data.</p>
        </div>

        <form id="comment-form" wire:submit="submit" class="flex flex-col gap-5">
            <div class="flex gap-3 sm:gap-4">
                <div class="flex-shrink-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-forest text-white flex items-center justify-center font-display font-semibold text-sm sm:text-base select-none" aria-hidden="true">A</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-display font-semibold text-[.9rem] sm:text-base text-ink">Anda</span>
                        <button type="button" id="comment-cancel" x-show="focused" x-cloak
                            @mousedown.prevent="draft = ''; focused = false"
                            class="text-[.85rem] sm:text-[.9rem] text-ink-2 hover:text-ink transition-colors"
                        >Batal</button>
                    </div>
                    <div class="comment-field rounded-[6px] bg-soft transition-all duration-200 focus-within:bg-white focus-within:ring-1 focus-within:ring-forest/20 focus-within:shadow-sm">
                        <textarea
                            id="comment-body"
                            x-ref="body"
                            x-model="draft"
                            :rows="focused ? 4 : 1"
                            @focus="focused = true"
                            @blur="if (! draft.trim()) focused = false"
                            @keydown.escape="if (! draft.trim()) focused = false"
                            placeholder="Apa tanggapan Anda?"
                            class="w-full px-4 py-3 sm:py-3.5 text-[.95rem] sm:text-[1.05rem] leading-[1.6] bg-transparent outline-none resize-none placeholder:text-ink-3 transition-[min-height] duration-200"
                        ></textarea>
                        <div id="comment-toolbar" x-show="focused" x-cloak class="flex items-center justify-between px-3 sm:px-4 py-2.5 border-t border-line/60">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="format('bold')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Tebal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
                                </button>
                                <button type="button" @click="format('italic')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Miring">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
                                </button>
                                <button type="button" @click="format('link')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Tautan">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </button>
                            </div>
                            <button type="submit" id="comment-submit" wire:loading.attr="disabled" wire:target="submit"
                                :disabled="! draft.trim()"
                                :class="draft.trim() ? 'bg-forest text-white hover:bg-forest-d' : 'bg-line text-ink-3 cursor-not-allowed'"
                                class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-[.85rem] sm:text-[.9rem] font-semibold transition-colors"
                            >Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div id="comments-list" class="mt-10 sm:mt-12 flex flex-col gap-6">
            @forelse ($comments as $comment)
                @php
                    $isOwn = auth()->check()
                        ? $comment->user_id === auth()->id()
                        : (filled($ownEmail) && $comment->email === $ownEmail);
                    $cLiked = $comment->isLikedBy($currentUser, $currentIp);
                    $cLikes = $likeCount($comment);
                    $cName = $comment->displayName();
                @endphp

                <article class="comment-item" wire:key="c-{{ $comment->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-display font-semibold text-sm select-none" style="background-color: {{ $avatarColor($cName) }};" aria-hidden="true">
                                {{ mb_substr($cName, 0, 1) }}
                            </div>
                            <div class="leading-tight">
                                <span class="block font-display font-semibold text-[.9rem] sm:text-[.95rem] text-ink">{{ $cName }}</span>
                                <time datetime="{{ $comment->created_at->toIso8601String() }}" class="block text-[.75rem] sm:text-[.8rem] text-ink-3">
                                    {{ $comment->created_at->translatedFormat('j M Y') }}
                                </time>
                            </div>
                        </div>
                        <button type="button" class="comment-more p-1 text-ink-3 hover:text-ink transition-colors" aria-label="Opsi komentar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                        </button>
                    </div>
                    <p class="mt-2.5 text-[.95rem] sm:text-[1rem] leading-[1.7] text-ink-2">{!! $renderBody($comment->body) !!}</p>

                    <div class="mt-3 flex items-center gap-5">
                        <button type="button" wire:click="toggleReaction({{ $comment->id }}, 'like')"
                            class="inline-flex items-center gap-1.5 text-[.85rem] sm:text-[.9rem] text-ink-2 hover:text-ink transition-colors group"
                            aria-label="Sukai komentar"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="group-hover:fill-pasopati/10">
                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                            </svg>
                            <span class="font-medium">{{ $cLikes }}</span>
                        </button>

                        <button type="button" class="replies-toggle hidden inline-flex items-center text-[.85rem] sm:text-[.9rem] text-ink-2 hover:text-ink transition-colors font-medium" aria-expanded="false" data-count="0"></button>

                        <button type="button" wire:click="setReplyTo({{ $comment->id }})"
                            class="inline-flex items-center gap-1.5 text-[.85rem] sm:text-[.9rem] text-ink-2 hover:text-ink transition-colors"
                            aria-label="Balas komentar"
                        >Balas</button>
                    </div>

                    @if ($comment->replies->count())
                        <div class="replies mt-4 space-y-4">
                            @foreach ($comment->replies as $reply)
                                @php
                                    $rOwn = auth()->check()
                                        ? $reply->user_id === auth()->id()
                                        : (filled($ownEmail) && $reply->email === $ownEmail);
                                    $rLiked = $reply->isLikedBy($currentUser, $currentIp);
                                    $rLikes = $likeCount($reply);
                                    $rName = $reply->displayName();
                                @endphp

                                <article class="reply-item" data-depth="1" wire:key="r-{{ $reply->id }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full text-white flex items-center justify-center font-display font-semibold text-xs select-none" style="background-color: {{ $avatarColor($rName) }};" aria-hidden="true">
                                                {{ mb_substr($rName, 0, 1) }}
                                            </div>
                                            <div class="leading-tight">
                                                <span class="block font-display font-semibold text-[.85rem] sm:text-[.9rem] text-ink">{{ $rName }}</span>
                                                <time datetime="{{ $reply->created_at->toIso8601String() }}" class="block text-[.72rem] sm:text-[.75rem] text-ink-3">
                                                    {{ $reply->created_at->translatedFormat('j M Y') }}
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-[.9rem] sm:text-[.95rem] leading-[1.65] text-ink-2">
                                        @if ($reply->mention_name)
                                            <span class="text-forest font-medium">{{ '@' . $reply->mention_name }}</span>
                                        @endif
                                        {!! $renderBody($reply->body) !!}
                                    </p>

                                    <div class="mt-2.5 flex items-center gap-4">
                                        <button type="button" wire:click="toggleReaction({{ $reply->id }}, 'like')"
                                            class="inline-flex items-center gap-1.5 text-[.8rem] sm:text-[.85rem] text-ink-3 hover:text-ink transition-colors group"
                                            aria-label="Sukai komentar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="group-hover:fill-pasopati/10">
                                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                            </svg>
                                            <span class="font-medium">{{ $rLikes }}</span>
                                        </button>
                                        <button type="button" class="replies-toggle hidden inline-flex items-center text-[.8rem] sm:text-[.85rem] text-ink-3 hover:text-ink transition-colors font-medium" aria-expanded="false" data-count="0"></button>
                                        <button type="button" wire:click="setReplyTo({{ $reply->id }})"
                                            class="inline-flex items-center gap-1.5 text-[.8rem] sm:text-[.85rem] text-ink-3 hover:text-ink transition-colors"
                                            aria-label="Balas"
                                        >Balas</button>
                                    </div>
                                    <div class="replies hidden mt-3 space-y-3"></div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <div class="reply-form mt-4 pl-4 sm:pl-6" x-show="replying === {{ $comment->id }}" x-cloak x-init="$nextTick(() => { $refs.replyBody?.focus(); })">
                        <form wire:submit="submitReply" class="flex gap-3 sm:gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-forest text-white flex items-center justify-center font-display font-semibold text-sm select-none" aria-hidden="true">A</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[.82rem] sm:text-[.85rem] text-ink-3">Membalas <strong class="text-ink font-semibold" x-text="replyingName"></strong></p>
                                    <button type="button" @click="cancelReply()" class="text-[.82rem] sm:text-[.85rem] text-ink-2 hover:text-ink transition-colors">Batal</button>
                                </div>
                                <div class="comment-field rounded-[6px] bg-soft transition-all duration-200 focus-within:bg-white focus-within:ring-1 focus-within:ring-forest/20 focus-within:shadow-sm">
                                    <textarea
                                        x-ref="replyBody"
                                        x-model="replyBody"
                                        :rows="replyFocused ? 4 : 1"
                                        @focus="replyFocused = true"
                                        @blur="if (! replyBody.trim()) replyFocused = false"
                                        @keydown.escape="if (! replyBody.trim()) cancelReply()"
                                        placeholder="Tulis balasan..."
                                        class="w-full px-4 py-3 sm:py-3.5 text-[.95rem] sm:text-[1.05rem] leading-[1.6] bg-transparent outline-none resize-none placeholder:text-ink-3"
                                    ></textarea>
                                    <div x-show="replyFocused" x-cloak class="flex items-center justify-between px-3 sm:px-4 py-2.5 border-t border-line/60">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="formatReply('bold')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Tebal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
                                            </button>
                                            <button type="button" @click="formatReply('italic')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Miring">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
                                            </button>
                                            <button type="button" @click="formatReply('link')" class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center rounded-[4px] text-ink-2 hover:bg-line/60 hover:text-ink transition-colors" aria-label="Tautan">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                            </button>
                                        </div>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="submitReply"
                                            :disabled="! replyBody.trim()"
                                            :class="replyBody.trim() ? 'bg-forest text-white hover:bg-forest-d' : 'bg-line text-ink-3 cursor-not-allowed'"
                                            class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-[.85rem] sm:text-[.9rem] font-semibold transition-colors"
                                        >Balas</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="text-center py-10">
                    <p class="text-[.9rem] text-ink-3">Belum ada tanggapan. Jadilah yang pertama berdiskusi.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
