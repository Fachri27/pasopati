<?php

namespace App\Mail;

use App\Models\DeforestoryCase;
use App\Models\DeforestorySubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeforestoryUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public DeforestoryCase $case;

    public DeforestorySubscriber $subscriber;

    public string $event;

    public string $subscriberLocale;

    public bool $isCaseSpecific;

    public function __construct(DeforestoryCase $case, DeforestorySubscriber $subscriber, string $event = 'created')
    {
        $this->case = $case;
        $this->subscriber = $subscriber;
        $this->event = $event;
        $this->subscriberLocale = in_array($subscriber->locale, ['id', 'en']) ? $subscriber->locale : 'id';
        $this->isCaseSpecific = $subscriber->type === 'case' && $subscriber->case_id === $case->id;
    }

    public function envelope(): Envelope
    {
        $isId = $this->subscriberLocale === 'id';
        $title = $this->case->translation($this->subscriberLocale)?->title ?? $this->case->slug;

        if ($this->isCaseSpecific) {
            $subject = $this->event === 'created'
                ? ($isId ? "Kasus baru yang Anda ikuti: {$title}" : "New case you follow: {$title}")
                : ($isId ? "Update untuk kasus yang Anda ikuti: {$title}" : "Update for the case you follow: {$title}");
        } else {
            $subject = $this->event === 'created'
                ? ($isId ? "Kasus Deforestory baru: {$title}" : "New Deforestory case: {$title}")
                : ($isId ? "Update kasus Deforestory: {$title}" : "Deforestory case updated: {$title}");
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deforestory-update',
            with: [
                'case' => $this->case,
                'subscriber' => $this->subscriber,
                'event' => $this->event,
                'locale' => $this->subscriberLocale,
                'isCaseSpecific' => $this->isCaseSpecific,
            ],
        );
    }
}
