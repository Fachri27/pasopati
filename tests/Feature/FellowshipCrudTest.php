<?php

namespace Tests\Feature;

use App\Livewire\Fellowship\FellowshipForm;
use App\Livewire\Fellowship\FellowshipTable;
use App\Models\Fellowship;
use App\Models\FellowshipTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FellowshipCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function createFellowship(string $titleId, string $titleEn, string $status = 'active'): Fellowship
    {
        $f = Fellowship::create([
            'slug' => \Illuminate\Support\Str::slug($titleId),
            'start_date' => '2026-01-01',
            'status' => $status,
            'user_id' => $this->admin->id,
        ]);
        FellowshipTranslation::create([
            'fellowship_id' => $f->id,
            'locale' => 'id',
            'title' => $titleId,
            'sub_judul' => 'Sub '.$titleId,
        ]);
        FellowshipTranslation::create([
            'fellowship_id' => $f->id,
            'locale' => 'en',
            'title' => $titleEn,
            'sub_judul' => 'Sub '.$titleEn,
        ]);
        return $f;
    }

    // --- Table Tests ---

    public function test_guest_cannot_access_fellowship(): void
    {
        $this->get(route('fellowship.index'))
            ->assertRedirect();
    }

    public function test_admin_can_view_fellowship_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipTable::class)
            ->assertOk();
    }

    public function test_fellowship_table_shows_data(): void
    {
        $this->createFellowship('Fellowship Karhutla', 'Karhutla Fellowship');

        Livewire::actingAs($this->admin)
            ->test(FellowshipTable::class)
            ->assertSee('Fellowship Karhutla');
    }

    public function test_fellowship_table_search(): void
    {
        $this->createFellowship('Fellowship Karhutla', 'Karhutla Fellowship');
        $this->createFellowship('Fellowship Iklim', 'Climate Fellowship');

        Livewire::actingAs($this->admin)
            ->test(FellowshipTable::class)
            ->set('search', 'Karhutla')
            ->assertSee('Fellowship Karhutla')
            ->assertDontSee('Fellowship Iklim');
    }

    public function test_admin_can_delete_fellowship(): void
    {
        $f = $this->createFellowship('To Delete', 'Delete Me');

        Livewire::actingAs($this->admin)
            ->test(FellowshipTable::class)
            ->call('delete', $f->id);

        $this->assertDatabaseMissing('fellowships', ['id' => $f->id]);
    }

    // --- Form Tests ---

    public function test_admin_can_view_fellowship_create_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class)
            ->assertOk();
    }

    public function test_admin_can_create_fellowship(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class)
            ->set('title_id', 'Fellowship Baru')
            ->set('title_en', 'New Fellowship')
            ->set('sub_judul_id', 'Sub Judul Fellowship')
            ->set('sub_judul_en', 'Sub Fellowship Title')
            ->set('start_date', '2026-09-01')
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('fellowship.index'));

        $this->assertDatabaseHas('fellowships', [
            'slug' => 'fellowship-baru',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('fellowship_translations', [
            'locale' => 'id',
            'title' => 'Fellowship Baru',
        ]);
    }

    public function test_create_fellowship_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class)
            ->call('save')
            ->assertHasErrors(['title_id', 'title_en', 'start_date', 'status']);
    }

    public function test_create_fellowship_validates_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class)
            ->set('title_id', 'Test')
            ->set('title_en', 'Test')
            ->set('start_date', '2026-01-01')
            ->set('status', 'invalid')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    public function test_admin_can_edit_fellowship(): void
    {
        $f = $this->createFellowship('Original Title', 'Original EN', 'draft');

        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class, ['fellowshipId' => $f->id])
            ->assertSet('title_id', 'Original Title')
            ->set('title_id', 'Updated Title')
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('fellowship.index'));

        $this->assertDatabaseHas('fellowship_translations', [
            'fellowship_id' => $f->id,
            'locale' => 'id',
            'title' => 'Updated Title',
        ]);
    }

    public function test_fellowship_slug_auto_generated(): void
    {
        Livewire::actingAs($this->admin)
            ->test(FellowshipForm::class)
            ->set('title_id', 'Judul Fellowship Spesial!')
            ->set('title_en', 'Special Fellowship Title')
            ->set('sub_judul_id', 'Sub Spesial')
            ->set('sub_judul_en', 'Sub Special')
            ->set('start_date', '2026-01-01')
            ->set('status', 'draft')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('fellowships', [
            'slug' => 'judul-fellowship-spesial',
        ]);
    }

    public function test_editor_can_access_fellowship(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($editor)
            ->test(FellowshipTable::class)
            ->assertOk();

        Livewire::actingAs($editor)
            ->test(FellowshipForm::class)
            ->assertOk();
    }
}
