<?php

namespace App\Jobs;

use App\Mail\DeforestoryUpdateMail;
use App\Models\DeforestoryCase;
use App\Models\DeforestorySubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DeforestoryNotificationJob implements ShouldQueue
{
    use Queueable;

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
            Mail::to($subscriber->email)
                ->queue(new DeforestoryUpdateMail($case, $subscriber, $this->event));
        }
    }
}
