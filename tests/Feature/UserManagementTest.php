<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // --- Table Tests ---

    public function test_guest_cannot_access_user_table(): void
    {
        $this->get(route('user.index'))
            ->assertRedirect();
    }

    public function test_editor_cannot_access_user_index(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('user.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_user_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserTable::class)
            ->assertOk();
    }

    public function test_user_table_shows_users(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        Livewire::actingAs($this->admin)
            ->test(UserTable::class)
            ->assertSee('Budi Santoso');
    }

    public function test_user_table_search(): void
    {
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@test.com']);
        User::factory()->create(['name' => 'Andi Wijaya', 'email' => 'andi@test.com']);

        Livewire::actingAs($this->admin)
            ->test(UserTable::class)
            ->set('search', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Wijaya');
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create(['name' => 'To Delete']);

        Livewire::actingAs($this->admin)
            ->test(UserTable::class)
            ->call('delete', $user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    // --- Form Tests: Create ---

    public function test_admin_can_view_user_create_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->assertOk();
    }

    public function test_editor_cannot_view_user_create_form(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('user.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Citra Dewi')
            ->set('email', 'citra@test.com')
            ->set('role', 'admin')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->call('save')
            ->assertRedirect(route('user.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Citra Dewi',
            'email' => 'citra@test.com',
            'role' => 'admin',
        ]);
    }

    public function test_create_user_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->call('save')
            ->assertHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Test')
            ->set('email', 'existing@test.com')
            ->set('role', 'editor')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_create_user_validates_role(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('role', 'superadmin')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->call('save')
            ->assertHasErrors(['role']);
    }

    public function test_create_user_validates_password_confirmation(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('role', 'editor')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'different')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_create_user_validates_password_min_length(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@test.com')
            ->set('role', 'editor')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_created_user_has_hashed_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'Hash Test')
            ->set('email', 'hash@test.com')
            ->set('role', 'editor')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->call('save');

        $user = User::where('email', 'hash@test.com')->first();
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    // --- Form Tests: Edit ---

    public function test_admin_can_edit_user(): void
    {
        $user = User::factory()->create(['name' => 'Original Name', 'role' => 'admin']);

        Livewire::actingAs($this->admin)
            ->test(UserForm::class, ['userId' => $user->id])
            ->assertSet('name', 'Original Name')
            ->set('name', 'Updated Name')
            ->call('save')
            ->assertRedirect(route('user.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_edit_user_can_change_role(): void
    {
        $user = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($this->admin)
            ->test(UserForm::class, ['userId' => $user->id])
            ->set('role', 'admin')
            ->call('save');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
    }

    public function test_edit_user_without_password_does_not_change_it(): void
    {
        $originalPassword = 'original_pass';
        $user = User::factory()->create(['password' => Hash::make($originalPassword)]);

        Livewire::actingAs($this->admin)
            ->test(UserForm::class, ['userId' => $user->id])
            ->set('name', 'Updated')
            ->call('save');

        $user->refresh();
        $this->assertTrue(Hash::check($originalPassword, $user->password));
    }

    public function test_edit_user_allows_same_email_for_self(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class, ['userId' => $this->admin->id])
            ->set('email', $this->admin->email)
            ->set('name', $this->admin->name)
            ->call('save');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
