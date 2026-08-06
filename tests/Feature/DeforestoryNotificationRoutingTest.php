<?php

namespace Tests\Feature;

use App\Jobs\DeforestoryNotificationJob;
use App\Mail\DeforestoryUpdateMail;
use App\Models\DeforestoryCase;
use App\Models\DeforestorySubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Verifikasi arah notifikasi subscriber Deforestory. Dua tier:
 *   - 'all'   → dapat email untuk SEMUA kasus yang di-publish.
 *   - 'case'  → dapat email HANYA untuk kasus yg case_id-nya cocok.
 * Subscriber active=false diabaikan. Job skip kalau case status != active.
 *
 * Job dipanggil langsung (handle()) + Mail::fake supaya deterministik,
 * gak bergantung config queue.
 */
class DeforestoryNotificationRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function makeCase(string $slug, string $status = 'active'): DeforestoryCase
    {
        return DeforestoryCase::create([
            'slug' => $slug,
            'status' => $status,
        ]);
    }

    private function makeSubscriber(string $email, string $type, ?int $caseId = null, bool $active = true): DeforestorySubscriber
    {
        return DeforestorySubscriber::create([
            'email' => $email,
            'type' => $type,
            'case_id' => $caseId,
            'locale' => 'id',
            'active' => $active,
        ]);
    }

    /** Subscriber 'all' dapat email saat kasus apapun di-publish. */
    public function test_all_subscriber_receives_email_for_any_case(): void
    {
        Mail::fake();
        $caseA = $this->makeCase('case-a');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryNotificationJob($caseA, 'created'))->handle();

        Mail::assertQueued(DeforestoryUpdateMail::class, fn ($m) => $m->hasTo('all@example.com'));
    }

    /** Subscriber 'case' dapat email HANYA untuk kasus yg cocok; kasus lain tidak. */
    public function test_case_subscriber_receives_only_for_matching_case(): void
    {
        Mail::fake();
        $caseA = $this->makeCase('case-a');
        $caseB = $this->makeCase('case-b');

        $this->makeSubscriber('case-a@example.com', 'case', $caseA->id);
        $this->makeSubscriber('case-b@example.com', 'case', $caseB->id);

        (new DeforestoryNotificationJob($caseA, 'created'))->handle();

        Mail::assertQueued(DeforestoryUpdateMail::class, fn ($m) => $m->hasTo('case-a@example.com'));
        Mail::assertNotQueued(DeforestoryUpdateMail::class, fn ($m) => $m->hasTo('case-b@example.com'));
    }

    /** Subscriber active=false (berhenti berlangganan) diabaikan. */
    public function test_inactive_subscribers_are_ignored(): void
    {
        Mail::fake();
        $caseA = $this->makeCase('case-a');

        $this->makeSubscriber('inactive-all@example.com', 'all', null, false);
        $this->makeSubscriber('inactive-case@example.com', 'case', $caseA->id, false);

        (new DeforestoryNotificationJob($caseA, 'created'))->handle();

        Mail::assertNotQueued(DeforestoryUpdateMail::class, fn ($m) => $m->hasTo('inactive-all@example.com'));
        Mail::assertNotQueued(DeforestoryUpdateMail::class, fn ($m) => $m->hasTo('inactive-case@example.com'));
    }

    /** Job tidak kirim apa-apa kalau case belum active. */
    public function test_job_skips_non_active_case(): void
    {
        Mail::fake();
        $case = $this->makeCase('case-a', 'draft');
        $this->makeSubscriber('all@example.com', 'all');

        (new DeforestoryNotificationJob($case, 'created'))->handle();

        Mail::assertNothingQueued();
    }
}