<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PasskeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_a_login_challenge_without_email(): void
    {
        $this->postJson(route('webauthn.login.options'))
            ->assertOk()
            ->assertJsonStructure(['challenge', 'timeout']);
    }

    public function test_login_with_a_bogus_assertion_is_rejected(): void
    {
        $this->postJson(route('webauthn.login.options'));

        $this->postJson(route('webauthn.login'), ['id' => 'x', 'rawId' => 'x', 'type' => 'public-key', 'response' => []])
            ->assertStatus(422);

        $this->assertGuest();
    }

    public function test_registration_requires_a_logged_in_cms_user(): void
    {
        $this->postJson(route('webauthn.register.options'))->assertUnauthorized();

        $pembaca = User::factory()->create(['role' => 'commenter']);
        $this->actingAs($pembaca)->postJson(route('webauthn.register.options'))->assertForbidden();
    }

    public function test_editor_gets_registration_options_for_the_built_in_authenticator(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->postJson(route('webauthn.register.options'))
            ->assertOk()
            ->assertJsonPath('authenticatorSelection.authenticatorAttachment', 'platform')
            ->assertJsonPath('authenticatorSelection.userVerification', 'required')
            ->assertJsonPath('hints', ['client-device']);
    }

    public function test_user_can_only_remove_own_devices(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Editor membuka form akun admin ditolak sebelum tombol hapus sempat ada.
        Livewire::actingAs($editor)
            ->test(UserForm::class, ['userId' => $admin->id])
            ->assertForbidden();

        // Di akunnya sendiri ia bisa memanggil hapus (tanpa perangkat: tak ada yang terhapus, tak ada galat).
        Livewire::actingAs($editor)
            ->test(UserForm::class, ['userId' => $editor->id])
            ->call('hapusPerangkat', 'tidak-ada')
            ->assertOk();
    }

    public function test_admin_cannot_manage_devices_on_another_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($admin)
            ->test(UserForm::class, ['userId' => $editor->id])
            ->call('hapusPerangkat', 'apa-saja')
            ->assertForbidden();
    }

    public function test_auth_provider_understands_webauthn_credentials(): void
    {
        // Tanpa driver ini login biometrik gagal 500 (Laravel mencari kolom rawId di tabel users).
        $this->assertSame('eloquent-webauthn', config('auth.providers.users.driver'));
    }
}
