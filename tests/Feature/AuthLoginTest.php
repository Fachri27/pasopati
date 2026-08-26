<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'wrong_password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        Livewire::test(LoginForm::class)
            ->set('email', 'nonexistent@test.com')
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_validates_required_fields(): void
    {
        Livewire::test(LoginForm::class)
            ->call('login')
            ->assertHasErrors(['email', 'password']);
    }

    public function test_login_validates_email_format(): void
    {
        Livewire::test(LoginForm::class)
            ->set('email', 'not-an-email')
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_login_validates_password_minimum_length(): void
    {
        Livewire::test(LoginForm::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'short')
            ->call('login')
            ->assertHasErrors(['password']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect();
    }

    public function test_login_redirects_to_intended_url(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $this->get(route('pages.index'));

        Livewire::test(LoginForm::class)
            ->set('email', 'admin@test.com')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('pages.index'));
    }
}
