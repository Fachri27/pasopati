<?php

namespace App\Jobs;

use App\Mail\DeforestoryCardMail;
use App\Models\DeforestoryCard;
use App\Models\DeforestorySubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Kirim email notifikasi ke subscriber CMS saat card Deforestory BARU masuk lewat
 * inbound webhook /api/deforestory/cards.
 *
 * Paralel dengan DeforestoryNotificationJob (yang berbasis DeforestoryCase, dipicu
 * saat laporan di-publish). Job ini berbasis DeforestoryCard supaya berlaku untuk
 * card yang baru di-push web lain meskipun belum ada DeforestoryCase-nya di CMS.
 *
 * Penerima: subscriber aktif type `all` (subscriber type `case` ikut kasus spesifik
 * lewat jalur laporan-publish, bukan "kasus baru muncul"). Dijalankan via queue
 * (async) — butuh `php artisan queue:work` jalan di CMS.
 */
class DeforestoryCardNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DeforestoryCard $card
    ) {}

    public function handle(): void
    {
        // SerializesModels me-resolve ulang by id; pastikan masih ada.
        if (! $this->card->exists) {
            return;
        }

        // Hanya card publish yang diberitau. Bisa jadi card di-set draft
        // SETELAH dispatch tapi sebelum worker jalan — skip biar gak email
        // card yang sudah disembunyiin.
        if ($this->card->status !== 'publish') {
            return;
        }

        $subscribers = DeforestorySubscriber::query()
            ->where('active', true)
            ->where('type', 'all')
            ->select('id', 'email', 'locale', 'unsubscribe_token')
            ->get();

        if ($subscribers->isEmpty()) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                ->queue(new DeforestoryCardMail($this->card, $subscriber));
        }
    }
}