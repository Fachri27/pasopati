<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Page;
use App\Services\ProfanityFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CommentSection extends Component
{
    // Model tempat komentar ditempel — polymorphic: Page, DeforestoryLaporan,
    // atau model apa pun yang punya relasi commentable.
    public Model $commentable;

    public string $body = '';

    public string $replyBody = '';

    public string $commentName = '';

    public string $replyName = '';

    public string $commentEmail = '';

    public ?int $replyingTo = null;

    public ?string $replyingToName = null;

    public ?string $website = null;

    public string $captchaToken = '';

    public bool $hasGuestInfo = false;

    public array $translations = [];

    public array $translatedComments = [];

    // Urutan komentar: new (terbaru), top (paling disukai), old (terlama).
    public string $sort = 'new';

    public function mount(Model $commentable): void
    {
        $this->commentable = $commentable;

        // Tamu yang sudah pernah mengisi nama diingat via cookie, jadi tidak
        // perlu mengetik ulang saat berkomentar lagi.
        if (! Auth::check()) {
            $storedName = (string) Cookie::get('pasopati_comment_name', '');
            $this->commentName = $storedName;
            $this->replyName = $storedName;
            $this->hasGuestInfo = filled($storedName);
        }
    }

    protected function rules(): array
    {
        // Form root dan balasan punya field terpisah, jadi validasi harus
        // mengikuti form yang sedang aktif.
        $rules = filled($this->replyingTo)
            ? ['replyBody' => 'required|min:3']
            : ['body' => 'required|min:3'];

        // Tamu wajib mencantumkan nama supaya komentarnya ada nama. Email
        // tidak dikumpulkan di form (sesuai design referensi) — kolom email
        // nullable, dan tamu root dilindungi oleh Turnstile captcha.
        if (! Auth::check()) {
            $field = filled($this->replyingTo) ? 'replyName' : 'commentName';
            $rules[$field] = 'required|min:2|max:50';
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        if ($this->website) {
            $this->dispatch('captcha:reset');

            return;
        }

        // Captcha wajib untuk tamu pada komentar utama maupun balasan.
        if (! Auth::check() && ! $this->verifyTurnstile()) {
            $this->addError('captchaToken', app()->getLocale() === 'id'
                ? 'Verifikasi captcha gagal. Silakan coba lagi.'
                : 'Captcha verification failed. Please try again.');
            $this->dispatch('captcha:reset');

            return;
        }

        $profanity = app(ProfanityFilter::class);

        $isReply = filled($this->replyingTo);

        Comment::query()->create([
            'page_id' => $this->commentable instanceof Page ? $this->commentable->id : null,
            'commentable_type' => $this->commentable::class,
            'commentable_id' => $this->commentable->id,
            'user_id' => Auth::id(),
            'name' => Auth::user()?->name ?? ($isReply ? $this->replyName : $this->commentName),
            'email' => Auth::user()?->email,
            'body' => $profanity->filter($isReply ? $this->replyBody : $this->body),
            'ip_address' => request()->ip(),
            'parent_id' => $this->replyingTo,
            'mention_name' => $this->replyingToName,
        ]);

        // Ingat nama tamu selama 1 tahun supaya tidak perlu mengetik ulang.
        if (! Auth::check()) {
            $usedName = $isReply ? $this->replyName : $this->commentName;
            Cookie::queue('pasopati_comment_name', $usedName, 60 * 24 * 365);

            // Sinkronkan nama balasan/root supaya keduanya diingat dan tidak
            // saling menimpa saat user berpindah form.
            $this->commentName = $usedName;
            $this->replyName = $usedName;
        }

        $this->dispatch('captcha:reset');

        // Tangkap parent SEBELUM reset — supaya sinyal 'comment-posted' tahu
        // ini balasan ke komentar mana (untuk buka thread + kosongkan editor
        // balasan yang dipakai, bukan editor root).
        $postedParentId = $isReply ? (int) ($this->replyingTo ?? 0) : 0;

        if ($isReply) {
            $this->reset(['replyBody', 'replyingTo', 'replyingToName', 'website', 'captchaToken']);
        } else {
            $this->reset(['body', 'replyingTo', 'replyingToName', 'website', 'captchaToken']);
        }

        // Beri sinyal ke Alpine supaya thread balasan (jika ada) tetap terbuka
        // setelah Livewire me-re-render komponen.
        $this->dispatch('comment-posted', parentId: $postedParentId);
    }

    private function verifyTurnstile(): bool
    {
        if (blank($this->captchaToken)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $this->captchaToken,
            'remoteip' => request()->ip(),
        ]);

        return $response->json('success') === true;
    }

    public function setReplyTo(?int $commentId): void
    {
        if ($commentId === null) {
            $this->replyingTo = null;
            $this->replyingToName = null;
            $this->resetValidation();

            return;
        }

        $comment = Comment::query()->find($commentId);

        if (! $comment) {
            return;
        }

        // Benang bersarang (gaya reference): parent_id menunjuk komentar
        // yang dibalas apa adanya, dan mention_name selalu diisi nama author
        // target, sehingga tiap balasan menampilkan @parent di awal pesan.
        $this->replyingTo = $comment->id;
        $this->replyingToName = $comment->displayName();

        $this->resetValidation();
    }

    public function toggleReaction(int $commentId, string $type): void
    {
        $comment = Comment::query()->findOrFail($commentId);
        $user = Auth::user();
        $ip = request()->ip();

        $existing = CommentReaction::query()
            ->where('comment_id', $commentId)
            ->where(fn ($q) => $user ? $q->where('user_id', $user->id) : $q->where('ip_address', $ip))
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete();
            } else {
                $existing->update(['type' => $type]);
            }
        } else {
            CommentReaction::query()->create([
                'comment_id' => $commentId,
                'user_id' => $user?->id,
                'ip_address' => $user ? null : $ip,
                'type' => $type,
            ]);
        }
    }

    public function translateComment(int $commentId): void
    {
        if (in_array($commentId, $this->translatedComments)) {
            $this->translatedComments = array_values(array_diff($this->translatedComments, [$commentId]));

            return;
        }

        if (! isset($this->translations[$commentId])) {
            $comment = Comment::query()->findOrFail($commentId);
            $browserLang = request()->getPreferredLanguage();
            $target = $browserLang ? substr($browserLang, 0, 2) : (app()->getLocale() === 'id' ? 'en' : 'id');

            $translated = null;
            try {
                $tr = new GoogleTranslate($target, 'auto');
                $translated = $tr->translate($comment->body);
            } catch (\Exception $e) {
                //
            }

            $this->translations[$commentId] = $translated ?? $comment->body;
        }

        $this->translatedComments[] = $commentId;
    }

    public function render()
    {
        $user = Auth::user();
        $ip = request()->ip();

        // Ambil seluruh komentar approved untuk halaman ini (sekali query,
        // tanpa N+1) lalu bangun pohon bersarang di PHP: parent_id menunjuk
        // komentar mana pun, sehingga balasan bisa bersarang sampai kedalaman
        // 3 (sesuai design reference).
        $all = Comment::query()
            ->where('commentable_type', $this->commentable::class)
            ->where('commentable_id', $this->commentable->id)
            ->where('is_approved', true)
            ->with('reactions')
            ->get();

        $byParent = $all->groupBy(fn ($c) => $c->parent_id ?? 0);

        // Balasan anak diurutkan kronologis (terlama di atas) agar seperti
        // alur percakapan, pada tiap level.
        $buildChildren = function (int $parentId) use (&$buildChildren, $byParent) {
            return $byParent->get($parentId, collect())
                ->sortBy('created_at')
                ->values()
                ->map(function ($comment) use (&$buildChildren) {
                    $comment->setRelation('replies', $buildChildren($comment->id));

                    return $comment;
                })
                ->values();
        };

        $roots = $byParent->get(0, collect());

        if ($this->sort === 'old') {
            $roots = $roots->sortBy('created_at')->values();
        } elseif ($this->sort === 'top') {
            $roots = $roots->sortByDesc(fn ($c) => $c->reactions->where('type', 'like')->count() - $c->reactions->where('type', 'dislike')->count())->values();
        } else {
            $roots = $roots->sortByDesc('created_at')->values();
        }

        $roots = $roots->map(fn ($comment) => tap($comment, fn ($c) => $c->setRelation('replies', $buildChildren($c->id))))->values();

        // Thread tertutup secara default; pengguna bisa buka satu per satu.
        $openThreads = [];

        return view('livewire.comment-section', [
            'comments' => $roots,
            'currentUser' => $user,
            'currentIp' => $ip,
            'translatedComments' => $this->translatedComments,
            'translations' => $this->translations,
            'openThreads' => $openThreads,
            'totalComments' => $all->count(),
        ]);
    }
}
