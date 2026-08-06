<?php

namespace App\Livewire;

use App\Models\DeforestorySubscriber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DeforestorySubscribe extends Component
{
    public string $email = '';

    public string $locale = 'id';

    public ?string $statusMessage = null;

    public string $statusMessageType = 'success';

    public bool $subscribed = false;

    public string $variant = 'archive';

    public ?int $caseId = null;

    public string $captchaToken = '';

    public bool $captchaReady = false;

    public string $storedEmail = '';

    public function mount(string $locale = 'id', string $variant = 'archive', ?int $caseId = null): void
    {
        $this->locale = in_array($locale, ['id', 'en']) ? $locale : app()->getLocale();
        $this->variant = in_array($variant, ['archive', 'case']) ? $variant : 'archive';
        $this->caseId = $caseId;
        $this->captchaReady = Auth::check() || blank(config('services.turnstile.secret_key'));
        $this->setSubscribedState();

        if (Auth::check()) {
            $this->email = Auth::user()?->email ?? '';
        }
    }

    private function setSubscribedState(): void
    {
        $this->storedEmail = '';
        $this->subscribed = false;

        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (! $user || ! $user->email) {
            return;
        }

        $this->storedEmail = strtolower($user->email);
        $this->subscribed = $this->isSubscribed($this->storedEmail);

        if ($this->subscribed) {
            $this->statusMessageType = 'info';
            $this->statusMessage = $this->getSuccessMessage('already');
        }
    }

    private function isSubscribed(string $email): bool
    {
        $type = $this->variant === 'archive' ? 'all' : 'case';
        $caseId = $this->variant === 'case' ? $this->caseId : null;

        return DeforestorySubscriber::query()
            ->where('email', strtolower($email))
            ->where('type', $type)
            ->where('active', true)
            ->when($caseId, fn ($q) => $q->where('case_id', $caseId))
            ->when(! $caseId, fn ($q) => $q->whereNull('case_id'))
            ->exists();
    }

    public function subscribe(): void
    {
        $this->statusMessage = null;
        $this->subscribed = false;

        $rules = [
            'email' => 'required|email|max:255',
        ];

        $messages = [
            'email.required' => $this->locale === 'id' ? 'Email wajib diisi.' : 'Email is required.',
            'email.email' => $this->locale === 'id' ? 'Format email tidak valid.' : 'Invalid email format.',
        ];

        $turnstileConfigured = ! blank(config('services.turnstile.secret_key'));

        if (! Auth::check() && $turnstileConfigured) {
            $rules['captchaToken'] = 'required|string|min:10';
            $messages['captchaToken.required'] = $this->locale === 'id'
                ? 'Verifikasi keamanan gagal. Silakan coba lagi.'
                : 'Security verification failed. Please try again.';
        }

        $this->validate($rules, $messages);

        if (! Auth::check() && $turnstileConfigured && ! $this->verifyTurnstile()) {
            $this->addError('captchaToken', $this->locale === 'id'
                ? 'Verifikasi captcha gagal. Silakan coba lagi.'
                : 'Captcha verification failed. Please try again.');
            $this->dispatch('captcha:reset');
            return;
        }

        $type = $this->variant === 'archive' ? 'all' : 'case';
        $caseId = $this->variant === 'case' ? $this->caseId : null;

        $subscriber = DeforestorySubscriber::query()
            ->where('email', strtolower($this->email))
            ->where('type', $type)
            ->when($caseId, fn ($q) => $q->where('case_id', $caseId))
            ->when(! $caseId, fn ($q) => $q->whereNull('case_id'))
            ->first();

        if ($subscriber) {
            if (! $subscriber->active) {
                $subscriber->update(['active' => true, 'locale' => $this->locale]);
                $this->subscribed = true;
                $this->statusMessage = $this->getSuccessMessage('reactivated');
            } else {
                $this->statusMessageType = 'info';
                $this->statusMessage = $this->getSuccessMessage('already');
            }

            $this->resetForm();
            return;
        }

        DeforestorySubscriber::create([
            'email' => strtolower($this->email),
            'type' => $type,
            'case_id' => $caseId,
            'locale' => $this->locale,
            'active' => true,
        ]);

        $this->subscribed = true;
        $this->statusMessage = $this->getSuccessMessage('new');

        $this->resetForm();
    }

    private function verifyTurnstile(): bool
    {
        if (blank($this->captchaToken)) {
            return false;
        }

        $secret = config('services.turnstile.secret_key');
        if (empty($secret)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $this->captchaToken,
            'remoteip' => request()->ip(),
        ]);

        return $response->json('success') === true;
    }

    private function resetForm(): void
    {
        $this->email = '';
        $this->captchaToken = '';
        $this->captchaReady = Auth::check() || blank(config('services.turnstile.secret_key'));
        $this->dispatch('captcha:reset');
    }

    private function getSuccessMessage(string $key): string
    {
        $isId = $this->locale === 'id';
        $scope = $this->variant === 'archive'
            ? ($isId ? 'seluruh arsip Deforestory' : 'all Deforestory archives')
            : ($isId ? 'kasus ini' : 'this case');

        return match ($key) {
            'new' => $isId
                ? "Terima kasih telah berlangganan update {$scope}."
                : "Thank you for subscribing to updates for {$scope}.",
            'reactivated' => $isId
                ? "Langganan Anda untuk {$scope} diaktifkan kembali."
                : "Your subscription for {$scope} has been reactivated.",
            'already' => $isId
                ? "Anda sudah berlangganan update {$scope}."
                : "You are already subscribed to updates for {$scope}.",
            default => '',
        };
    }

    public function render()
    {
        return view('livewire.deforestory-subscribe', [
            'isGuest' => ! Auth::check(),
            'siteKey' => config('services.turnstile.site_key'),
            'preFilledEmail' => Auth::check() ? Auth::user()?->email : '',
        ]);
    }
}
