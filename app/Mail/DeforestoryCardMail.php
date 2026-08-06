<?php

namespace App\Mail;

use App\Models\DeforestoryCard;
use App\Models\DeforestorySubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notifikasi "kasus baru" berbasis card Deforestory (didorong web lain).
 *
 * Beda dari DeforestoryUpdateMail (yang berbasis DeforestoryCase + dipicu saat laporan
 * publish): mailable ini pakai data card (title_id/title_en, excerpt_id/excerpt_en,
 * slug) langsung, jadi cocok untuk card baru yang belum punya DeforestoryCase di CMS.
 */
class DeforestoryCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public DeforestoryCard $card;
    public DeforestorySubscriber $subscriber;
    public string $subscriberLocale;

    public function __construct(DeforestoryCard $card, DeforestorySubscriber $subscriber)
    {
        $this->card = $card;
        $this->subscriber = $subscriber;
        $this->subscriberLocale = in_array($subscriber->locale, ['id', 'en']) ? $subscriber->locale : 'id';
    }

    public function envelope(): Envelope
    {
        $isId = $this->subscriberLocale === 'id';
        $title = $this->cardTitle();

        return new Envelope(
            subject: $isId ? "Kasus Deforestory baru: {$title}" : "New Deforestory case: {$title}",
        );
    }

    public function content(): Content
    {
        $isId = $this->subscriberLocale === 'id';

        return new Content(
            markdown: 'emails.deforestory-card',
            with: [
                'card' => $this->card,
                'subscriber' => $this->subscriber,
                'locale' => $this->subscriberLocale,
                'title' => $this->cardTitle(),
                'excerpt' => $this->cardExcerpt(),
                'caseUrl' => route('deforestory.case', ['locale' => $this->subscriberLocale, 'slug' => $this->card->slug]),
                'unsubscribeUrl' => route('deforestory.unsubscribe', ['locale' => $this->subscriberLocale, 'token' => $this->subscriber->unsubscribe_token]),
                'isId' => $isId,
            ]
        );
    }

    /** Judul card sesuai locale subscriber, fallback ke id. */
    protected function cardTitle(): string
    {
        return $this->subscriberLocale === 'en'
            ? ($this->card->title_en ?: $this->card->title_id ?: $this->card->slug)
            : ($this->card->title_id ?: $this->card->title_en ?: $this->card->slug);
    }

    /** Excerpt card sesuai locale subscriber, fallback ke id. */
    protected function cardExcerpt(): string
    {
        return $this->subscriberLocale === 'en'
            ? (string) ($this->card->excerpt_en ?: $this->card->excerpt_id ?? '')
            : (string) ($this->card->excerpt_id ?: $this->card->excerpt_en ?? '');
    }
}