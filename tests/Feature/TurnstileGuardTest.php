<?php

namespace Tests\Feature;

use App\Livewire\CommentSection;
use App\Livewire\DeforestorySubscribe;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Simulasi anti-bot: pastikan guard Turnstile di CommentSection dan
 * DeforestorySubscribe benar-benar memblokir tamu yang:
 *   1. kirim tanpa captcha token, dan
 *   2. kirim dengan token palsu (Cloudflare menjawab success=false).
 * Sebaliknya, human dengan token valid (Cloudflare success=true) lolos.
 *
 * Cloudflare dimock via Http::fake supaya test deterministik (gak butuh
 * jaringan). Inilah yang dibuktikan juga manual via API siteverify di
 * docs/simulasi: secret real (.env) menolak token palsu -> success=false.
 */
class TurnstileGuardTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(): Page
    {
        return Page::create([
            'slug' => 'test-expose',
            'page_type' => 'expose',
            'type' => 'default',
            'status' => 'draft',
            'user_id' => User::factory()->create()->id,
        ]);
    }

    // ---- Komentar ----------------------------------------------------------

    /** Bot: kirim komentar TANPA captcha -> ditolak, komentar tidak tersimpan. */
    public function test_guest_comment_without_captcha_is_rejected(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('commentName', 'Bot')
            ->set('body', 'Spam tanpa captcha')
            ->call('submit')
            ->assertHasErrors(['captchaToken']);

        $this->assertDatabaseMissing('comments', ['body' => 'Spam tanpa captcha']);
    }

    /** Bot: kirim komentar dengan token PALSU (Cloudflare gagal) -> ditolak. */
    public function test_guest_comment_with_failed_captcha_is_rejected(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ], 200)]);

        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('commentName', 'Bot')
            ->set('captchaToken', 'fake-bot-token-xxxxx')
            ->set('body', 'Spam dengan token palsu')
            ->call('submit')
            ->assertHasErrors(['captchaToken']);

        $this->assertDatabaseMissing('comments', ['body' => 'Spam dengan token palsu']);
    }

    /** Human: komentar dengan captcha valid (Cloudflare success) -> lolos. */
    public function test_guest_comment_with_valid_captcha_passes(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('commentName', 'Andi')
            ->set('captchaToken', 'valid-token')
            ->set('body', 'Komentar sah')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', ['body' => 'Komentar sah']);
    }

    // ---- Subscribe ---------------------------------------------------------

    /** Bot: subscribe TANPA captcha -> ditolak, subscriber tidak tersimpan. */
    public function test_guest_subscribe_without_captcha_is_rejected(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        Livewire::test(DeforestorySubscribe::class, ['locale' => 'id', 'variant' => 'archive'])
            ->set('email', 'bot@example.com')
            ->call('subscribe')
            ->assertHasErrors(['captchaToken']);

        $this->assertDatabaseMissing('deforestory_subscribers', ['email' => 'bot@example.com']);
    }

    /** Bot: subscribe dengan token PALSU -> ditolak. */
    public function test_guest_subscribe_with_failed_captcha_is_rejected(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false], 200)]);

        Livewire::test(DeforestorySubscribe::class, ['locale' => 'id', 'variant' => 'archive'])
            ->set('email', 'bot@example.com')
            ->set('captchaToken', 'fake-bot-token-xxxxx')
            ->call('subscribe')
            ->assertHasErrors(['captchaToken']);

        $this->assertDatabaseMissing('deforestory_subscribers', ['email' => 'bot@example.com']);
    }

    /** Human: subscribe dengan captcha valid -> lolos & tersimpan. */
    public function test_guest_subscribe_with_valid_captcha_passes(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        Livewire::test(DeforestorySubscribe::class, ['locale' => 'id', 'variant' => 'archive'])
            ->set('email', 'human@example.com')
            ->set('captchaToken', 'valid-token')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('deforestory_subscribers', ['email' => 'human@example.com']);
    }

    /** variant='case' (per-kasus) menyimpan row type='case' + case_id ter-set. */
    public function test_case_variant_creates_per_case_subscriber(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        $case = \App\Models\DeforestoryCase::create(['slug' => 'mayawana', 'status' => 'active']);

        Livewire::test(DeforestorySubscribe::class, [
            'locale' => 'id',
            'variant' => 'case',
            'caseId' => $case->id,
        ])
            ->set('email', 'case-follower@example.com')
            ->set('captchaToken', 'valid-token')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('deforestory_subscribers', [
            'email' => 'case-follower@example.com',
            'type' => 'case',
            'case_id' => $case->id,
            'active' => true,
        ]);
    }
}