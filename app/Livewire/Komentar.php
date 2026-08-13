<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class Komentar extends Component
{
    public Page $page;

    public string $body = '';

    public string $replyBody = '';

    public string $commentName = '';

    public string $commentEmail = '';

    public ?int $replyingTo = null;

    public ?string $replyingToName = null;

    public bool $hasGuestInfo = false;

    public function mount(Page $page): void
    {
        $this->page = $page;

        if (! Auth::check()) {
            $this->commentName = (string) Cookie::get('pasopati_comment_name', '');
            $this->commentEmail = (string) Cookie::get('pasopati_comment_email', '');
            $this->hasGuestInfo = filled($this->commentName) && filled($this->commentEmail);
        }
    }

    protected function rules(): array
    {
        $rules = ['body' => 'required|min:3'];

        if (! Auth::check()) {
            $rules['commentName'] = 'required|min:2|max:50';
            $rules['commentEmail'] = 'required|email|max:100';
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        $comment = Comment::query()->create([
            'page_id' => $this->page->id,
            'user_id' => Auth::id(),
            'name' => Auth::user()?->name ?? $this->commentName,
            'email' => Auth::user()?->email ?? $this->commentEmail,
            'body' => $this->body,
            'ip_address' => request()->ip(),
            'parent_id' => $this->replyingTo,
            'mention_name' => $this->replyingToName,
        ]);

        if (! Auth::check()) {
            Cookie::queue('pasopati_comment_name', $this->commentName, 60 * 24 * 365);
            Cookie::queue('pasopati_comment_email', $this->commentEmail, 60 * 24 * 365);
        }

        $this->reset(['body', 'replyingTo', 'replyingToName']);
    }

    public function submitReply(): void
    {
        $this->validate(['replyBody' => 'required|min:3']);

        Comment::query()->create([
            'page_id' => $this->page->id,
            'user_id' => Auth::id(),
            'name' => Auth::user()?->name ?? $this->commentName,
            'email' => Auth::user()?->email ?? $this->commentEmail,
            'body' => $this->replyBody,
            'ip_address' => request()->ip(),
            'parent_id' => $this->replyingTo,
            'mention_name' => $this->replyingToName,
        ]);

        if (! Auth::check()) {
            Cookie::queue('pasopati_comment_name', $this->commentName, 60 * 24 * 365);
            Cookie::queue('pasopati_comment_email', $this->commentEmail, 60 * 24 * 365);
        }

        $this->reset(['replyBody', 'replyingTo', 'replyingToName']);
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

        if ($comment->parent_id) {
            $this->replyingTo = $comment->parent_id;
            $this->replyingToName = $comment->displayName();
        } else {
            $this->replyingTo = $comment->id;
            $this->replyingToName = null;
        }

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

    public function render()
    {
        $user = Auth::user();
        $ip = request()->ip();

        $comments = Comment::query()
            ->where('page_id', $this->page->id)
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->with(['reactions', 'replies.reactions'])
            ->latest()
            ->get();

        return view('livewire.komentar', [
            'comments' => $comments,
            'currentUser' => $user,
            'currentIp' => $ip,
        ]);
    }
}
