<?php

namespace App\Mail;

use App\Models\Petition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PetitionVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    public Petition $petition;

    public function __construct(Petition $petition, string $verificationUrl)
    {
        $this->petition = $petition;
        $this->verificationUrl = $verificationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi Tanda Tangan Petisi',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.petition-verification',
        );
    }
}
