<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * Login Google untuk kolom komentar (Laravel Socialite).
 *
 *   GET /comment/login/google?intended=<url>   → simpan intended, redirect Google
 *   GET /comment/login/google/callback         → cari/buat user role 'commenter', login
 *   GET /comment/logout?intended=<url>         → logout, balik ke halaman
 *
 * Commenter role 'commenter' TIDAK boleh akses route admin (role:admin,editor).
 */
class CommentGoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function googleUserMock(
        string $id = 'google-123',
        string $name = 'Budi Santoso',
        string $email = 'budi@gmail.com',
        string $avatar = 'https://lh3.googleusercontent.com/x.png',
    ): Mockery\MockInterface {
        $mock = Mockery::mock();
        $mock->shouldReceive('getId')->andReturn($id);
        $mock->shouldReceive('getName')->andReturn($name);
        $mock->shouldReceive('getEmail')->andReturn($email);
        $mock->shouldReceive('getAvatar')->andReturn($avatar);
        $mock->shouldReceive('getNickname')->andReturn(null);

        return $mock;
    }

    public function test_redirect_route_sets_intended_and_redirects_to_google(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth?test=1'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $res = $this->get('/comment/login/google?intended=/id/deforestory/mayawana/jejak');

        $res->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $res->headers->get('Location') ?? '');
        $this->assertSame('/id/deforestory/mayawana/jejak', session('url.intended'));
    }

    public function test_callback_creates_commenter_and_redirects_to_intended(): void
    {
        Socialite::shouldReceive('driver')->once()->with('google')
            ->andReturn(Mockery::mock(['user' => $this->googleUserMock()]));

        $res = $this
            ->withSession(['url.intended' => '/id/deforestory/mayawana/jejak'])
            ->get('/comment/login/google/callback');

        $res->assertRedirect('/id/deforestory/mayawana/jejak');

        $user = User::where('email', 'budi@gmail.com')->first();
        $this->assertNotNull($user, 'user commenter dibuat');
        $this->assertSame('commenter', $user->role, 'role harus commenter, bukan editor');
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertNull($user->password, 'user Google tanpa password');
        $this->assertSame('https://lh3.googleusercontent.com/x.png', $user->image);
    }

    public function test_callback_links_existing_user_by_email_without_duplicate(): void
    {
        // User sudah ada (editor, punya password) dengan email yang sama.
        $existing = User::factory()->create([
            'email' => 'budi@gmail.com',
            'role' => 'editor',
            'google_id' => null,
        ]);

        Socialite::shouldReceive('driver')->once()->with('google')
            ->andReturn(Mockery::mock(['user' => $this->googleUserMock()]));

        $res = $this
            ->withSession(['url.intended' => '/id/x'])
            ->get('/comment/login/google/callback');

        $res->assertRedirect('/id/x');

        // Tidak duplikat: tetap 1 user dengan email itu.
        $this->assertSame(1, User::where('email', 'budi@gmail.com')->count());
        $existing->refresh();
        $this->assertSame('google-123', $existing->google_id, 'google_id ter-link');
        $this->assertSame('editor', $existing->role, 'role lama dipertahankan');
        $this->assertNotNull($existing->password, 'password lama tidak dihapus');
    }

    public function test_commenter_cannot_access_admin_routes(): void
    {
        $commenter = User::factory()->create(['role' => 'commenter']);

        // /kategori di dalam group role:admin,editor → commenter 403.
        $this->actingAs($commenter)->get('/kategori')->assertStatus(403);
    }

    public function test_logout_redirects_to_intended(): void
    {
        $commenter = User::factory()->create(['role' => 'commenter']);

        $res = $this->actingAs($commenter)
            ->get('/comment/logout?intended=/id/deforestory/mayawana/jejak');

        $res->assertRedirect('/id/deforestory/mayawana/jejak');
        $this->assertGuest();
    }

    public function test_logout_falls_back_to_root_for_external_intended(): void
    {
        $commenter = User::factory()->create(['role' => 'commenter']);

        // intended eksternal harus diabaikan → balik ke '/'.
        $this->actingAs($commenter)
            ->get('/comment/logout?intended=https://evil.example.com')
            ->assertRedirect('/');
    }
}
