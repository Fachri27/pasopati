<?php

namespace App\Jobs;

use App\Mail\DeforestoryUpdateMail;
use App\Models\DeforestoryCase;
use App\Models\DeforestorySentEmail;
use App\Models\DeforestorySubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DeforestoryNotificationJob implements ShouldQueue
{
    use Queueable;

    // Queue `database` punya retry_after=90. Tanpa batas tegas, job yang lambat
    // bisa di-reserved ulang ke worker lain sementara worker asli masih jalan →
    // double-send. $timeout < retry_after + failOnTimeout=true membuat timeout
    // membuang job ke failed_jobs (bukan silent retry). $tries membatasi retry
    // exception; idempotensi di bawah menahan retry tetap aman dari email ganda.
    public int $tries = 3;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    public DeforestoryCase $case;

    public string $event;

    public function __construct(DeforestoryCase $case, string $event = 'created')
    {
        $this->case = $case;
        $this->event = $event;
    }

    public function handle(): void
    {
        $case = $this->case;

        if ($case->status !== 'active') {
            return;
        }

        $subscribers = DeforestorySubscriber::query()
            ->where('active', true)
            ->where(function ($q) use ($case) {
                $q->where('type', 'all')
                    ->orWhere(function ($q2) use ($case) {
                        $q2->where('type', 'case')->where('case_id', $case->id);
                    });
            })
            ->select('id', 'email', 'locale', 'type', 'case_id')
            ->get();

        if ($subscribers->isEmpty()) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            // Catat SEBELUM queue. Kalau baris sudah ada (unique violation) =
            // pernah dikirim untuk (subscriber, case, event) ini → skip, jangan
            // antri mailable lagi. Insert-before-queue memilih aman ke arah
            // "tidak spam": bila job crash setelah insert tapi sebelum queue,
            // retry melewatkan baris itu (email satu subscriber hilang, bukan
            // ganda) — itu lebih baik daripada sebaliknya.
            try {
                DeforestorySentEmail::create([
                    'subscriber_id' => $subscriber->id,
                    'case_id' => $case->id,
                    'event' => $this->event,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                continue;
            }

            Mail::to($subscriber->email)
                ->queue(new DeforestoryUpdateMail($case, $subscriber, $this->event));
        }
    }
}
