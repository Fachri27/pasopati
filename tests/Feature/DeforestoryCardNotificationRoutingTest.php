<?php

namespace Tests\Feature;

use App\Jobs\DeforestoryCardNotificationJob;
use App\Mail\DeforestoryCardMail;
use App\Models\DeforestoryCard;
use App\Models\DeforestorySubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Verifikasi job notifikasi card Deforestory (card baru masuk dari simontini
 * lewat inbound webhook). Hanya subscriber aktif type `all` yang dikirimi email.
 * Card draft di-skip. Dan — fokus utama — idempotensi: job di-handle ulang
 * (simulasi retry/timeout) tidak boleh antri email ganda.
 *
 * Job dipanggil langsung (handle()) + Mail::fake supaya deterministik. Tidak
 * menyentuh webhook/controller simontini sama sekali.
 */
class DeforestoryCardNotificationRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function makeCard(string $slug, string $status = 'publish'): DeforestoryCard
    {
        return DeforestoryCard::create([
            'slug' => $slug,
            'title_id' => ucfirst($slug),
            'status' => $status,
            'sort' => 1,
        ]);
    }

    private function makeSubscriber(string $email, string $type, bool $active = true): DeforestorySubscriber
    {
        return DeforestorySubscriber::create([
            'email' => $email,
            'type' => $type,
            'locale' => 'id',
            'active' => $active,
        ]);
    }

    /** Subscriber type `all` dapat email saat card publish baru masuk. */
    public function test_all_subscriber_receives_card_email(): void
    {
        Mail::fake();
        $card = $this->makeCard('card-a');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryCardNotificationJob($card))->handle();

        Mail::assertQueued(DeforestoryCardMail::class, fn ($m) => $m->hasTo('all@example.com'));
    }

    /** Subscriber type `case` TIDAK dapat email card baru (jalur card hanya untuk `all`). */
    public function test_case_subscriber_does_not_receive_card_email(): void
    {
        Mail::fake();
        $card = $this->makeCard('card-a');
        $this->makeSubscriber('case@example.com', 'case');

        (new DeforestoryCardNotificationJob($card))->handle();

        Mail::assertNotQueued(DeforestoryCardMail::class, fn ($m) => $m->hasTo('case@example.com'));
    }

    /** Job tidak kirim apa-apa kalau card berstatus draft. */
    public function test_job_skips_draft_card(): void
    {
        Mail::fake();
        $card = $this->makeCard('card-a', 'draft');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryCardNotificationJob($card))->handle();

        Mail::assertNothingQueued();
    }

    /**
     * Idempotensi: job di-handle dua kali (simulasi retry/timeout me-loop ulang)
     * untuk (subscriber, card) yang sama hanya boleh antri email sekali.
     */
    public function test_retry_does_not_queue_duplicate_email(): void
    {
        Mail::fake();
        $card = $this->makeCard('card-a');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryCardNotificationJob($card))->handle();
        (new DeforestoryCardNotificationJob($card))->handle();

        Mail::assertQueuedCount(1);
        Mail::assertQueued(DeforestoryCardMail::class, fn ($m) => $m->hasTo('all@example.com'));
    }

    /** Card berbeda tetap masing-masing dikirim (idempotensi per-card). */
    public function test_different_card_still_sends(): void
    {
        Mail::fake();
        $cardA = $this->makeCard('card-a');
        $cardB = $this->makeCard('card-b');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryCardNotificationJob($cardA))->handle();
        (new DeforestoryCardNotificationJob($cardB))->handle();

        Mail::assertQueuedCount(2);
    }

    /** Subscriber berbeda tetap masing-masing dapat email (idempotensi per-subscriber). */
    public function test_idempotency_is_per_subscriber(): void
    {
        Mail::fake();
        $card = $this->makeCard('card-a');
        $this->makeSubscriber('one@example.com', 'all');
        $this->makeSubscriber('two@example.com', 'all');

        (new DeforestoryCardNotificationJob($card))->handle();
        (new DeforestoryCardNotificationJob($card))->handle();

        Mail::assertQueuedCount(2);
        Mail::assertQueued(DeforestoryCardMail::class, fn ($m) => $m->hasTo('one@example.com'));
        Mail::assertQueued(DeforestoryCardMail::class, fn ($m) => $m->hasTo('two@example.com'));
    }
}