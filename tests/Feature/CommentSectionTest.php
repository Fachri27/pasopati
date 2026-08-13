<?php

namespace Tests\Feature;

use App\Livewire\CommentSection;
use App\Models\Comment;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CommentSectionTest extends TestCase
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

    public function test_logged_in_user_can_submit_comment(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $page = $this->makePage();

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['commentable' => $page])
            ->set('body', 'Komentar uji coba')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'page_id' => $page->id,
            'user_id' => $user->id,
            'body' => 'Komentar uji coba',
        ]);
    }

    public function test_guest_can_submit_comment_with_name_and_captcha(): void
    {
        // Tamu hanya mengisi nama (form tidak mengumpulkan email sesuai design
        // reference). Komentar tersimpan dengan name terisi & email null.
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('commentName', 'Budi')
            ->set('captchaToken', 'dummy-token')
            ->set('body', 'Komentar dari tamu')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'page_id' => $page->id,
            'name' => 'Budi',
            'email' => null,
            'body' => 'Komentar dari tamu',
        ]);
    }

    public function test_guest_without_name_gets_validation_error(): void
    {
        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('body', 'Komentar tanpa nama')
            ->call('submit')
            ->assertHasErrors(['commentName']);
    }

    public function test_guest_name_is_remembered_via_cookie(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        $page = $this->makePage();

        Livewire::test(CommentSection::class, ['commentable' => $page])
            ->set('commentName', 'Budi')
            ->set('captchaToken', 'dummy-token')
            ->set('body', 'Komentar pertama')
            ->call('submit');

        $this->assertNotNull(Cookie::queued('pasopati_comment_name'));
    }

    public function test_returning_guest_name_is_prefilled_from_cookie(): void
    {
        $page = $this->makePage();

        $component = Livewire::withCookies([
            'pasopati_comment_name' => 'Sari',
        ])->test(CommentSection::class, ['commentable' => $page]);

        $component
            ->assertSet('commentName', 'Sari');
    }

    public function test_reply_to_reply_creates_mention_under_root(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $page = $this->makePage();

        $root = Comment::create([
            'page_id' => $page->id,
            'commentable_type' => Page::class,
            'commentable_id' => $page->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'body' => 'Komentar root',
            'is_approved' => true,
        ]);

        $reply = Comment::create([
            'page_id' => $page->id,
            'commentable_type' => Page::class,
            'commentable_id' => $page->id,
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'body' => 'Sebuah balasan',
            'is_approved' => true,
            'parent_id' => $root->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['commentable' => $page])
            ->call('setReplyTo', $reply->id)
            ->assertSet('replyingTo', $root->id)
            ->assertSet('replyingToName', 'Sari')
            ->set('body', 'Setuju')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'page_id' => $page->id,
            'parent_id' => $root->id,
            'mention_name' => 'Sari',
            'body' => 'Setuju',
        ]);
    }

    public function test_logged_in_user_can_comment_on_a_laporan(): void
    {
        // Komentar di artikel laporan Deforestory — bukan Page. Wajib tersimpan
        // dengan commentable_type=Laporan & page_id null (polymorphic).
        $user = User::factory()->create(['role' => 'admin']);

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);
        $laporan = DeforestoryLaporan::create([
            'case_id' => $case->id, 'slug' => 'jejak', 'sort' => 1,
            'status' => 'active', 'published_at' => '2025-06-03',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'id',
            'title' => 'Jejak', 'excerpt' => 'x', 'content' => '<p>x</p>',
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['commentable' => $laporan])
            ->set('body', 'Komentar untuk laporan')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => DeforestoryLaporan::class,
            'commentable_id' => $laporan->id,
            'page_id' => null,
            'user_id' => $user->id,
            'body' => 'Komentar untuk laporan',
        ]);
    }

    public function test_laporan_comments_are_scoped_per_laporan(): void
    {
        // Komentar laporan A tidak boleh muncul di laporan B.
        $user = User::factory()->create(['role' => 'admin']);
        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);
        $laporanA = DeforestoryLaporan::create([
            'case_id' => $case->id, 'slug' => 'a', 'sort' => 1, 'status' => 'active',
        ]);
        $laporanB = DeforestoryLaporan::create([
            'case_id' => $case->id, 'slug' => 'b', 'sort' => 2, 'status' => 'active',
        ]);
        foreach (['id'] as $locale) {
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporanA->id, 'locale' => $locale,
                'title' => 'A', 'excerpt' => '', 'content' => '',
            ]);
            DeforestoryLaporanTranslation::create([
                'laporan_id' => $laporanB->id, 'locale' => $locale,
                'title' => 'B', 'excerpt' => '', 'content' => '',
            ]);
        }

        // Komentar di laporan A.
        Comment::create([
            'commentable_type' => DeforestoryLaporan::class,
            'commentable_id' => $laporanA->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'body' => 'Komentar A',
            'is_approved' => true,
        ]);

        // Laporan B harus melihat 0 komentar.
        $component = Livewire::actingAs($user)
            ->test(CommentSection::class, ['commentable' => $laporanB]);

        $this->assertSame(0, $component->viewData('totalComments'));
    }
}
