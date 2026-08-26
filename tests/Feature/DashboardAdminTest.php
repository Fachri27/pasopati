<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Pasopati CMS');
    }

    public function test_editor_can_view_dashboard(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_shows_article_count(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Artikel');
    }

    public function test_dashboard_shows_fellowship_count(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Fellowship');
    }

    public function test_dashboard_shows_petition_count(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Petisi');
    }

    public function test_dashboard_shows_comment_count(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Komentar');
    }

    public function test_dashboard_shows_user_count(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna');
    }

    public function test_dashboard_shows_quick_actions(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aksi Cepat')
            ->assertSee('Artikel Baru')
            ->assertSee('Fellowship Baru')
            ->assertSee('Petisi Baru')
            ->assertSee('Kategori Baru');
    }

    public function test_dashboard_shows_nav_links(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Artikel')
            ->assertSee('Kejadian')
            ->assertSee('Fellowship')
            ->assertSee('Petisi')
            ->assertSee('Deforestory')
            ->assertSee('Kategori');
    }

    public function test_admin_sees_user_management_link(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna');
    }

    public function test_editor_does_not_see_user_management_link(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('user.index'));
    }
}
